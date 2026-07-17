# Budget Calculator Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reproduce the studio's Google-Sheet budget calculator as a public + admin-accessible Laravel feature: a live client form that computes a faithful budget estimate (4 sections) and downloads a PDF, backed by fully admin-editable reference data.

**Architecture:** All reference/measurement data lives in `calc_*` DB tables (seeded from a verified JSON extract). A single `BudgetCalculatorService` (PHP) holds every formula and is the one source of truth — used by both the live AJAX preview and the dompdf export. Admin CRUD screens (following the existing `CategoryController` pattern) manage the reference data.

**Tech Stack:** Laravel 10 / PHP 8.1, MySQL (prod) + SQLite `:memory:` (tests), Bootstrap 5 admin template (existing), Tailwind CDN (public page), `spatie/laravel-permission`, `barryvdh/laravel-dompdf`.

## Global Constraints

- **Money is rounded to whole rupiah** (`round($x)` → int) in every service output. **Areas keep full float precision** in the service result and are formatted to **1 decimal** only in views/PDF.
- **Room area = `panjang × lebar`** (full precision, e.g. `2.5 × 2.5 = 6.25`). Never use a pre-rounded 1-decimal area — it breaks the summary totals (455.7 vs 455.8).
- **Percentages stored as decimal fractions** (20% → `0.2000`, 0.4% → `0.0040`), columns `decimal(6,4)`.
- **Never feed rounded intermediates back into a calculation.** Round only at the leaves (money) / at display (areas).
- Follow existing patterns verbatim: controllers like `app/Http/Controllers/Admin/CategoryController.php`; admin views like `resources/views/admin/categories/{index,form}.blade.php`; migrations as anonymous classes; permission gates `view_/create_/edit_/delete_*` in the controller constructor.
- Admin UI labels in **English**; public calculator UI in **Bahasa Indonesia**.
- Reference data source of truth for the seeder: `database/data/budget_calculator_seed.json` (already created & verified: 63 rooms, 4 factor groups/16 options, 15 allocations, 3 building types, 10 components, 5 zonasi, 6 size tiers, 3 settings).
- Sidebar caption + link gated by `@can('view_calculator')`; permission set added to `PermissionSeeder` and granted to `super_admin` (all) + `editor` in `DatabaseSeeder`.
- Run tests with: `php artisan test --filter <TestClass>` (SQLite in-memory, `RefreshDatabase`).

---

## File Structure

```
composer.json                                            (+ barryvdh/laravel-dompdf)
database/data/budget_calculator_seed.json                (exists — verified extract)
database/migrations/2026_07_18_000100_create_budget_calculator_tables.php
database/seeders/BudgetCalculatorSeeder.php
database/seeders/DatabaseSeeder.php                      (register seeder + calc permissions)
database/seeders/PermissionSeeder.php                    (+ calculator permissions)

app/Models/Calc/FactorGroup.php  FactorOption.php  Allocation.php  BuildingType.php
app/Models/Calc/Component.php  Zonasi.php  SizeTier.php  Room.php  RoomArea.php  Setting.php

app/Services/BudgetCalculatorService.php                 (all formulas — one source of truth)
app/Http/Requests/CalculateBudgetRequest.php
app/Http/Controllers/BudgetCalculatorController.php      (public: show/calculate/pdf)
app/Http/Controllers/Admin/Calc/RoomController.php  ZonasiController.php
  BuildingTypeController.php  FactorGroupController.php  AllocationController.php
  ComponentController.php  SizeTierController.php  SettingController.php

resources/views/kalkulator/show.blade.php                (public form + live AJAX)
resources/views/kalkulator/partials/result.blade.php     (server-rendered result fragment)
resources/views/kalkulator/pdf.blade.php                 (dompdf template)
resources/views/admin/calc/rooms/{index,form}.blade.php
resources/views/admin/calc/zonasi/{index,form}.blade.php
resources/views/admin/calc/building-types/{index,form}.blade.php
resources/views/admin/calc/factor-groups/{index,form}.blade.php
resources/views/admin/calc/allocations/{index,form}.blade.php
resources/views/admin/calc/components/{index,form}.blade.php
resources/views/admin/calc/size-tiers/{index,form}.blade.php
resources/views/admin/calc/settings/edit.blade.php

resources/views/admin/layouts/partials/sidebar.blade.php (+ Budget Calculator section)
routes/web.php                                           (+ public + admin.calc.* routes)

tests/Unit/BudgetCalculatorServiceTest.php
tests/Feature/BudgetCalculatorPublicTest.php
tests/Feature/CalculatorAdminTest.php
```

---

## Phase 1 — Data layer

### Task 1: Install dompdf

**Files:**
- Modify: `composer.json`

- [ ] **Step 1: Require the package**

Run: `composer require barryvdh/laravel-dompdf:^2.0`
Expected: package installs; `Barryvdh\DomPDF\ServiceProvider` auto-discovered.

- [ ] **Step 2: Verify autodiscovery**

Run: `php artisan package:discover --ansi`
Expected: output lists `barryvdh/laravel-dompdf`.

- [ ] **Step 3: Commit**

```bash
git add composer.json composer.lock
git commit -m "chore: add barryvdh/laravel-dompdf for budget calculator PDF export"
```

---

### Task 2: Migrations — all `calc_*` tables

**Files:**
- Create: `database/migrations/2026_07_18_000100_create_budget_calculator_tables.php`

**Interfaces:**
- Produces tables: `calc_factor_groups`, `calc_factor_options`, `calc_allocations`, `calc_building_types`, `calc_components`, `calc_zonasi`, `calc_size_tiers`, `calc_rooms`, `calc_room_areas`, `calc_settings`.

- [ ] **Step 1: Write the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calc_factor_groups', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();       // jabodetabek, existing_building, target_building, style
            $table->string('name');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_factor_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factor_group_id')->constrained('calc_factor_groups')->cascadeOnDelete();
            $table->string('label');
            $table->decimal('multiplier', 6, 4);   // 1.0000, 1.1500, 0.8000
            $table->string('note')->nullable();
            $table->boolean('is_default')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('category');            // pelaksanaan | perancangan | persiapan
            $table->string('label');
            $table->decimal('percentage', 6, 4);   // 0.1500 = 15%, 1.0000 = 100% (base)
            $table->boolean('is_base')->default(false);  // true only for "Bangunan"
            $table->boolean('is_default')->default(false);
            $table->string('note')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_building_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();       // standar | optimal | premium
            $table->string('name');
            $table->bigInteger('price_per_m2');    // 5500000 ...
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // Struktur, Dinding, ...
            $table->string('standar');
            $table->string('optimal');
            $table->string('premium');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_zonasi', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();      // R-1 ... R-5
            $table->string('name')->nullable();
            $table->decimal('kdb', 6, 4);          // 0.6000
            $table->decimal('klb', 6, 4);          // 1.2000 (ratio)
            $table->decimal('ktb', 6, 4);
            $table->decimal('rth', 6, 4);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_size_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();       // minimum ... eksekutif
            $table->string('name');                // Minimum ... Eksekutif (matches JSON tier names)
            $table->string('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('category');            // service | public | private | luxury
            $table->string('name');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_room_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('calc_rooms')->cascadeOnDelete();
            $table->foreignId('size_tier_id')->constrained('calc_size_tiers')->cascadeOnDelete();
            $table->decimal('panjang', 6, 2);
            $table->decimal('lebar', 6, 2);
            $table->decimal('area', 8, 2);         // = panjang * lebar (full precision, NOT display-rounded)
            $table->timestamps();
            $table->unique(['room_id', 'size_tier_id']);
        });

        Schema::create('calc_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();       // dana_darurat_pct, sirkulasi_pct, toleransi_default
            $table->string('value');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calc_room_areas');
        Schema::dropIfExists('calc_rooms');
        Schema::dropIfExists('calc_size_tiers');
        Schema::dropIfExists('calc_zonasi');
        Schema::dropIfExists('calc_components');
        Schema::dropIfExists('calc_building_types');
        Schema::dropIfExists('calc_allocations');
        Schema::dropIfExists('calc_factor_options');
        Schema::dropIfExists('calc_factor_groups');
        Schema::dropIfExists('calc_settings');
    }
};
```

- [ ] **Step 2: Run the migration**

Run: `php artisan migrate`
Expected: all 10 `calc_*` tables created, no errors.

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_07_18_000100_create_budget_calculator_tables.php
git commit -m "feat: add budget calculator reference-data tables"
```

---

### Task 3: Eloquent models

**Files:**
- Create: `app/Models/Calc/FactorGroup.php`, `FactorOption.php`, `Allocation.php`, `BuildingType.php`, `Component.php`, `Zonasi.php`, `SizeTier.php`, `Room.php`, `RoomArea.php`, `Setting.php`

**Interfaces:**
- Produces: `App\Models\Calc\Room` (`hasMany areas` → `RoomArea`), `RoomArea` (`belongsTo room`, `belongsTo sizeTier`), `FactorGroup` (`hasMany options` → `FactorOption`), plus flat models `Allocation`, `BuildingType`, `Component`, `Zonasi`, `SizeTier`, `Setting`.

- [ ] **Step 1: Write the models**

`app/Models/Calc/FactorGroup.php`:
```php
<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class FactorGroup extends Model
{
    protected $table = 'calc_factor_groups';
    protected $fillable = ['key', 'name', 'order'];

    public function options()
    {
        return $this->hasMany(FactorOption::class, 'factor_group_id')->orderBy('order');
    }
}
```

`app/Models/Calc/FactorOption.php`:
```php
<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class FactorOption extends Model
{
    protected $table = 'calc_factor_options';
    protected $fillable = ['factor_group_id', 'label', 'multiplier', 'note', 'is_default', 'order'];
    protected $casts = ['multiplier' => 'float', 'is_default' => 'boolean'];

    public function group()
    {
        return $this->belongsTo(FactorGroup::class, 'factor_group_id');
    }
}
```

`app/Models/Calc/Allocation.php`:
```php
<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class Allocation extends Model
{
    protected $table = 'calc_allocations';
    protected $fillable = ['category', 'label', 'percentage', 'is_base', 'is_default', 'note', 'order'];
    protected $casts = ['percentage' => 'float', 'is_base' => 'boolean', 'is_default' => 'boolean'];
}
```

`app/Models/Calc/BuildingType.php`:
```php
<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class BuildingType extends Model
{
    protected $table = 'calc_building_types';
    protected $fillable = ['key', 'name', 'price_per_m2', 'order'];
    protected $casts = ['price_per_m2' => 'integer'];
}
```

`app/Models/Calc/Component.php`:
```php
<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $table = 'calc_components';
    protected $fillable = ['name', 'standar', 'optimal', 'premium', 'order'];
}
```

`app/Models/Calc/Zonasi.php`:
```php
<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class Zonasi extends Model
{
    protected $table = 'calc_zonasi';
    protected $fillable = ['code', 'name', 'kdb', 'klb', 'ktb', 'rth', 'order'];
    protected $casts = ['kdb' => 'float', 'klb' => 'float', 'ktb' => 'float', 'rth' => 'float'];
}
```

`app/Models/Calc/SizeTier.php`:
```php
<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class SizeTier extends Model
{
    protected $table = 'calc_size_tiers';
    protected $fillable = ['key', 'name', 'description', 'order'];
}
```

`app/Models/Calc/Room.php`:
```php
<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'calc_rooms';
    protected $fillable = ['category', 'name', 'order'];

    public function areas()
    {
        return $this->hasMany(RoomArea::class, 'room_id');
    }
}
```

