# Budget Calculator — Design Spec

**Date:** 2026-07-18
**Status:** Approved design (pending user review of this document)
**Source of truth:** Google Sheet "Project Budget & Requirement Form"
`https://docs.google.com/spreadsheets/d/163-0-HJlZpi6eoZCoyPPbuZdQ2XvDJttboRo3x7B5_s/edit` (tabs `838767931` = form, `1698046740` = reference data)

## 1. Goal

Reproduce the spreadsheet's client budget calculator as a feature inside the existing Laravel 10 app (`farka-studio`):

1. A **public-facing calculator page** (`/kalkulator-budget`, Indonesian UI) where a client fills in project inputs and sees a live-computed budget estimate reproducing all four sheet sections, then **downloads the result as a branded PDF**.
2. The same page is **also reachable from inside the admin panel** (internal tool for the studio team).
3. All the sheet's **reference / measurement data** (room area catalog, zonasi coefficients, base prices, weighting factors, allocation percentages, quality components, global settings) is **stored in the database and fully editable via admin CRUD** (English admin UI, matching existing panel style), so the studio can add/edit data without touching the sheet or code.

**Not persisted:** individual client submissions are *not* saved to the DB. The calculator is stateless per request; the client's takeaway is the downloadable PDF. (This can be added later without schema churn — see §11.)

## 2. Confirmed decisions

| Decision | Choice |
|---|---|
| Placement | Public page **and** admin-accessible |
| Save client submissions | No — live only, but **downloadable as PDF** |
| Reference data management | Full admin CRUD now |
| Output completeness | All 4 sections, faithful to sheet |
| Calculation engine | **Server-authoritative PHP** (`BudgetCalculatorService`); live preview via debounced AJAX; PDF re-uses the same service |
| PDF generation | `barryvdh/laravel-dompdf` |
| Language | Public form Indonesian; admin labels English (matches existing panel) |

## 3. Verified calculation model

Every formula below was reverse-engineered from the sheet's values and **cross-verified against the sheet's worked example** (all 17 steps matched exactly; adversarial verifier returned `high` confidence, zero issues). The example: land 300 m², Style Mediterranean, Zonasi R-3, Tipe bangunan Standar, Budget Rp 2,000,000,000.

**Inputs → weighting**
- `bobot = jabodetabek × existing_building × target_building × style` (one selected multiplier per factor group). Example: `1.00 × 1.00 × 1.00 × 1.15 = 1.15`.
- `base_price = building_types[tipe].price_per_m2` (Standar 5,500,000 / Optimal 8,500,000 / Premium 12,500,000).
- `harga_per_m2_bobot = base_price × bobot` → `5,500,000 × 1.15 = 6,325,000`.

**Budget chain**
- `gross_budget = budget + toleransi` → `2,000,000,000 + 0`.
- `dana_darurat = gross_budget × dana_darurat_pct` (default 10%) → `200,000,000`.
- `nett_budget = gross_budget − dana_darurat` → `1,800,000,000`.
- `total_alokasi = Σ percentage of every selected allocation EXCEPT the base "Bangunan" (100%)`. The base "Bangunan" is the `1` in the denominator, **not** part of the sum. Example selected extras: Landscape 15% + Biaya arsitek 4% + Pembersihan lahan 0.4% + Tes tanah 0.4% + Survey topografi 0.2% = **20%**.
- `nett_construction_budget = nett_budget / (1 + total_alokasi)` → `1,800,000,000 / 1.20 = 1,500,000,000`.

**Section A — Design-to-Budget**
- `budget_area = nett_construction_budget / harga_per_m2_bobot` → `1,500,000,000 / 6,325,000 = 237.2 m²` (displayed 1 decimal).

**Section B — Design-to-Regulation** (from Zonasi row × land area)
- `KDB = kdb% × land`, `KLB = klb × land`, `KTB = ktb% × land`, `RTH = rth% × land`.
  R-3 (60% / 1.20 / 60% / 40%), land 300 → `180 / 360 / 180 / 120 m²`.