`app/Models/Calc/RoomArea.php`:
```php
<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class RoomArea extends Model
{
    protected $table = 'calc_room_areas';
    protected $fillable = ['room_id', 'size_tier_id', 'panjang', 'lebar', 'area'];
    protected $casts = ['panjang' => 'float', 'lebar' => 'float', 'area' => 'float'];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function sizeTier()
    {
        return $this->belongsTo(SizeTier::class, 'size_tier_id');
    }
}
```

`app/Models/Calc/Setting.php`:
```php
<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'calc_settings';
    protected $fillable = ['key', 'value', 'note'];

    public static function value(string $key, $default = null)
    {
        $row = static::where('key', $key)->first();
        return $row ? $row->value : $default;
    }
}
```

- [ ] **Step 2: Verify autoload**

Run: `php artisan tinker --execute="echo App\Models\Calc\Room::class;"`
Expected: prints `App\Models\Calc\Room`.

- [ ] **Step 3: Commit**

```bash
git add app/Models/Calc
git commit -m "feat: add budget calculator Eloquent models"
```

---

### Task 4: Seeder from verified JSON

**Files:**
- Create: `database/seeders/BudgetCalculatorSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/CalculatorAdminTest.php` (seeder-count test lives here for now)

**Interfaces:**
- Consumes: `database/data/budget_calculator_seed.json` shape `{rooms:[{category,name,areas:[{tier,p,l,area}]}], tables:{factor_groups,allocations,building_types,components,zonasi,size_tiers,settings}}`.
- Produces: fully populated `calc_*` tables. **Room area is stored as `round(p*l, 2)`**, NOT the JSON `area` field.

- [ ] **Step 1: Write the seeder**

```php
<?php

namespace Database\Seeders;

use App\Models\Calc\Allocation;
use App\Models\Calc\BuildingType;
use App\Models\Calc\Component;
use App\Models\Calc\FactorGroup;
use App\Models\Calc\FactorOption;
use App\Models\Calc\Room;
use App\Models\Calc\RoomArea;
use App\Models\Calc\Setting;
use App\Models\Calc\SizeTier;
use App\Models\Calc\Zonasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BudgetCalculatorSeeder extends Seeder
{
    public function run(): void
    {
        $json = json_decode(file_get_contents(database_path('data/budget_calculator_seed.json')), true);
        $t = $json['tables'];

        // Size tiers first — rooms reference them by name.
        foreach ($t['size_tiers'] as $i => $row) {
            SizeTier::updateOrCreate(
                ['key' => $row['key']],
                ['name' => $row['name'], 'description' => $row['description'] ?? null, 'order' => $i + 1]
            );
        }
        $tierByName = SizeTier::pluck('id', 'name'); // 'Minimum' => id ...

        // Factor groups + options.
        foreach ($t['factor_groups'] as $gi => $g) {
            $group = FactorGroup::updateOrCreate(
                ['key' => $g['key']],
                ['name' => $g['name'], 'order' => $gi + 1]
            );
            foreach ($g['options'] as $oi => $opt) {
                FactorOption::updateOrCreate(
                    ['factor_group_id' => $group->id, 'label' => $opt['label']],
                    [
                        'multiplier' => $opt['multiplier'],
                        'note' => $opt['note'] ?? null,
                        'is_default' => $opt['is_default'] ?? false,
                        'order' => $oi + 1,
                    ]
                );
            }
        }

        foreach ($t['allocations'] as $i => $a) {
            Allocation::updateOrCreate(
                ['category' => $a['category'], 'label' => $a['label']],
                [
                    'percentage' => $a['percentage'],
                    'is_base' => $a['is_base'] ?? false,
                    'is_default' => $a['is_default'] ?? false,
                    'order' => $i + 1,
                ]
            );
        }

        foreach ($t['building_types'] as $i => $b) {
            BuildingType::updateOrCreate(
                ['key' => $b['key']],
                ['name' => $b['name'], 'price_per_m2' => $b['price_per_m2'], 'order' => $i + 1]
            );
        }

        foreach ($t['components'] as $i => $c) {
            Component::updateOrCreate(
                ['name' => $c['name']],
                ['standar' => $c['standar'], 'optimal' => $c['optimal'], 'premium' => $c['premium'], 'order' => $i + 1]
            );
        }

        foreach ($t['zonasi'] as $i => $z) {
            Zonasi::updateOrCreate(
                ['code' => $z['code']],
                [
                    'name' => $z['name'] ?? null,
                    'kdb' => $z['kdb'], 'klb' => $z['klb'], 'ktb' => $z['ktb'], 'rth' => $z['rth'],
                    'order' => $i + 1,
                ]
            );
        }

        foreach ($t['settings'] as $s) {
            Setting::updateOrCreate(
                ['key' => $s['key']],
                ['value' => $s['value'], 'note' => $s['note'] ?? null]
            );
        }

        // Rooms + areas. area = panjang * lebar (full precision), NOT the JSON display value.
        foreach ($json['rooms'] as $ri => $r) {
            $room = Room::updateOrCreate(
                ['name' => $r['name'], 'category' => Str::lower($r['category'])],
                ['order' => $ri + 1]
            );
            foreach ($r['areas'] as $a) {
                $tierId = $tierByName[$a['tier']] ?? null;
                if (!$tierId) {
                    continue;
                }
                $panjang = $a['p'] ?? 0;
                $lebar = $a['l'] ?? 0;
                RoomArea::updateOrCreate(
                    ['room_id' => $room->id, 'size_tier_id' => $tierId],
                    ['panjang' => $panjang, 'lebar' => $lebar, 'area' => round($panjang * $lebar, 2)]
                );
            }
        }
    }
}
```

- [ ] **Step 2: Register in DatabaseSeeder**

In `database/seeders/DatabaseSeeder.php`, inside `run()`, add `BudgetCalculatorSeeder::class` to the `$this->call([...])` array (after `PermissionSeeder::class`):

```php
        $this->call([
            PermissionSeeder::class,
            BudgetCalculatorSeeder::class,
        ]);
```

- [ ] **Step 3: Write the failing count test**

Create `tests/Feature/CalculatorAdminTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Calc\Room;
use App\Models\Calc\RoomArea;
use App\Models\Calc\FactorOption;
use App\Models\Calc\Allocation;
use App\Models\Calc\Zonasi;
use App\Models\Calc\SizeTier;
use App\Models\Calc\BuildingType;
use App\Models\Calc\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_reference_data_is_seeded_completely(): void
    {
        $this->assertSame(63, Room::count());
        $this->assertSame(63 * 6, RoomArea::count());
        $this->assertSame(6, SizeTier::count());
        $this->assertSame(16, FactorOption::count());
        $this->assertSame(15, Allocation::count());
        $this->assertSame(5, Zonasi::count());
        $this->assertSame(3, BuildingType::count());
        $this->assertSame(10, Component::count());

        // Precision guard: Kamar mandi Premium must be 6.25 (2.5 x 2.5), not 6.3.
        $kamarMandi = Room::where('name', 'Kamar mandi')->where('category', 'private')->first();
        $premium = SizeTier::where('key', 'premium')->first();
        $area = RoomArea::where('room_id', $kamarMandi->id)->where('size_tier_id', $premium->id)->first();
        $this->assertEqualsWithDelta(6.25, $area->area, 0.001);
    }
}
```

- [ ] **Step 4: Run test to verify it fails**

Run: `php artisan test --filter CalculatorAdminTest`
Expected: FAIL (counts are 0 before seeder wired / mismatched).

- [ ] **Step 5: Run seeder and re-run test**

Run: `php artisan test --filter CalculatorAdminTest`
Expected: PASS (63 rooms, 378 areas, 6.25 precision).

- [ ] **Step 6: Commit**

```bash
git add database/seeders/BudgetCalculatorSeeder.php database/seeders/DatabaseSeeder.php tests/Feature/CalculatorAdminTest.php
git commit -m "feat: seed budget calculator reference data from verified extract"
```

---

## Phase 2 — Calculation service (the crown jewel)

### Task 5: `BudgetCalculatorService` + unit test against the sheet example

**Files:**
- Create: `app/Services/BudgetCalculatorService.php`
- Test: `tests/Unit/BudgetCalculatorServiceTest.php`

**Interfaces:**
- Consumes: models from Task 3, seeded data from Task 4.
- Produces: `calculate(array $input): array`. **Input contract:**
  ```php
  [
    'nama_proyek'       => string,
    'luas_tanah'        => float,          // m²
    'lokasi_proyek'     => ?string,
    'factor_option_ids' => int[],          // one selected FactorOption id per group
    'building_type_id'  => int,
    'zonasi_id'         => int,
    'budget'            => float,          // Rp
    'toleransi'         => float,          // Rp
    'dana_darurat_pct'  => float,          // 0.10
    'sirkulasi_pct'     => float,          // 0.20
    'allocation_ids'    => int[],          // selected Allocation ids (base is auto-excluded from sum)
    'rooms'             => [ ['room_id'=>int,'size_tier_id'=>int,'jumlah'=>int,'prioritas'=>'utama'|'sekunder'|'tersier'], ... ],
  ]
  ```
  **Output contract** (keys later tasks depend on):
  ```php
  [
    'weighting' => ['bobot'=>float, 'base_price'=>int, 'harga_per_m2_bobot'=>int, 'factors'=>[['label'=>..,'multiplier'=>..], ...]],
    'budget'    => ['gross'=>int,'dana_darurat'=>int,'nett'=>int,'total_alokasi'=>float,'nett_construction'=>int,'area'=>float],
    'regulation'=> ['code'=>string,'kdb'=>float,'klb'=>float,'ktb'=>float,'rth'=>float,'luas_terbangun'=>float,'cost'=>int],
    'needs'     => ['rows'=>[['name'=>..,'prioritas'=>..,'jumlah'=>..,'tier'=>..,'area_unit'=>float,'total'=>float], ...],
                    'subtotals'=>['utama'=>float,'sekunder'=>float,'tersier'=>float],
                    'rooms_total'=>float,'sirkulasi'=>float,'grand_total'=>float],
    'summary'   => ['baseline'=>int, 'rows'=>[['label'=>..,'area'=>float,'cost'=>int,'selisih'=>?int], ...]],
  ]
  ```

- [ ] **Step 1: Write the failing test (reproduces the sheet's worked example exactly)**

```php
<?php

namespace Tests\Unit;

use App\Models\Calc\Allocation;
use App\Models\Calc\BuildingType;
use App\Models\Calc\FactorOption;
use App\Models\Calc\Room;
use App\Models\Calc\SizeTier;
use App\Models\Calc\Zonasi;
use App\Services\BudgetCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    private function optionId(string $groupKey, string $label): int
    {
        return FactorOption::where('label', $label)
            ->whereHas('group', fn ($q) => $q->where('key', $groupKey))
            ->value('id');
    }

    private function sheetExampleInput(): array
    {
        $premium = SizeTier::where('key', 'premium')->value('id');
        $roomId = fn (string $name, string $cat) => Room::where('name', $name)->where('category', $cat)->value('id');

        // The 21-room program from the sheet (all Premium tier).
        $program = [
            ['Dapur kotor', 'service', 1, 'utama'],
            ['Area cuci', 'service', 1, 'utama'],
            ['Gudang', 'service', 1, 'utama'],
            ['Area utilitas', 'service', 1, 'utama'],
            ['Kamar tidur supir', 'service', 1, 'utama'],
            ['Kamar mandi supir', 'service', 1, 'utama'],
            ['Carport', 'public', 2, 'utama'],
            ['Garasi', 'public', 2, 'utama'],
            ['Gerbang utama', 'public', 1, 'utama'],
            ['Ruang tamu', 'public', 1, 'utama'],
            ['Ruang makan', 'public', 1, 'utama'],
            ['Dapur', 'public', 1, 'utama'],
            ['Toilet tamu', 'public', 2, 'utama'],
            ['Taman depan', 'public', 1, 'utama'],
            ['Kamar tidur utama', 'private', 1, 'utama'],
            ['Walk-in-closet', 'private', 1, 'utama'],
            ['Kamar mandi utama', 'private', 1, 'utama'],
            ['Kamar tidur', 'private', 2, 'utama'],
            ['Kamar mandi', 'private', 1, 'utama'],
            ['Ruang kerja', 'private', 1, 'sekunder'],
            ['Foyer', 'public', 1, 'tersier'],
        ];
        $rooms = [];
        foreach ($program as [$name, $cat, $qty, $prio]) {
            $rooms[] = [
                'room_id' => $roomId($name, $cat),
                'size_tier_id' => $premium,
                'jumlah' => $qty,
                'prioritas' => $prio,
            ];
        }

        // Default extra allocations: Landscape, Biaya arsitek, Pembersihan lahan, Tes tanah, Survey topografi.
        $allocIds = Allocation::where('is_default', true)->where('is_base', false)->pluck('id')->all();

        return [
            'nama_proyek' => 'Rizal',
            'luas_tanah' => 300,
            'lokasi_proyek' => 'Jakarta',
            'factor_option_ids' => [
                $this->optionId('jabodetabek', 'Ya'),
                $this->optionId('existing_building', 'Tidak'),
                $this->optionId('target_building', 'Bangun baru'),
                $this->optionId('style', 'Mediterranean'),
            ],
            'building_type_id' => BuildingType::where('key', 'standar')->value('id'),
            'zonasi_id' => Zonasi::where('code', 'R-3')->value('id'),
            'budget' => 2_000_000_000,
            'toleransi' => 0,
            'dana_darurat_pct' => 0.10,
            'sirkulasi_pct' => 0.20,
            'allocation_ids' => $allocIds,
            'rooms' => $rooms,
        ];
    }

    public function test_reproduces_sheet_example(): void
    {
        $result = (new BudgetCalculatorService())->calculate($this->sheetExampleInput());

        // Weighting
        $this->assertEqualsWithDelta(1.15, $result['weighting']['bobot'], 0.0001);
        $this->assertSame(5_500_000, $result['weighting']['base_price']);
        $this->assertSame(6_325_000, $result['weighting']['harga_per_m2_bobot']);

        // Budget chain
        $this->assertSame(2_000_000_000, $result['budget']['gross']);
        $this->assertSame(200_000_000, $result['budget']['dana_darurat']);
        $this->assertSame(1_800_000_000, $result['budget']['nett']);
        $this->assertEqualsWithDelta(0.20, $result['budget']['total_alokasi'], 0.0001);
        $this->assertSame(1_500_000_000, $result['budget']['nett_construction']);
        $this->assertEqualsWithDelta(237.2, round($result['budget']['area'], 1), 0.05);

        // Regulation (R-3, land 300)
        $this->assertEqualsWithDelta(180.0, $result['regulation']['kdb'], 0.05);
        $this->assertEqualsWithDelta(360.0, $result['regulation']['klb'], 0.05);
        $this->assertEqualsWithDelta(120.0, $result['regulation']['rth'], 0.05);
        $this->assertEqualsWithDelta(360.0, $result['regulation']['luas_terbangun'], 0.05);
        $this->assertSame(2_277_000_000, $result['regulation']['cost']);

        // Needs
        $this->assertEqualsWithDelta(379.75, $result['needs']['subtotals']['utama'], 0.01);
        $this->assertEqualsWithDelta(20.0, $result['needs']['subtotals']['sekunder'], 0.01);
        $this->assertEqualsWithDelta(12.0, $result['needs']['subtotals']['tersier'], 0.01);
        $this->assertEqualsWithDelta(411.75, $result['needs']['rooms_total'], 0.01);
        $this->assertEqualsWithDelta(494.1, round($result['needs']['grand_total'], 1), 0.05);

        // Summary rows: [Budget, Regulasi, Kebutuhan Utama, +Sekunder, +Tersier]
        $rows = $result['summary']['rows'];
        $this->assertSame(1_500_000_000, $result['summary']['baseline']);
        $this->assertSame(1_500_000_000, $rows[0]['cost']);       // Budget baseline
        $this->assertNull($rows[0]['selisih']);
        $this->assertSame(2_277_000_000, $rows[1]['cost']);       // Regulasi
        $this->assertSame(-777_000_000, $rows[1]['selisih']);
        $this->assertSame(2_882_302_500, $rows[2]['cost']);       // Kebutuhan Utama
        $this->assertSame(-1_382_302_500, $rows[2]['selisih']);
        $this->assertSame(3_034_102_500, $rows[3]['cost']);       // +Sekunder
        $this->assertSame(3_125_182_500, $rows[4]['cost']);       // +Tersier
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter BudgetCalculatorServiceTest`
Expected: FAIL with "Class BudgetCalculatorService not found".

- [ ] **Step 3: Write the service**

```php
<?php

namespace App\Services;

use App\Models\Calc\Allocation;
use App\Models\Calc\BuildingType;
use App\Models\Calc\FactorOption;
use App\Models\Calc\Room;
use App\Models\Calc\RoomArea;
use App\Models\Calc\Zonasi;

class BudgetCalculatorService
{
    /**
     * Compute the full budget estimate. Money is rounded to whole rupiah;
     * areas keep full precision (round only for display).
     */
    public function calculate(array $in): array
    {
        // --- Weighting ---
        $options = FactorOption::whereIn('id', $in['factor_option_ids'] ?? [])->get();
        $bobot = 1.0;
        $factors = [];
        foreach ($options as $opt) {
            $bobot *= (float) $opt->multiplier;
            $factors[] = ['label' => $opt->label, 'multiplier' => (float) $opt->multiplier];
        }

        $basePrice = (int) BuildingType::whereKey($in['building_type_id'])->value('price_per_m2');
        $hargaBobot = (int) round($basePrice * $bobot);

        // --- Budget chain ---
        $gross = (int) round($in['budget'] + $in['toleransi']);
        $danaDarurat = (int) round($gross * $in['dana_darurat_pct']);
        $nett = $gross - $danaDarurat;

        // total_alokasi = sum of selected NON-base allocation percentages.
        $totalAlokasi = (float) Allocation::whereIn('id', $in['allocation_ids'] ?? [])
            ->where('is_base', false)
            ->sum('percentage');
        $nettConstruction = (int) round($nett / (1 + $totalAlokasi));

        $budgetArea = $hargaBobot > 0 ? $nettConstruction / $hargaBobot : 0.0;

        // --- Regulation ---
        $zonasi = Zonasi::findOrFail($in['zonasi_id']);
        $land = (float) $in['luas_tanah'];
        $kdb = $zonasi->kdb * $land;
        $klb = $zonasi->klb * $land;
        $ktb = $zonasi->ktb * $land;
        $rth = $zonasi->rth * $land;
        $luasTerbangun = $klb;
        $regulasiCost = (int) round($luasTerbangun * $hargaBobot);

        // --- Needs ---
        $rows = [];
        $subtotals = ['utama' => 0.0, 'sekunder' => 0.0, 'tersier' => 0.0];
        foreach ($in['rooms'] ?? [] as $r) {
            $areaUnit = (float) RoomArea::where('room_id', $r['room_id'])
                ->where('size_tier_id', $r['size_tier_id'])
                ->value('area');
            $qty = (int) $r['jumlah'];
            $total = $areaUnit * $qty;
            $prio = $r['prioritas'];
            if (isset($subtotals[$prio])) {
                $subtotals[$prio] += $total;
            }
            $rows[] = [
                'name' => Room::whereKey($r['room_id'])->value('name'),
                'prioritas' => $prio,
                'jumlah' => $qty,
                'tier' => \App\Models\Calc\SizeTier::whereKey($r['size_tier_id'])->value('name'),
                'area_unit' => $areaUnit,
                'total' => $total,
            ];
        }
        $roomsTotal = $subtotals['utama'] + $subtotals['sekunder'] + $subtotals['tersier'];
        $sirkulasiPct = (float) $in['sirkulasi_pct'];
        $sirkulasi = $roomsTotal * $sirkulasiPct;
        $grandTotal = $roomsTotal * (1 + $sirkulasiPct);

        // --- Summary ---
        $baseline = $nettConstruction;
        $mult = 1 + $sirkulasiPct;
        $utamaArea = $subtotals['utama'] * $mult;
        $sekunderArea = ($subtotals['utama'] + $subtotals['sekunder']) * $mult;
        $tersierArea = ($subtotals['utama'] + $subtotals['sekunder'] + $subtotals['tersier']) * $mult;

        $cost = fn (float $area) => (int) round($area * $hargaBobot);
        $summaryRows = [
            ['label' => 'Budget', 'area' => $budgetArea, 'cost' => $baseline, 'selisih' => null],
            ['label' => 'Regulasi', 'area' => $luasTerbangun, 'cost' => $regulasiCost, 'selisih' => $baseline - $regulasiCost],
            ['label' => 'Kebutuhan (Utama)', 'area' => $utamaArea, 'cost' => $cost($utamaArea), 'selisih' => $baseline - $cost($utamaArea)],
            ['label' => 'Kebutuhan (+Sekunder)', 'area' => $sekunderArea, 'cost' => $cost($sekunderArea), 'selisih' => $baseline - $cost($sekunderArea)],
            ['label' => 'Kebutuhan (+Tersier)', 'area' => $tersierArea, 'cost' => $cost($tersierArea), 'selisih' => $baseline - $cost($tersierArea)],
        ];

        return [
            'weighting' => [
                'bobot' => $bobot,
                'base_price' => $basePrice,
                'harga_per_m2_bobot' => $hargaBobot,
                'factors' => $factors,
            ],
            'budget' => [
                'gross' => $gross,
                'dana_darurat' => $danaDarurat,
                'nett' => $nett,
                'total_alokasi' => $totalAlokasi,
                'nett_construction' => $nettConstruction,
                'area' => $budgetArea,
            ],
            'regulation' => [
                'code' => $zonasi->code,
                'kdb' => $kdb, 'klb' => $klb, 'ktb' => $ktb, 'rth' => $rth,
                'luas_terbangun' => $luasTerbangun,
                'cost' => $regulasiCost,
            ],
            'needs' => [
                'rows' => $rows,
                'subtotals' => $subtotals,
                'rooms_total' => $roomsTotal,
                'sirkulasi' => $sirkulasi,
                'grand_total' => $grandTotal,
            ],
            'summary' => [
                'baseline' => $baseline,
                'rows' => $summaryRows,
            ],
        ];
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter BudgetCalculatorServiceTest`
Expected: PASS (all sheet numbers reproduced).

- [ ] **Step 5: Commit**

```bash
git add app/Services/BudgetCalculatorService.php tests/Unit/BudgetCalculatorServiceTest.php
git commit -m "feat: add BudgetCalculatorService reproducing the sheet formula chain"
```

---

## Phase 3 — Public calculator

### Task 6: Routes, FormRequest, controller (show + calculate JSON)

**Files:**
- Modify: `routes/web.php`
- Create: `app/Http/Requests/CalculateBudgetRequest.php`
- Create: `app/Http/Controllers/BudgetCalculatorController.php`
- Test: `tests/Feature/BudgetCalculatorPublicTest.php`

**Interfaces:**
- Consumes: `BudgetCalculatorService::calculate()` (Task 5).
- Produces: routes named `kalkulator.show`, `kalkulator.calculate`, `kalkulator.pdf`; `CalculateBudgetRequest::calculatorInput()` returning the service input array.

- [ ] **Step 1: Add public routes**

In `routes/web.php`, after the `Route::get('/sitemap.xml', ...)` line and before the admin group, add:
```php
// Public budget calculator
Route::get('/kalkulator-budget', [\App\Http\Controllers\BudgetCalculatorController::class, 'show'])->name('kalkulator.show');
Route::post('/kalkulator-budget/calculate', [\App\Http\Controllers\BudgetCalculatorController::class, 'calculate'])->name('kalkulator.calculate');
Route::post('/kalkulator-budget/pdf', [\App\Http\Controllers\BudgetCalculatorController::class, 'pdf'])->name('kalkulator.pdf');
```

- [ ] **Step 2: Write the FormRequest**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public calculator
    }

    public function rules(): array
    {
        return [
            'nama_proyek' => ['nullable', 'string', 'max:255'],
            'luas_tanah' => ['required', 'numeric', 'min:1'],
            'lokasi_proyek' => ['nullable', 'string', 'max:255'],
            'factor_option_ids' => ['required', 'array', 'min:1'],
            'factor_option_ids.*' => ['integer', 'exists:calc_factor_options,id'],
            'building_type_id' => ['required', 'integer', 'exists:calc_building_types,id'],
            'zonasi_id' => ['required', 'integer', 'exists:calc_zonasi,id'],
            'budget' => ['required', 'numeric', 'min:0'],
            'toleransi' => ['nullable', 'numeric', 'min:0'],
            'dana_darurat_pct' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'sirkulasi_pct' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'allocation_ids' => ['nullable', 'array'],
            'allocation_ids.*' => ['integer', 'exists:calc_allocations,id'],
            'rooms' => ['nullable', 'array'],
            'rooms.*.room_id' => ['required_with:rooms', 'integer', 'exists:calc_rooms,id'],
            'rooms.*.size_tier_id' => ['required_with:rooms', 'integer', 'exists:calc_size_tiers,id'],
            'rooms.*.jumlah' => ['required_with:rooms', 'integer', 'min:1'],
            'rooms.*.prioritas' => ['required_with:rooms', 'in:utama,sekunder,tersier'],
        ];
    }

    /** Normalise into the BudgetCalculatorService input shape, applying setting defaults. */
    public function calculatorInput(): array
    {
        $v = $this->validated();
        $darurat = $v['dana_darurat_pct'] ?? (float) \App\Models\Calc\Setting::value('dana_darurat_pct', 0.10);
        $sirkulasi = $v['sirkulasi_pct'] ?? (float) \App\Models\Calc\Setting::value('sirkulasi_pct', 0.20);

        return [
            'nama_proyek' => $v['nama_proyek'] ?? '',
            'luas_tanah' => (float) $v['luas_tanah'],
            'lokasi_proyek' => $v['lokasi_proyek'] ?? null,
            'factor_option_ids' => $v['factor_option_ids'],
            'building_type_id' => (int) $v['building_type_id'],
            'zonasi_id' => (int) $v['zonasi_id'],
            'budget' => (float) $v['budget'],
            'toleransi' => (float) ($v['toleransi'] ?? 0),
            'dana_darurat_pct' => (float) $darurat,
            'sirkulasi_pct' => (float) $sirkulasi,
            'allocation_ids' => $v['allocation_ids'] ?? [],
            'rooms' => $v['rooms'] ?? [],
        ];
    }
}
```

- [ ] **Step 3: Write the controller (show + calculate; pdf stubbed until Task 8)**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalculateBudgetRequest;
use App\Models\Calc\Allocation;
use App\Models\Calc\BuildingType;
use App\Models\Calc\Component;
use App\Models\Calc\FactorGroup;
use App\Models\Calc\Room;
use App\Models\Calc\Setting;
use App\Models\Calc\SizeTier;
use App\Models\Calc\Zonasi;
use App\Services\BudgetCalculatorService;

class BudgetCalculatorController extends Controller
{
    public function show()
    {
        return view('kalkulator.show', $this->referenceData());
    }

    public function calculate(CalculateBudgetRequest $request, BudgetCalculatorService $service)
    {
        return response()->json($service->calculate($request->calculatorInput()));
    }

    /** Shared reference data for the form (Task 8 reuses this for the PDF). */
    private function referenceData(): array
    {
        return [
            'factorGroups' => FactorGroup::with('options')->orderBy('order')->get(),
            'allocations' => Allocation::orderBy('order')->get()->groupBy('category'),
            'buildingTypes' => BuildingType::orderBy('order')->get(),
            'components' => Component::orderBy('order')->get(),
            'zonasiList' => Zonasi::orderBy('order')->get(),
            'sizeTiers' => SizeTier::orderBy('order')->get(),
            'rooms' => Room::with('areas')->orderBy('category')->orderBy('order')->get(),
            'settings' => [
                'dana_darurat_pct' => (float) Setting::value('dana_darurat_pct', 0.10),
                'sirkulasi_pct' => (float) Setting::value('sirkulasi_pct', 0.20),
                'toleransi_default' => (float) Setting::value('toleransi_default', 0),
            ],
        ];
    }
}
```