- `luas_terbangun = KLB` (max buildable floor area) → `360 m²`.
- `regulasi_cost = luas_terbangun × harga_per_m2_bobot` → `360 × 6,325,000 = 2,277,000,000`.

**Section C — Design-to-Needs** (client-built room program)
- Per room row: `area_unit = room_areas[room][tipe_luasan].area`; `row_total = jumlah × area_unit`.
- `rooms_total = Σ row_total` (computed on **unrounded** values). Example `411.75 m²` (displayed 411.8).
- `sirkulasi = rooms_total × sirkulasi_pct` (default 20%) → `82.35` (displayed 82.4).
- `grand_total = rooms_total × (1 + sirkulasi_pct)` → `494.1 m²`.

**Section D — Summary comparison** (baseline = `nett_construction_budget`)
- **Budget row:** area `= budget_area` (237.2); cost `= nett_construction_budget` **directly** (this is the baseline, *not* area×harga). No selisih.
- **Regulasi row:** area `= luas_terbangun`; cost `= area × harga_per_m2_bobot`; `selisih = baseline − cost` → `−777,000,000`.
- **Kebutuhan rows** (cumulative by priority Utama → +Sekunder → +Tersier), each `× (1 + sirkulasi_pct)`:
  - Utama: `379.75 × 1.20 = 455.7 m²` → cost `455.7 × 6,325,000 = 2,882,302,500`; selisih `−1,382,302,500`.
  - +Sekunder: `399.75 × 1.20 = 479.7 m²` → `3,034,102,500`; selisih `−1,534,102,500`.
  - +Tersier: `411.75 × 1.20 = 494.1 m²` → `3,125,182,500`; selisih `−1,625,182,500`.

**Precision rule:** compute on full-precision floats; round only for display (areas 1 decimal, rupiah integer). Never feed rounded intermediates back in.

## 4. Data model

All tables use the existing conventions: `id`, `timestamps`, an `order` integer for display ordering where a list is user-sortable, and are editable via admin CRUD. Prefix `calc_` to namespace the feature.

| Table | Key columns | Rows (seeded) |
|---|---|---|
| `calc_factor_groups` | `key` (unique), `name`, `order` | 4 |
| `calc_factor_options` | `factor_group_id` FK, `label`, `multiplier` decimal(6,4), `note`, `is_default` bool, `order` | 16 |
| `calc_allocations` | `category` enum(pelaksanaan/perancangan/persiapan), `label`, `percentage` decimal(6,4), `is_base` bool, `is_default` bool, `note`, `order` | 15 |
| `calc_building_types` | `key` (unique), `name`, `price_per_m2` bigint, `order` | 3 |
| `calc_components` | `name`, `standar`, `optimal`, `premium` (text), `order` | 10 |
| `calc_zonasi` | `code` (unique), `name`, `kdb` decimal(6,4), `klb` decimal(6,4), `ktb` decimal(6,4), `rth` decimal(6,4), `order` | 5 |
| `calc_size_tiers` | `key` (unique), `name`, `description`, `order` | 6 |
| `calc_rooms` | `category` enum(service/public/private/luxury), `name`, `order` | 63 |
| `calc_room_areas` | `room_id` FK, `size_tier_id` FK, `panjang` decimal(6,2), `lebar` decimal(6,2), `area` decimal(8,2) | 378 (63×6) |
| `calc_settings` | `key` (unique), `value`, `note` | 3 (`dana_darurat_pct`, `sirkulasi_pct`, `toleransi_default`) |

**Percentages stored as decimal fractions** (e.g. 20% → `0.2000`, 0.4% → `0.0040`). `calc_settings` values stored as strings and cast on read.