- [ ] **Step 4: Write the failing feature test**

Create `tests/Feature/BudgetCalculatorPublicTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Calc\BuildingType;
use App\Models\Calc\FactorOption;
use App\Models\Calc\Zonasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCalculatorPublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_public_page_renders_without_auth(): void
    {
        $this->get(route('kalkulator.show'))->assertOk()->assertViewIs('kalkulator.show');
    }

    public function test_calculate_endpoint_returns_expected_numbers(): void
    {
        $opt = fn (string $g, string $l) => FactorOption::where('label', $l)
            ->whereHas('group', fn ($q) => $q->where('key', $g))->value('id');

        $payload = [
            'nama_proyek' => 'Rizal',
            'luas_tanah' => 300,
            'factor_option_ids' => [
                $opt('jabodetabek', 'Ya'), $opt('existing_building', 'Tidak'),
                $opt('target_building', 'Bangun baru'), $opt('style', 'Mediterranean'),
            ],
            'building_type_id' => BuildingType::where('key', 'standar')->value('id'),
            'zonasi_id' => Zonasi::where('code', 'R-3')->value('id'),
            'budget' => 2_000_000_000,
            'toleransi' => 0,
            'dana_darurat_pct' => 0.10,
            'sirkulasi_pct' => 0.20,
            'allocation_ids' => \App\Models\Calc\Allocation::where('is_default', true)->where('is_base', false)->pluck('id')->all(),
            'rooms' => [],
        ];

        $res = $this->postJson(route('kalkulator.calculate'), $payload);
        $res->assertOk()
            ->assertJsonPath('weighting.harga_per_m2_bobot', 6_325_000)
            ->assertJsonPath('budget.nett_construction', 1_500_000_000)
            ->assertJsonPath('regulation.cost', 2_277_000_000);
    }

    public function test_calculate_validates_input(): void
    {
        $this->postJson(route('kalkulator.calculate'), [])->assertStatus(422);
    }
}
```

Note: `test_public_page_renders_without_auth` will pass only after Task 7 creates the view. Run the other two now.

- [ ] **Step 5: Run the calculate tests**

Run: `php artisan test --filter "BudgetCalculatorPublicTest::test_calculate_endpoint_returns_expected_numbers"`
Run: `php artisan test --filter "BudgetCalculatorPublicTest::test_calculate_validates_input"`
Expected: both PASS.

- [ ] **Step 6: Commit**

```bash
git add routes/web.php app/Http/Requests/CalculateBudgetRequest.php app/Http/Controllers/BudgetCalculatorController.php tests/Feature/BudgetCalculatorPublicTest.php
git commit -m "feat: public budget calculator routes, request validation, calculate endpoint"
```

---

### Task 7: Public calculator page (form + live AJAX)

**Files:**
- Create: `resources/views/kalkulator/show.blade.php`

**Interfaces:**
- Consumes: `referenceData()` view vars (Task 6) + `POST kalkulator.calculate` JSON.

- [ ] **Step 1: Write the Blade page**

Standalone Tailwind-CDN page (matches the public site). Renders every input section from the reference data, posts debounced state to `kalkulator.calculate`, and paints the result. Full file:

```blade
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kalkulator Budget | Farka Studio</title>
    <link rel="icon" href="{{ asset('farkalogo.svg') }}" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-50 text-neutral-900">
<div class="max-w-6xl mx-auto px-4 py-8">
    <header class="mb-8">
        <h1 class="text-2xl font-semibold">Kalkulator Budget Proyek</h1>
        <p class="text-neutral-500">Estimasi kebutuhan biaya pembangunan berdasarkan preferensi Anda. *Angka hanya asumsi dan bukan final.</p>
    </header>

    <div class="grid lg:grid-cols-3 gap-6">
        <form id="calc-form" class="lg:col-span-2 space-y-8">
            {{-- 1. General --}}
            <section class="bg-white rounded-xl shadow-sm p-5">
                <h2 class="font-semibold mb-3">Informasi Umum</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <label class="block"><span class="text-sm">Nama proyek</span>
                        <input name="nama_proyek" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="Nama proyek"></label>
                    <label class="block"><span class="text-sm">Luas tanah (m²)</span>
                        <input name="luas_tanah" type="number" step="0.1" value="300" required class="mt-1 w-full border rounded-lg px-3 py-2"></label>
                    <label class="block sm:col-span-2"><span class="text-sm">Lokasi proyek</span>
                        <input name="lokasi_proyek" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="Kota / wilayah"></label>
                </div>
            </section>

            {{-- 2. Weighting factors --}}
            <section class="bg-white rounded-xl shadow-sm p-5">
                <h2 class="font-semibold mb-3">Faktor Bobot</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach($factorGroups as $group)
                        <label class="block"><span class="text-sm">{{ $group->name }}</span>
                            <select name="factor_option_ids[]" class="mt-1 w-full border rounded-lg px-3 py-2">
                                @foreach($group->options as $opt)
                                    <option value="{{ $opt->id }}" @selected($opt->is_default)>{{ $opt->label }} (×{{ rtrim(rtrim(number_format($opt->multiplier,2),'0'),'.') }})</option>
                                @endforeach
                            </select>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- 3. Allocations --}}
            <section class="bg-white rounded-xl shadow-sm p-5">
                <h2 class="font-semibold mb-3">Alokasi Dana</h2>
                @foreach($allocations as $category => $items)
                    <div class="mb-3">
                        <div class="text-sm font-medium capitalize text-neutral-500 mb-1">{{ $category }}</div>
                        <div class="grid sm:grid-cols-2 gap-2">
                            @foreach($items as $a)
                                <label class="flex items-center gap-2 text-sm {{ $a->is_base ? 'opacity-70' : '' }}">
                                    <input type="checkbox" name="allocation_ids[]" value="{{ $a->id }}"
                                        @checked($a->is_default || $a->is_base) @disabled($a->is_base)>
                                    {{ $a->label }} ({{ rtrim(rtrim(number_format($a->percentage*100,2),'0'),'.') }}%)
                                    @if($a->is_base)<input type="hidden" name="allocation_ids[]" value="{{ $a->id }}">@endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </section>

            {{-- 4. Design-to-Budget --}}
            <section class="bg-white rounded-xl shadow-sm p-5">
                <h2 class="font-semibold mb-3">Design-to-Budget</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <label class="block"><span class="text-sm">Budget (Rp)</span>
                        <input name="budget" type="number" value="2000000000" required class="mt-1 w-full border rounded-lg px-3 py-2"></label>
                    <label class="block"><span class="text-sm">Toleransi (Rp)</span>
                        <input name="toleransi" type="number" value="{{ (int) $settings['toleransi_default'] }}" class="mt-1 w-full border rounded-lg px-3 py-2"></label>
                    <label class="block"><span class="text-sm">Dana darurat (%)</span>
                        <input name="dana_darurat_pct_display" type="number" step="1" value="{{ $settings['dana_darurat_pct']*100 }}" class="mt-1 w-full border rounded-lg px-3 py-2"></label>
                    <label class="block"><span class="text-sm">Tipe bangunan</span>
                        <select name="building_type_id" class="mt-1 w-full border rounded-lg px-3 py-2">
                            @foreach($buildingTypes as $bt)
                                <option value="{{ $bt->id }}">{{ $bt->name }} (Rp {{ number_format($bt->price_per_m2,0,',','.') }}/m²)</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            {{-- 5. Design-to-Regulation --}}
            <section class="bg-white rounded-xl shadow-sm p-5">
                <h2 class="font-semibold mb-3">Design-to-Regulation</h2>
                <label class="block max-w-sm"><span class="text-sm">Zonasi lahan</span>
                    <select name="zonasi_id" class="mt-1 w-full border rounded-lg px-3 py-2">
                        @foreach($zonasiList as $z)
                            <option value="{{ $z->id }}" @selected($z->code==='R-3')>{{ $z->code }} — {{ $z->name }}</option>
                        @endforeach
                    </select>
                </label>
            </section>

            {{-- 6. Design-to-Needs (room builder) --}}
            <section class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Design-to-Needs</h2>
                    <button type="button" id="add-room" class="text-sm bg-neutral-900 text-white rounded-lg px-3 py-1.5">+ Tambah ruangan</button>
                </div>
                <input type="hidden" name="sirkulasi_pct" value="{{ $settings['sirkulasi_pct'] }}">
                <div id="rooms-body" class="space-y-2"></div>
                <p class="text-xs text-neutral-400 mt-2">Sirkulasi {{ (int) round($settings['sirkulasi_pct']*100) }}% ditambahkan otomatis.</p>
            </section>
        </form>

        {{-- Result panel --}}
        <aside class="lg:col-span-1">
            <div id="result" class="bg-white rounded-xl shadow-sm p-5 sticky top-6 text-sm">
                <p class="text-neutral-400">Isi form untuk melihat estimasi…</p>
            </div>
            <button id="download-pdf" class="mt-4 w-full bg-emerald-600 text-white rounded-lg py-2.5 font-medium">Download PDF</button>
        </aside>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const ROOMS = @json($rooms);
const TIERS = @json($sizeTiers);

function rupiah(n){ return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }
function m2(n){ return (Math.round(n*10)/10).toLocaleString('id-ID') + ' m²'; }

// --- Room builder ---
function roomRow(){
    const wrap = document.createElement('div');
    wrap.className = 'grid grid-cols-12 gap-2 items-center room-row';
    const opts = ROOMS.map(r => `<option value="${r.id}">${r.name} (${r.category})</option>`).join('');
    const tierOpts = TIERS.map(t => `<option value="${t.id}" ${t.key==='premium'?'selected':''}>${t.name}</option>`).join('');
    wrap.innerHTML = `
        <select class="col-span-4 border rounded-lg px-2 py-1.5 r-room">${opts}</select>
        <select class="col-span-3 border rounded-lg px-2 py-1.5 r-tier">${tierOpts}</select>
        <input type="number" min="1" value="1" class="col-span-2 border rounded-lg px-2 py-1.5 r-qty">
        <select class="col-span-2 border rounded-lg px-2 py-1.5 r-prio">
            <option value="utama">Utama</option><option value="sekunder">Sekunder</option><option value="tersier">Tersier</option>
        </select>
        <button type="button" class="col-span-1 text-red-500 r-del">✕</button>`;
    wrap.querySelector('.r-del').addEventListener('click', () => { wrap.remove(); recalc(); });
    wrap.querySelectorAll('select,input').forEach(el => el.addEventListener('change', recalc));
    return wrap;
}
document.getElementById('add-room').addEventListener('click', () => {
    document.getElementById('rooms-body').appendChild(roomRow());
    recalc();
});

// --- Collect payload ---
function payload(){
    const f = document.getElementById('calc-form');
    const fd = new FormData(f);
    const data = {
        nama_proyek: fd.get('nama_proyek') || '',
        luas_tanah: parseFloat(fd.get('luas_tanah')) || 0,
        lokasi_proyek: fd.get('lokasi_proyek') || '',
        factor_option_ids: fd.getAll('factor_option_ids[]').map(Number),
        building_type_id: Number(fd.get('building_type_id')),
        zonasi_id: Number(fd.get('zonasi_id')),
        budget: parseFloat(fd.get('budget')) || 0,
        toleransi: parseFloat(fd.get('toleransi')) || 0,
        dana_darurat_pct: (parseFloat(fd.get('dana_darurat_pct_display')) || 0) / 100,
        sirkulasi_pct: parseFloat(fd.get('sirkulasi_pct')) || 0,
        allocation_ids: fd.getAll('allocation_ids[]').map(Number),
        rooms: [...document.querySelectorAll('.room-row')].map(row => ({
            room_id: Number(row.querySelector('.r-room').value),
            size_tier_id: Number(row.querySelector('.r-tier').value),
            jumlah: Number(row.querySelector('.r-qty').value),
            prioritas: row.querySelector('.r-prio').value,
        })),
    };
    return data;
}

// --- Live calc ---
let timer = null;
function recalc(){
    clearTimeout(timer);
    timer = setTimeout(async () => {
        const res = await fetch("{{ route('kalkulator.calculate') }}", {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body: JSON.stringify(payload()),
        });
        if(!res.ok){ document.getElementById('result').innerHTML = '<p class="text-red-500">Lengkapi input yang wajib diisi.</p>'; return; }
        render(await res.json());
    }, 300);
}

function render(r){
    const s = r.summary.rows;
    const selis = (v) => v===null ? '' : `<span class="${v<0?'text-red-600':'text-emerald-600'}">${rupiah(v)}</span>`;
    document.getElementById('result').innerHTML = `
        <h3 class="font-semibold mb-2">Ringkasan Estimasi</h3>
        <div class="space-y-1">
            <div class="flex justify-between"><span>Bobot</span><b>×${r.weighting.bobot.toFixed(2)}</b></div>
            <div class="flex justify-between"><span>Harga/m² berbobot</span><b>${rupiah(r.weighting.harga_per_m2_bobot)}</b></div>
            <div class="flex justify-between"><span>Nett construction</span><b>${rupiah(r.budget.nett_construction)}</b></div>
            <div class="flex justify-between"><span>Luas by budget</span><b>${m2(r.budget.area)}</b></div>
            <div class="flex justify-between"><span>Luas terbangun (regulasi)</span><b>${m2(r.regulation.luas_terbangun)}</b></div>
            <div class="flex justify-between"><span>Kebutuhan (grand total)</span><b>${m2(r.needs.grand_total)}</b></div>
        </div>
        <table class="w-full mt-3 text-xs border-t">
            <thead><tr class="text-left text-neutral-400"><th class="py-1">Skenario</th><th>Luas</th><th>Biaya</th><th>Selisih</th></tr></thead>
            <tbody>${s.map(row => `<tr class="border-t"><td class="py-1">${row.label}</td><td>${m2(row.area)}</td><td>${rupiah(row.cost)}</td><td>${selis(row.selisih)}</td></tr>`).join('')}</tbody>
        </table>`;
}

// --- PDF: submit collected payload to the pdf route ---
document.getElementById('download-pdf').addEventListener('click', () => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('kalkulator.pdf') }}";
    const add = (k,v) => { const i=document.createElement('input'); i.type='hidden'; i.name=k; i.value=v; form.appendChild(i); };
    add('_token', CSRF);
    const p = payload();
    const walk = (obj, prefix) => {
        Object.entries(obj).forEach(([k,v]) => {
            const key = prefix ? `${prefix}[${k}]` : k;
            if (Array.isArray(v)) v.forEach((item,i) => (typeof item==='object') ? walk(item,`${key}[${i}]`) : add(`${key}[${i}]`, item));
            else if (v && typeof v==='object') walk(v, key);
            else add(key, v);
        });
    };
    walk(p, '');
    document.body.appendChild(form);
    form.submit();
    form.remove();
});

// initial
document.getElementById('calc-form').addEventListener('change', recalc);
recalc();
</script>
</body>
</html>
```

- [ ] **Step 2: Run the render test**

Run: `php artisan test --filter "BudgetCalculatorPublicTest::test_public_page_renders_without_auth"`
Expected: PASS.

- [ ] **Step 3: Manual smoke check (optional)**

Run: `php artisan serve` then open `http://127.0.0.1:8000/kalkulator-budget`; change inputs → result panel updates; add rooms → totals change.

- [ ] **Step 4: Commit**

```bash
git add resources/views/kalkulator/show.blade.php
git commit -m "feat: public budget calculator form with live AJAX preview"
```

---

### Task 8: PDF export

**Files:**
- Create: `resources/views/kalkulator/pdf.blade.php`
- Modify: `app/Http/Controllers/BudgetCalculatorController.php` (add `pdf()`)
- Test: `tests/Feature/BudgetCalculatorPublicTest.php` (add pdf test)

**Interfaces:**
- Consumes: `BudgetCalculatorService::calculate()`, `CalculateBudgetRequest`.

- [ ] **Step 1: Add the `pdf()` method**

Add `use Barryvdh\DomPDF\Facade\Pdf;` at the top of `BudgetCalculatorController.php`, then add:
```php
    public function pdf(CalculateBudgetRequest $request, BudgetCalculatorService $service)
    {
        $input = $request->calculatorInput();
        $result = $service->calculate($input);
        $name = $input['nama_proyek'] !== '' ? $input['nama_proyek'] : 'Proyek';
        $filename = 'Estimasi-Budget-' . \Illuminate\Support\Str::slug($name) . '.pdf';

        return Pdf::loadView('kalkulator.pdf', [
            'input' => $input,
            'result' => $result,
        ])->download($filename);
    }
```

- [ ] **Step 2: Write the PDF Blade**