**Models:** one Eloquent model per table under `App\Models\Calc\` (e.g. `App\Models\Calc\Room`, `Calc\RoomArea`, `Calc\FactorGroup`, …) to keep the namespace tidy. Relationships: `FactorGroup hasMany FactorOption`; `Room hasMany RoomArea`; `RoomArea belongsTo SizeTier`.

**Seeder:** `BudgetCalculatorSeeder` reads `database/data/budget_calculator_seed.json` (already extracted & verified from the sheet — 63 rooms, all tables) and populates every table idempotently (`updateOrCreate` on natural keys). Registered in `DatabaseSeeder`.

## 5. Calculation service

`App\Services\BudgetCalculatorService` — the single source of truth for the formulas.

- `calculate(array $input): array` — pure function of validated input + reference data (loaded from DB, cached per request). Returns a structured result: `weighting`, `budget` (gross/darurat/nett/nett_construction/area), `regulation` (kdb/klb/ktb/rth/luas_terbangun/cost), `needs` (per-row areas, subtotals per priority, total/sirkulasi/grand_total), `summary` (the comparison rows with area/cost/selisih).
- Input shape (validated by a FormRequest):
  - `nama_proyek`, `luas_tanah`, `lokasi_proyek`
  - `factors`: `{group_key: option_id}` for the 4 groups
  - `building_type_id`, `zonasi_id`
  - `budget`, `toleransi`, plus overridable `dana_darurat_pct`
  - `allocations`: array of selected allocation ids (base "Bangunan" always implicitly included)
  - `rooms`: array of `{room_id, size_tier_id, jumlah, prioritas}` (prioritas ∈ utama/sekunder/tersier)
- No DB writes. Deterministic. Fully unit-tested against the sheet example (§8).

## 6. Public calculator page

**Route:** `GET /kalkulator-budget` → `BudgetCalculatorController@show` (public, no auth). Rendered with a **standalone Blade using Tailwind CDN** to match the public site aesthetic, with Farka branding.

**Live calc:** `POST /kalkulator-budget/calculate` → returns JSON from `BudgetCalculatorService`. The page posts the current form state (debounced ~300 ms; also on explicit "Hitung" clicks and room add/remove) and renders the result panel. CSRF via the standard token.

**PDF:** `POST /kalkulator-budget/pdf` → same validation + service, renders `resources/views/kalkulator/pdf.blade.php` through dompdf, returns a download (`Estimasi-Budget-{nama_proyek}.pdf`).

**Page layout** (single scrolling form mirroring the sheet's flow, with a sticky/inline result summary):
1. **General information** — nama proyek, luas tanah (m²), lokasi.
2. **Faktor bobot** — 4 selects (Jabodetabek, Existing building, Target building, Style), each showing its multiplier; live `bobot` readout.
3. **Alokasi dana** — grouped checkboxes (Pelaksanaan / Perancangan / Persiapan) with percentages; "Bangunan" is fixed/base; Interior variants are mutually exclusive (radio within Pelaksanaan). Live `total_alokasi` readout.
4. **Design-to-Budget** — budget (Rp) + toleransi inputs, dana darurat %, tipe bangunan select (with the quality-components table shown for reference); outputs harga/m² berbobot + affordable area.
5. **Design-to-Regulation** — zonasi select; outputs KDB/KLB/KTB/RTH + luas terbangun + regulasi cost.
6. **Design-to-Needs** — a **room builder**: add rows from the catalog (grouped by category), each with jumlah, tipe luasan, and prioritas; outputs per-row area, subtotals, sirkulasi, grand total.
7. **Summary** — the comparison table (Budget / Regulasi / Kebutuhan Utama/+Sekunder/+Tersier) with cost + Lebih/Kurang, color-coded.
8. **Download PDF** button.

**Admin access:** a sidebar link (and/or a dashboard shortcut) opens the same public page (or an admin-wrapped variant); no separate calculator implementation.

## 7. Admin CRUD (reference data)

New sidebar section **"Budget Calculator"** (Bootstrap admin template, Phosphor/Tabler icons, DataTables — matching existing resources). Each reference table gets an index + shared `form` blade following the `CategoryController` pattern (permission middleware in constructor, `order` column, reorder + bulk-destroy where a sortable list makes sense).

Admin resources under `App\Http\Controllers\Admin\Calc\`:
- **Rooms** (`rooms` + nested 6 `room_areas` edited on the room form — one screen manages a room and its 6 tier areas).
- **Zonasi**, **Building Types & Price**, **Factor Groups + Options**, **Allocations**, **Quality Components**, **Size Tiers**, **Settings** (single edit page for globals).

**Permissions:** add one granular set governing the whole calculator config area — `view_calculator`, `create_calculator`, `edit_calculator`, `delete_calculator` — appended to `PermissionSeeder`, granted to `super_admin` (all) and `editor` in `DatabaseSeeder`. Sidebar entries gated with `@can('view_calculator')`.

**Routes:** grouped under the existing `admin` prefix/`auth` middleware in `routes/web.php`, following the established `Route::resource` + reorder/bulk-destroy ordering.

## 8. Testing

- **Unit:** `BudgetCalculatorServiceTest` seeds the reference data and asserts the service reproduces the sheet example to the rupiah / 1-decimal m²: bobot 1.15, harga 6,325,000, nett_construction 1,500,000,000, budget_area 237.2, KLB 360, regulasi_cost 2,277,000,000, needs grand_total 494.1, and all three Kebutuhan summary rows (2,882,302,500 / 3,034,102,500 / 3,125,182,500) with their selisih.
- **Feature:** public page renders (200, no auth); `/calculate` returns correct JSON for the example; `/pdf` returns a `application/pdf` download. Admin CRUD: create/edit/delete a room (+areas) and a zonasi row gated by permission; unauthorized user blocked.
- Follows the existing `tests/Feature/AdminPanelTest.php` conventions.

## 9. Architecture summary

```
routes/web.php
  public:  GET /kalkulator-budget, POST /kalkulator-budget/calculate, POST /kalkulator-budget/pdf
  admin:   Route::resource for each calc_* reference table (+ reorder/bulk-destroy)