```blade
@php
    function rp($n){ return 'Rp ' . number_format(round($n), 0, ',', '.'); }
    function ar($n){ return number_format(round($n,1), 1, ',', '.') . ' m²'; }
@endphp
<!doctype html>
<html>
<head><meta charset="utf-8">
<style>
    body{ font-family: DejaVu Sans, sans-serif; font-size: 11px; color:#222; }
    h1{ font-size:18px; margin:0 0 2px; } h2{ font-size:13px; border-bottom:1px solid #ccc; padding-bottom:3px; margin:16px 0 6px; }
    table{ width:100%; border-collapse:collapse; margin-top:4px; }
    th,td{ text-align:left; padding:4px 6px; border-bottom:1px solid #eee; }
    .muted{ color:#888; } .right{ text-align:right; } .neg{ color:#c0392b; } .pos{ color:#27ae60; }
</style>
</head>
<body>
    <h1>Estimasi Budget Proyek</h1>
    <div class="muted">Farka Studio — *angka hanya asumsi dan bukan final.</div>

    <h2>Informasi Umum</h2>
    <table>
        <tr><td>Nama proyek</td><td>{{ $input['nama_proyek'] ?: '-' }}</td></tr>
        <tr><td>Luas tanah</td><td>{{ ar($input['luas_tanah']) }}</td></tr>
        <tr><td>Lokasi</td><td>{{ $input['lokasi_proyek'] ?: '-' }}</td></tr>
        <tr><td>Bobot</td><td>×{{ number_format($result['weighting']['bobot'],2) }}</td></tr>
        <tr><td>Harga per m² (berbobot)</td><td>{{ rp($result['weighting']['harga_per_m2_bobot']) }}</td></tr>
    </table>

    <h2>Design-to-Budget</h2>
    <table>
        <tr><td>Gross budget</td><td class="right">{{ rp($result['budget']['gross']) }}</td></tr>
        <tr><td>Dana darurat</td><td class="right">{{ rp($result['budget']['dana_darurat']) }}</td></tr>
        <tr><td>Nett construction budget</td><td class="right">{{ rp($result['budget']['nett_construction']) }}</td></tr>
        <tr><td>Luas terjangkau</td><td class="right">{{ ar($result['budget']['area']) }}</td></tr>
    </table>

    <h2>Design-to-Regulation ({{ $result['regulation']['code'] }})</h2>
    <table>
        <tr><td>KDB</td><td class="right">{{ ar($result['regulation']['kdb']) }}</td></tr>
        <tr><td>KLB / Luas terbangun</td><td class="right">{{ ar($result['regulation']['luas_terbangun']) }}</td></tr>
        <tr><td>RTH</td><td class="right">{{ ar($result['regulation']['rth']) }}</td></tr>
        <tr><td>Biaya (regulasi)</td><td class="right">{{ rp($result['regulation']['cost']) }}</td></tr>
    </table>

    <h2>Design-to-Needs</h2>
    <table>
        <thead><tr><th>Ruangan</th><th>Prioritas</th><th class="right">Jml</th><th>Tipe</th><th class="right">Total</th></tr></thead>
        <tbody>
        @foreach($result['needs']['rows'] as $row)
            <tr><td>{{ $row['name'] }}</td><td>{{ ucfirst($row['prioritas']) }}</td><td class="right">{{ $row['jumlah'] }}</td><td>{{ $row['tier'] }}</td><td class="right">{{ ar($row['total']) }}</td></tr>
        @endforeach
        </tbody>
    </table>
    <p class="right">Total {{ ar($result['needs']['rooms_total']) }} + sirkulasi {{ ar($result['needs']['sirkulasi']) }} = <b>{{ ar($result['needs']['grand_total']) }}</b></p>

    <h2>Summary</h2>
    <table>
        <thead><tr><th>Skenario</th><th class="right">Luas</th><th class="right">Biaya</th><th class="right">Selisih</th></tr></thead>
        <tbody>
        @foreach($result['summary']['rows'] as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td class="right">{{ ar($row['area']) }}</td>
                <td class="right">{{ rp($row['cost']) }}</td>
                <td class="right {{ $row['selisih']!==null && $row['selisih']<0 ? 'neg':'pos' }}">{{ $row['selisih']===null ? '-' : rp($row['selisih']) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
```

- [ ] **Step 3: Add the failing PDF test**

Append to `tests/Feature/BudgetCalculatorPublicTest.php`:
```php
    public function test_pdf_endpoint_returns_pdf_download(): void
    {
        $opt = fn (string $g, string $l) => \App\Models\Calc\FactorOption::where('label', $l)
            ->whereHas('group', fn ($q) => $q->where('key', $g))->value('id');

        $res = $this->post(route('kalkulator.pdf'), [
            'nama_proyek' => 'Rizal',
            'luas_tanah' => 300,
            'factor_option_ids' => [
                $opt('jabodetabek', 'Ya'), $opt('existing_building', 'Tidak'),
                $opt('target_building', 'Bangun baru'), $opt('style', 'Mediterranean'),
            ],
            'building_type_id' => \App\Models\Calc\BuildingType::where('key', 'standar')->value('id'),
            'zonasi_id' => \App\Models\Calc\Zonasi::where('code', 'R-3')->value('id'),
            'budget' => 2_000_000_000,
            'toleransi' => 0,
            'dana_darurat_pct' => 0.10,
            'sirkulasi_pct' => 0.20,
            'allocation_ids' => \App\Models\Calc\Allocation::where('is_default', true)->where('is_base', false)->pluck('id')->all(),
            'rooms' => [],
        ]);

        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('content-type'));
    }
```

- [ ] **Step 4: Run the PDF test**

Run: `php artisan test --filter "BudgetCalculatorPublicTest::test_pdf_endpoint_returns_pdf_download"`
Expected: PASS (returns `application/pdf`).

- [ ] **Step 5: Commit**

```bash
git add resources/views/kalkulator/pdf.blade.php app/Http/Controllers/BudgetCalculatorController.php tests/Feature/BudgetCalculatorPublicTest.php
git commit -m "feat: budget calculator PDF export via dompdf"
```

---

## Phase 4 — Admin CRUD for reference data

### Task 9: Permissions, sidebar, and admin route group

**Files:**
- Modify: `database/seeders/PermissionSeeder.php`, `database/seeders/DatabaseSeeder.php`
- Modify: `resources/views/admin/layouts/partials/sidebar.blade.php`
- Modify: `routes/web.php`

**Interfaces:**
- Produces: permissions `view_calculator`, `create_calculator`, `edit_calculator`, `delete_calculator`; admin routes named `admin.calc.*`.

- [ ] **Step 1: Add permissions to PermissionSeeder**

In `database/seeders/PermissionSeeder.php`, append to the `$permissions` array:
```php
            'view_calculator', 'create_calculator', 'edit_calculator', 'delete_calculator',
```

- [ ] **Step 2: Grant to the editor role**

In `database/seeders/DatabaseSeeder.php`, add the four permissions to the `$editorRole->syncPermissions([...])` list:
```php
            'view_calculator', 'create_calculator', 'edit_calculator', 'delete_calculator',
```
(super_admin already syncs all permissions.)

- [ ] **Step 3: Add the admin route group**

In `routes/web.php`, inside the `Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(...)`, add a nested calc group (before the `role:super_admin` block):
```php
        // Budget Calculator reference data
        Route::name('calc.')->prefix('calculator')->group(function () {
            Route::resource('rooms', \App\Http\Controllers\Admin\Calc\RoomController::class)->except('show');
            Route::resource('zonasi', \App\Http\Controllers\Admin\Calc\ZonasiController::class)->except('show');
            Route::resource('building-types', \App\Http\Controllers\Admin\Calc\BuildingTypeController::class)->except('show');
            Route::resource('factor-groups', \App\Http\Controllers\Admin\Calc\FactorGroupController::class)->except('show');
            Route::resource('allocations', \App\Http\Controllers\Admin\Calc\AllocationController::class)->except('show');
            Route::resource('components', \App\Http\Controllers\Admin\Calc\ComponentController::class)->except('show');
            Route::resource('size-tiers', \App\Http\Controllers\Admin\Calc\SizeTierController::class)->except('show');
            Route::get('settings', [\App\Http\Controllers\Admin\Calc\SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [\App\Http\Controllers\Admin\Calc\SettingController::class, 'update'])->name('settings.update');
        });
```
Route names become `admin.calc.rooms.index`, etc.

- [ ] **Step 4: Add the sidebar section**

In `resources/views/admin/layouts/partials/sidebar.blade.php`, before the `@role('super_admin')` System block, add:
```blade
        @can('view_calculator')
        <li class="pc-item pc-caption">
          <label>Budget Calculator</label>
          <i class="ph-duotone ph-calculator"></i>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.rooms.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.rooms.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-door"></i></span><span class="pc-mtext">Rooms &amp; Areas</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.zonasi.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.zonasi.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-map-trifold"></i></span><span class="pc-mtext">Zonasi</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.building-types.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.building-types.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-buildings"></i></span><span class="pc-mtext">Building Types &amp; Price</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.factor-groups.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.factor-groups.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-sliders"></i></span><span class="pc-mtext">Weighting Factors</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.allocations.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.allocations.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-chart-pie-slice"></i></span><span class="pc-mtext">Allocations</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.components.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.components.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-squares-four"></i></span><span class="pc-mtext">Quality Components</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.size-tiers.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.size-tiers.index') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-ruler"></i></span><span class="pc-mtext">Size Tiers</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('admin.calc.settings.*') ? 'active' : '' }}">
          <a href="{{ route('admin.calc.settings.edit') }}" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-gear-six"></i></span><span class="pc-mtext">Calculator Settings</span></a>
        </li>
        <li class="pc-item {{ request()->routeIs('kalkulator.show') ? 'active' : '' }}">
          <a href="{{ route('kalkulator.show') }}" target="_blank" class="pc-link"><span class="pc-micon"><i class="ph-duotone ph-calculator"></i></span><span class="pc-mtext">Open Calculator ↗</span></a>
        </li>
        @endcan
```