app/Services/BudgetCalculatorService.php      ← single source of truth (formulas)
app/Http/Controllers/BudgetCalculatorController.php  ← public show/calculate/pdf
app/Http/Requests/CalculateBudgetRequest.php  ← input validation
app/Http/Controllers/Admin/Calc/*Controller.php ← reference-data CRUD
app/Models/Calc/*.php                          ← one model per table

database/migrations/2026_07_18_*_create_calc_*_tables.php
database/seeders/BudgetCalculatorSeeder.php    ← reads database/data/budget_calculator_seed.json
database/data/budget_calculator_seed.json      ← verified extract (63 rooms + all tables)

resources/views/kalkulator/show.blade.php      ← public form (Tailwind CDN)
resources/views/kalkulator/pdf.blade.php       ← dompdf template
resources/views/admin/calc/**/*.blade.php      ← admin index + form per resource
```

## 10. Implementation phases (single feature, phased build)

1. **Data layer** — migrations + `App\Models\Calc\*` + `BudgetCalculatorSeeder` (from the verified JSON). Verify counts (63 rooms, 378 areas, etc.).
2. **Calculation service + unit tests** — lock the formulas against the sheet example before any UI.
3. **Public calculator** — controller, FormRequest, `show` page with live AJAX, dompdf PDF.
4. **Admin CRUD** — permissions, sidebar, resource controllers + views for all reference tables.

Each phase is independently testable; the plan (writing-plans) will break these into concrete steps.

## 11. Out of scope (future, no schema churn required)

- Persisting client submissions as leads (add a `calc_submissions` table + admin list; the service already returns a serializable result).
- Emailing the PDF / capturing client contact.
- Interior/luxury allocation logic beyond simple additive percentages (current model matches the sheet).
- Multi-currency / i18n of the admin side.

## 12. Data provenance

Reference data was extracted from both sheet tabs via CSV export and **independently cross-verified** by a parallel extraction + adversarial verification pass (see `database/data/budget_calculator_seed.json`). Verifier verdict: rooms complete (63), tables complete, calc consistent — **high confidence, zero issues**.