- [ ] **Step 5: Commit (routes will 500 until controllers exist — that's fine, next tasks add them)**

```bash
git add database/seeders/PermissionSeeder.php database/seeders/DatabaseSeeder.php resources/views/admin/layouts/partials/sidebar.blade.php routes/web.php
git commit -m "feat: calculator admin permissions, sidebar, and route group"
```

---

### Task 10: Rooms CRUD (reference implementation, with nested tier areas)

**Files:**
- Create: `app/Http/Controllers/Admin/Calc/RoomController.php`
- Create: `resources/views/admin/calc/rooms/index.blade.php`, `resources/views/admin/calc/rooms/form.blade.php`
- Test: `tests/Feature/CalculatorAdminTest.php` (add CRUD tests)

**Interfaces:**
- Consumes: `App\Models\Calc\Room`, `RoomArea`, `SizeTier`; permissions from Task 9.

- [ ] **Step 1: Write the controller**

```php
<?php

namespace App\Http\Controllers\Admin\Calc;

use App\Http\Controllers\Controller;
use App\Models\Calc\Room;
use App\Models\Calc\SizeTier;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_calculator')->only(['index']);
        $this->middleware('permission:create_calculator')->only(['create', 'store']);
        $this->middleware('permission:edit_calculator')->only(['edit', 'update']);
        $this->middleware('permission:delete_calculator')->only(['destroy']);
    }

    public function index()
    {
        $rooms = Room::withCount('areas')->orderBy('category')->orderBy('order')->get();
        return view('admin.calc.rooms.index', compact('rooms'));
    }

    public function create()
    {
        $sizeTiers = SizeTier::orderBy('order')->get();
        return view('admin.calc.rooms.form', compact('sizeTiers'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $room = Room::create([
            'category' => $data['category'],
            'name' => $data['name'],
            'order' => (Room::max('order') ?? 0) + 1,
        ]);
        $this->syncAreas($room, $data['areas']);

        return redirect()->route('admin.calc.rooms.index')->with('success', 'Room created successfully.');
    }

    public function edit(string $id)
    {
        $room = Room::with('areas')->findOrFail($id);
        $sizeTiers = SizeTier::orderBy('order')->get();
        $areasByTier = $room->areas->keyBy('size_tier_id');
        return view('admin.calc.rooms.form', compact('room', 'sizeTiers', 'areasByTier'));
    }

    public function update(Request $request, string $id)
    {
        $room = Room::findOrFail($id);
        $data = $this->validateData($request);
        $room->update(['category' => $data['category'], 'name' => $data['name']]);
        $this->syncAreas($room, $data['areas']);

        return redirect()->route('admin.calc.rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy(string $id)
    {
        Room::findOrFail($id)->delete(); // room_areas cascade
        return redirect()->route('admin.calc.rooms.index')->with('success', 'Room deleted successfully.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'category' => 'required|in:service,public,private,luxury',
            'name' => 'required|string|max:255',
            'areas' => 'required|array',
            'areas.*.size_tier_id' => 'required|integer|exists:calc_size_tiers,id',
            'areas.*.panjang' => 'required|numeric|min:0',
            'areas.*.lebar' => 'required|numeric|min:0',
        ]);
    }

    /** area = panjang * lebar (full precision). */
    private function syncAreas(Room $room, array $areas): void
    {
        foreach ($areas as $a) {
            $room->areas()->updateOrCreate(
                ['size_tier_id' => $a['size_tier_id']],
                ['panjang' => $a['panjang'], 'lebar' => $a['lebar'], 'area' => round($a['panjang'] * $a['lebar'], 2)]
            );
        }
    }
}
```

- [ ] **Step 2: Write the index view**

Model it on `resources/views/admin/categories/index.blade.php` (DataTables + delete). Columns: Category, Name, Area tiers count, Actions. Full file:
```blade
@extends('admin.layouts.admin')
@section('title', 'Rooms')
@section('page_title', 'Rooms & Areas')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Rooms &amp; Areas</li>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">@endpush
@section('content')
<div class="row"><div class="col-xl-12"><div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>All Rooms</h5>
        @can('create_calculator')<a href="{{ route('admin.calc.rooms.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add Room</a>@endcan
    </div>
    <div class="card-body">
        <div class="mb-3"><input type="search" id="table-search" class="form-control form-control-sm" placeholder="Search rooms…"></div>
        <div class="table-responsive">
            <table id="rooms-table" class="table table-striped table-bordered nowrap">
                <thead><tr><th>Category</th><th>Name</th><th>Tiers</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse($rooms as $room)
                    <tr>
                        <td><span class="badge bg-light-secondary text-capitalize">{{ $room->category }}</span></td>
                        <td>{{ $room->name }}</td>
                        <td><span class="badge bg-light-primary rounded-pill">{{ $room->areas_count }}</span></td>
                        <td class="text-end">
                            @can('edit_calculator')<a href="{{ route('admin.calc.rooms.edit', $room->id) }}" class="btn btn-icon btn-link-success"><i class="ti ti-edit"></i></a>@endcan
                            @can('delete_calculator')
                            <form action="{{ route('admin.calc.rooms.destroy', $room->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-icon btn-link-danger delete-btn" data-name="{{ $room->name }}"><i class="ti ti-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No rooms found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div></div></div>
@endsection
@push('scripts')
<script src="{{ asset('admin_assets/js/plugins/dataTables.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/plugins/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(function(){ var t=$('#rooms-table').DataTable({paging:true,pageLength:25,dom:'rtip'}); $('#table-search').on('input',function(){t.search(this.value).draw();}); });
</script>
@endpush
```

- [ ] **Step 3: Write the form view (room + 6 tier P×L rows)**

```blade
@extends('admin.layouts.admin')
@section('title', isset($room) ? 'Edit Room' : 'Create Room')
@section('page_title', isset($room) ? 'Edit Room' : 'Create Room')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.calc.rooms.index') }}">Rooms</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ isset($room) ? 'Edit' : 'Create' }}</li>
@endsection
@section('content')
<div class="row"><div class="col-lg-8 mx-auto"><div class="card"><div class="card-body">
    <form action="{{ isset($room) ? route('admin.calc.rooms.update', $room->id) : route('admin.calc.rooms.store') }}" method="POST">
        @csrf @if(isset($room))@method('PUT')@endif
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="category" class="form-select" required>
                    @foreach(['service','public','private','luxury'] as $c)
                        <option value="{{ $c }}" @selected(old('category', $room->category ?? '')===$c)>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input name="name" class="form-control" value="{{ old('name', $room->name ?? '') }}" required>
            </div>
        </div>

        <h6 class="mt-4">Area per Size Tier (Panjang × Lebar)</h6>
        <table class="table table-sm align-middle">
            <thead><tr><th>Tier</th><th>Panjang (m)</th><th>Lebar (m)</th></tr></thead>
            <tbody>
            @foreach($sizeTiers as $i => $tier)
                @php $existing = isset($areasByTier) ? ($areasByTier[$tier->id] ?? null) : null; @endphp
                <tr>
                    <td>{{ $tier->name }}<input type="hidden" name="areas[{{ $i }}][size_tier_id]" value="{{ $tier->id }}"></td>
                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="areas[{{ $i }}][panjang]" value="{{ old("areas.$i.panjang", $existing->panjang ?? 0) }}" required></td>
                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="areas[{{ $i }}][lebar]" value="{{ old("areas.$i.lebar", $existing->lebar ?? 0) }}" required></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <p class="text-muted small">Area is computed automatically as Panjang × Lebar.</p>

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('admin.calc.rooms.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary">{{ isset($room) ? 'Update Room' : 'Save Room' }}</button>
        </div>
    </form>
</div></div></div></div>
@endsection
```

- [ ] **Step 4: Write CRUD tests**

Append to `tests/Feature/CalculatorAdminTest.php`:
```php
    private function superAdmin(): \App\Models\User
    {
        return \App\Models\User::where('email', 'admin@farkastudio.test')->first();
    }

    public function test_admin_can_create_and_delete_room(): void
    {
        $admin = $this->superAdmin();
        $tiers = SizeTier::orderBy('order')->get();
        $areas = $tiers->values()->map(fn ($t, $i) => ['size_tier_id' => $t->id, 'panjang' => 2, 'lebar' => 3])->all();

        $this->actingAs($admin)->post(route('admin.calc.rooms.store'), [
            'category' => 'service', 'name' => 'Test Room', 'areas' => $areas,
        ])->assertRedirect(route('admin.calc.rooms.index'));

        $room = Room::where('name', 'Test Room')->first();
        $this->assertNotNull($room);
        $this->assertSame(6, $room->areas()->count());
        $this->assertEqualsWithDelta(6.0, $room->areas()->first()->area, 0.001); // 2*3

        $this->actingAs($admin)->delete(route('admin.calc.rooms.destroy', $room->id))
            ->assertRedirect(route('admin.calc.rooms.index'));
        $this->assertNull(Room::find($room->id));
    }

    public function test_room_index_renders(): void
    {
        $this->actingAs($this->superAdmin())->get(route('admin.calc.rooms.index'))->assertOk();
    }
```

- [ ] **Step 5: Run the tests**

Run: `php artisan test --filter CalculatorAdminTest`
Expected: PASS (seed counts + room create/delete + index render).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/Calc/RoomController.php resources/views/admin/calc/rooms tests/Feature/CalculatorAdminTest.php
git commit -m "feat: admin CRUD for calculator rooms and tier areas"
```

---

### Task 11: Zonasi, Building Types, Components, Size Tiers CRUD

These four are flat single-table resources. Build each **exactly like `CategoryController` + `categories/{index,form}.blade.php`**, substituting the fields below. Each controller has the same permission-middleware constructor as `RoomController` (Task 10, Step 1) with `permission:*_calculator`.

**Files (create per resource):**
- `app/Http/Controllers/Admin/Calc/{Zonasi,BuildingType,Component,SizeTier}Controller.php`
- `resources/views/admin/calc/{zonasi,building-types,components,size-tiers}/{index,form}.blade.php`

**Field/validation specs:**

| Resource | Model | List columns | Form fields (name → validation) |
|---|---|---|---|
| Zonasi | `Calc\Zonasi` | Code, Name, KDB%, KLB, RTH% | `code` req·string·unique · `name` nullable·string · `kdb`,`ktb`,`rth` req·numeric·0–1 (enter as %, ÷100 on save) · `klb` req·numeric·min0 |
| Building Types | `Calc\BuildingType` | Name, Key, Price/m² | `key` req·string·unique · `name` req·string · `price_per_m2` req·integer·min0 |
| Components | `Calc\Component` | Name, Standar, Optimal, Premium | `name` req·string · `standar`,`optimal`,`premium` req·string·max500 |
| Size Tiers | `Calc\SizeTier` | Name, Key, Description | `key` req·string·unique · `name` req·string · `description` nullable·string·max500 |

- [ ] **Step 1: Zonasi controller + views**

Controller `index`: `Zonasi::orderBy('order')->get()`. `store`/`update` — accept `kdb`,`ktb`,`rth` as percentages from the form and divide by 100 before saving (store as fraction); `klb` saved as-is. Example store body:
```php
    public function store(Request $request)
    {
        $data = $this->validated($request);
        Zonasi::create($this->normalize($data) + ['order' => (Zonasi::max('order') ?? 0) + 1]);
        return redirect()->route('admin.calc.zonasi.index')->with('success', 'Zonasi created successfully.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => 'required|string|max:50|unique:calc_zonasi,code' . ($id ? ",$id" : ''),
            'name' => 'nullable|string|max:255',
            'kdb' => 'required|numeric|min:0|max:100',
            'klb' => 'required|numeric|min:0',
            'ktb' => 'required|numeric|min:0|max:100',
            'rth' => 'required|numeric|min:0|max:100',
        ]);
    }

    private function normalize(array $data): array
    {
        $data['kdb'] /= 100; $data['ktb'] /= 100; $data['rth'] /= 100;
        return $data;
    }
```
Form view: inputs for code, name, and kdb/ktb/rth shown as percentages (`value="{{ old('kdb', isset($zonasi) ? $zonasi->kdb*100 : '') }}"`), klb as ratio. Index view mirrors categories index (drop the reorder/bulk JS; keep the search + delete pattern from Task 10 Step 2).

- [ ] **Step 2: Building Types controller + views** — plain fields, no normalization. Slug/key input free text; store `order = max+1`.

- [ ] **Step 3: Components controller + views** — plain text fields.

- [ ] **Step 4: Size Tiers controller + views** — plain fields.

- [ ] **Step 5: Add render + one write test each**

Append to `tests/Feature/CalculatorAdminTest.php`:
```php
    public function test_flat_calc_admin_pages_render(): void
    {
        $admin = $this->superAdmin();
        foreach ([
            route('admin.calc.zonasi.index'), route('admin.calc.zonasi.create'),
            route('admin.calc.building-types.index'), route('admin.calc.building-types.create'),
            route('admin.calc.components.index'), route('admin.calc.components.create'),
            route('admin.calc.size-tiers.index'), route('admin.calc.size-tiers.create'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_zonasi_percentages_are_stored_as_fractions(): void
    {
        $this->actingAs($this->superAdmin())->post(route('admin.calc.zonasi.store'), [
            'code' => 'R-9', 'name' => 'Test', 'kdb' => 50, 'klb' => 2, 'ktb' => 50, 'rth' => 30,
        ])->assertRedirect(route('admin.calc.zonasi.index'));
        $z = Zonasi::where('code', 'R-9')->first();
        $this->assertEqualsWithDelta(0.50, $z->kdb, 0.001);
        $this->assertEqualsWithDelta(2.0, $z->klb, 0.001);
    }
```

- [ ] **Step 6: Run and commit**

Run: `php artisan test --filter CalculatorAdminTest`
Expected: PASS.
```bash
git add app/Http/Controllers/Admin/Calc/ZonasiController.php app/Http/Controllers/Admin/Calc/BuildingTypeController.php app/Http/Controllers/Admin/Calc/ComponentController.php app/Http/Controllers/Admin/Calc/SizeTierController.php resources/views/admin/calc/zonasi resources/views/admin/calc/building-types resources/views/admin/calc/components resources/views/admin/calc/size-tiers tests/Feature/CalculatorAdminTest.php
git commit -m "feat: admin CRUD for zonasi, building types, components, size tiers"
```

---

### Task 12: Allocations CRUD

**Files:**
- Create: `app/Http/Controllers/Admin/Calc/AllocationController.php`
- Create: `resources/views/admin/calc/allocations/{index,form}.blade.php`
- Test: add to `tests/Feature/CalculatorAdminTest.php`

**Interfaces:** `Calc\Allocation`. Percentage entered as % in the form, stored as fraction.

- [ ] **Step 1: Controller** — same shape as Task 11 Zonasi. Validation:
```php
        return $request->validate([
            'category' => 'required|in:pelaksanaan,perancangan,persiapan',
            'label' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0',       // entered as %, ÷100 on save
            'is_base' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'note' => 'nullable|string|max:255',
        ]);
```
On save: `$data['percentage'] /= 100;` and cast `is_base`/`is_default` to `(bool) ($request->has(...))`. `order = max+1` on create.

- [ ] **Step 2: Views** — index columns: Category, Label, Percentage (show `percentage*100 . '%'`), Base?, Default?. Form: category select, label, percentage (as %), is_base checkbox, is_default checkbox, note.

- [ ] **Step 3: Test**
```php
    public function test_allocation_percentage_stored_as_fraction(): void
    {
        $this->actingAs($this->superAdmin())->post(route('admin.calc.allocations.store'), [
            'category' => 'persiapan', 'label' => 'Test fee', 'percentage' => 2.5,
        ])->assertRedirect(route('admin.calc.allocations.index'));
        $this->assertEqualsWithDelta(0.025, \App\Models\Calc\Allocation::where('label','Test fee')->value('percentage'), 0.0001);
    }
```

- [ ] **Step 4: Run and commit**

Run: `php artisan test --filter CalculatorAdminTest`
Expected: PASS.
```bash
git add app/Http/Controllers/Admin/Calc/AllocationController.php resources/views/admin/calc/allocations tests/Feature/CalculatorAdminTest.php
git commit -m "feat: admin CRUD for calculator allocations"
```

---

### Task 13: Factor Groups + Options CRUD

**Files:**
- Create: `app/Http/Controllers/Admin/Calc/FactorGroupController.php`
- Create: `resources/views/admin/calc/factor-groups/{index,form}.blade.php`
- Test: add to `tests/Feature/CalculatorAdminTest.php`

**Interfaces:** `Calc\FactorGroup` (`hasMany options`). Options edited inline on the group form (like Rooms→areas in Task 10).

- [ ] **Step 1: Controller** — `index`: `FactorGroup::withCount('options')->orderBy('order')->get()`. `create`/`edit` render the form; `store`/`update` save the group and sync its options from a repeatable `options[]` array. Validation:
```php
        return $request->validate([
            'key' => 'required|string|max:100|unique:calc_factor_groups,key' . ($id ? ",$id" : ''),
            'name' => 'required|string|max:255',
            'options' => 'required|array|min:1',
            'options.*.label' => 'required|string|max:255',
            'options.*.multiplier' => 'required|numeric|min:0',
            'options.*.note' => 'nullable|string|max:255',
            'options.*.is_default' => 'nullable|boolean',
        ]);
```
Sync options: delete removed rows then `updateOrCreate` by `id` if present, else create; set `order` by array index. For simplicity on update: `$group->options()->delete();` then recreate from the submitted array (options carry no external FKs). Mark `is_default` from a single radio (`default_index`) so exactly one option per group is default.

- [ ] **Step 2: Views** — form has group key + name, then a JS-repeatable list of option rows (label, multiplier, note, "default" radio). Index columns: Name, Key, Options count. (Reuse the room index table shell.)

- [ ] **Step 3: Test**
```php
    public function test_factor_group_saves_options(): void
    {
        $this->actingAs($this->superAdmin())->post(route('admin.calc.factor-groups.store'), [
            'key' => 'test_group', 'name' => 'Test Group',
            'options' => [
                ['label' => 'A', 'multiplier' => 1.0, 'is_default' => 1],
                ['label' => 'B', 'multiplier' => 1.2],
            ],
        ])->assertRedirect(route('admin.calc.factor-groups.index'));
        $g = \App\Models\Calc\FactorGroup::where('key','test_group')->first();
        $this->assertSame(2, $g->options()->count());
    }
```

- [ ] **Step 4: Run and commit**

Run: `php artisan test --filter CalculatorAdminTest`
Expected: PASS.
```bash
git add app/Http/Controllers/Admin/Calc/FactorGroupController.php resources/views/admin/calc/factor-groups tests/Feature/CalculatorAdminTest.php
git commit -m "feat: admin CRUD for weighting factor groups and options"
```

---

### Task 14: Settings edit page

**Files:**
- Create: `app/Http/Controllers/Admin/Calc/SettingController.php`
- Create: `resources/views/admin/calc/settings/edit.blade.php`
- Test: add to `tests/Feature/CalculatorAdminTest.php`

**Interfaces:** `Calc\Setting` (key/value). Follows the single-row `ContactSettingController` pattern (`edit`/`update` only).

- [ ] **Step 1: Controller**
```php
<?php

namespace App\Http\Controllers\Admin\Calc;

use App\Http\Controllers\Controller;
use App\Models\Calc\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_calculator')->only('edit');
        $this->middleware('permission:edit_calculator')->only('update');
    }

    public function edit()
    {
        $settings = Setting::orderBy('key')->get()->keyBy('key');
        return view('admin.calc.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        // dana_darurat_pct & sirkulasi_pct entered as %, stored as fraction; toleransi_default as rupiah.
        $data = $request->validate([
            'dana_darurat_pct' => 'required|numeric|min:0|max:100',
            'sirkulasi_pct' => 'required|numeric|min:0|max:100',
            'toleransi_default' => 'required|numeric|min:0',
        ]);
        Setting::updateOrCreate(['key' => 'dana_darurat_pct'], ['value' => $data['dana_darurat_pct'] / 100]);
        Setting::updateOrCreate(['key' => 'sirkulasi_pct'], ['value' => $data['sirkulasi_pct'] / 100]);
        Setting::updateOrCreate(['key' => 'toleransi_default'], ['value' => $data['toleransi_default']]);

        return redirect()->route('admin.calc.settings.edit')->with('success', 'Settings updated successfully.');
    }
}
```

- [ ] **Step 2: View** — a simple card form with three inputs (dana darurat %, sirkulasi %, toleransi default Rp), values from `$settings['key']->value` (percent ones ×100 for display). Follows `resources/views/admin/contact-settings/form.blade.php` shell.

- [ ] **Step 3: Test**
```php
    public function test_settings_update_stores_fractions(): void
    {
        $this->actingAs($this->superAdmin())->put(route('admin.calc.settings.update'), [
            'dana_darurat_pct' => 12, 'sirkulasi_pct' => 25, 'toleransi_default' => 1000000,
        ])->assertRedirect(route('admin.calc.settings.edit'));
        $this->assertEqualsWithDelta(0.12, (float) \App\Models\Calc\Setting::value('dana_darurat_pct'), 0.0001);
        $this->assertEqualsWithDelta(0.25, (float) \App\Models\Calc\Setting::value('sirkulasi_pct'), 0.0001);
    }
```

- [ ] **Step 4: Run and commit**

Run: `php artisan test --filter CalculatorAdminTest`
Expected: PASS.
```bash
git add app/Http/Controllers/Admin/Calc/SettingController.php resources/views/admin/calc/settings tests/Feature/CalculatorAdminTest.php
git commit -m "feat: admin edit page for calculator global settings"
```

---

### Task 15: Full-suite green + permission gate check

**Files:**
- Test: `tests/Feature/CalculatorAdminTest.php` (add permission-denial test)

- [ ] **Step 1: Add a permission-denial test**
```php
    public function test_viewer_without_calculator_permission_is_forbidden(): void
    {
        $user = \App\Models\User::factory()->create(); // no roles/permissions
        $this->actingAs($user)->get(route('admin.calc.rooms.index'))->assertForbidden();
    }
```

- [ ] **Step 2: Run the whole suite**

Run: `php artisan test`
Expected: all green, including the pre-existing `AdminPanelTest` (which compiles the sidebar — now containing the new calc section).

- [ ] **Step 3: Final commit**

```bash
git add tests/Feature/CalculatorAdminTest.php
git commit -m "test: calculator admin permission gating"
```

---

## Self-Review (author checklist — completed)

**1. Spec coverage:**
- Public page + admin access → Tasks 6–9 (sidebar link opens `kalkulator.show`). ✓
- Live AJAX, server-authoritative calc → Tasks 5–7. ✓
- PDF via dompdf, no DB persistence of submissions → Tasks 1, 8. ✓
- All 4 sections faithful → Task 5 service + Task 5 test asserts every sheet number. ✓
- 10 reference tables + seeder from verified JSON → Tasks 2–4. ✓
- Full admin CRUD (rooms+areas, zonasi, prices, factors, allocations, components, size tiers, settings) → Tasks 10–14. ✓
- Permissions `*_calculator` + editor/super_admin grants + sidebar gating → Task 9, tested Task 15. ✓
- Precision rules (area = P×L, money → int rupiah) → Global Constraints, enforced in seeder (Task 4), service (Task 5), room CRUD (Task 10), and asserted by tests. ✓

**2. Placeholder scan:** No "TBD/TODO"; every code step shows real code. Tasks 11–13 give complete controller/validation specs + explicit reference to the existing `CategoryController`/`categories` views to reproduce (an existing in-repo pattern, not a placeholder). ✓

**3. Type consistency:** Service input/output keys in Task 5 match their consumers — `weighting.harga_per_m2_bobot`, `budget.nett_construction`, `regulation.cost`, `summary.rows[].{area,cost,selisih}` used identically in Tasks 6 (test), 7 (JS `render`), 8 (PDF blade). Model class names (`App\Models\Calc\*`) consistent across seeder, service, controllers. Route names `admin.calc.*` consistent between Task 9 (definition) and Tasks 10–14 (usage). ✓

## Notes for the implementer

- The verified reference data already exists at `database/data/budget_calculator_seed.json` — do not regenerate it.
- `barryvdh/laravel-dompdf` must be installed (Task 1) before Task 8 runs.
- The pre-existing `tests/Feature/AdminPanelTest.php` renders the sidebar; after Task 9 it exercises the new calc menu, so keep that suite green.
- Keep money rounding (`round()` → int) and `area = panjang*lebar` exactly as specified — the unit test in Task 5 will fail otherwise (455.7 vs 455.8).
