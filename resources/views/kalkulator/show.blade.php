<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Budget Estimator | Farka Studio</title>
    <meta name="description" content="Estimasi biaya pembangunan secara instan bersama Farka Studio — hitung kebutuhan budget berdasarkan luas tanah, zonasi, kualitas bangunan, dan program ruang.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('farkalogo.svg') }}" type="image/x-icon">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Farka Studio">
    <meta property="og:title" content="Budget Estimator | Farka Studio">
    <meta property="og:description" content="Estimasi biaya pembangunan secara instan berdasarkan luas tanah, zonasi, kualitas bangunan, dan program ruang.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('farkalogo.svg') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Budget Estimator | Farka Studio">
    <meta name="twitter:description" content="Estimasi biaya pembangunan secara instan berdasarkan luas tanah, zonasi, kualitas bangunan, dan program ruang.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Noto+Sans+JP:wght@400;500;700&family=Source+Sans+3:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ============ Reset & tokens ============ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --ink: #111111;
            --ink-70: rgba(17,17,17,.70);
            --ink-55: rgba(17,17,17,.55);
            --ink-45: rgba(17,17,17,.45);
            --ink-40: rgba(17,17,17,.40);
            --line: rgba(17,17,17,.12);
            --line-strong: rgba(17,17,17,.28);
            --wash: rgba(17,17,17,.03);
            --pos: #15803d;
            --neg: #dc2626;
            --radius: 16px;
            --font-title: 'Montserrat', system-ui, -apple-system, Segoe UI, sans-serif;
            --font-head: 'Source Sans 3', system-ui, -apple-system, Segoe UI, sans-serif;
            --font-body: 'Noto Sans JP', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
        }
        html { -webkit-text-size-adjust: 100%; }
        body {
            font-family: var(--font-body);
            color: var(--ink);
            background: #fff;
            font-size: 16px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3 { font-family: var(--font-title); font-weight: 800; line-height: 1.2; }
        a { color: inherit; }

        /* ============ Layout ============ */
        .wrap { max-width: 1240px; margin: 0 auto; padding: 32px 20px 64px; }
        .topbar {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 11px; letter-spacing: .2em; text-transform: uppercase;
            color: var(--ink-40); text-decoration: none; transition: color .15s;
        }
        .topbar:hover { color: var(--ink); }
        .page-head { margin: 20px 0 28px; }
        .page-title { font-size: clamp(1.7rem, 4.5vw, 2.4rem); font-weight: 900; letter-spacing: -.02em; }
        .page-sub { margin-top: 8px; font-size: clamp(1rem, 2.4vw, 1.15rem); color: var(--ink-55); max-width: 660px; }
        .page-sub .note { color: var(--ink-40); }

        .layout { display: grid; grid-template-columns: 1fr; gap: 22px; align-items: start; }
        @media (min-width: 1024px) {
            .layout { grid-template-columns: minmax(0, 1.3fr) minmax(400px, 1fr); gap: 28px; }
        }
        .form-col { display: grid; gap: 20px; }

        /* ============ Card / section ============ */
        .card { border: 1px solid var(--line); border-radius: var(--radius); padding: 22px; }
        @media (min-width: 640px) { .card { padding: 26px 28px; } }
        .step { font-size: 11px; letter-spacing: .2em; text-transform: uppercase; color: var(--ink-40); }
        .card-title { font-size: clamp(1.2rem, 3vw, 1.5rem); margin: 4px 0 18px; }
        .card-title.tight { margin-bottom: 4px; }
        .card-sub { font-size: .95rem; color: var(--ink-55); margin-bottom: 18px; }

        /* ============ Fields ============ */
        .grid2 { display: grid; grid-template-columns: 1fr; gap: 16px; }
        @media (min-width: 640px) { .grid2 { grid-template-columns: 1fr 1fr; gap: 18px; } }
        .col-2 { grid-column: 1 / -1; }
        .field { display: block; }
        .field-label { display: block; font-size: .9rem; font-weight: 600; color: var(--ink-70); margin-bottom: 7px; }
        .control {
            display: block; width: 100%;
            font-family: inherit; font-size: 1rem; color: var(--ink);
            background: #fff; border: 1px solid var(--line-strong); border-radius: 12px;
            padding: 12px 15px; outline: none; transition: border-color .15s;
        }
        .control::placeholder { color: var(--ink-40); }
        .control:focus { border-color: var(--ink); }
        select.control {
            -webkit-appearance: none; appearance: none; cursor: pointer; padding-right: 40px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23111' stroke-opacity='0.5' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 14px center;
        }
        input[type=number] { -moz-appearance: textfield; }
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

        /* Currency input group */
        .rp-group { display: flex; align-items: stretch; border: 1px solid var(--line-strong); border-radius: 12px; overflow: hidden; transition: border-color .15s; }
        .rp-group:focus-within { border-color: var(--ink); }
        .rp-prefix { display: grid; place-items: center; padding: 0 14px; color: var(--ink-55); background: var(--wash); border-right: 1px solid var(--line); font-size: .95rem; }
        .rp-input { flex: 1; min-width: 0; border: 0; outline: none; font-family: inherit; font-size: 1rem; padding: 12px 15px; color: var(--ink); background: transparent; }
        .rp-input::placeholder { color: var(--ink-40); }

        /* ============ Allocations ============ */
        .alloc-groups { display: grid; gap: 18px; }
        .alloc-cat { font-size: 11px; letter-spacing: .16em; text-transform: uppercase; color: var(--ink-40); margin-bottom: 8px; }
        .alloc-list { display: grid; grid-template-columns: 1fr; gap: 10px; }
        @media (min-width: 640px) { .alloc-list { grid-template-columns: 1fr 1fr; } }
        .alloc-item {
            display: flex; align-items: center; gap: 12px; cursor: pointer;
            border: 1px solid var(--line); border-radius: 12px; padding: 11px 15px;
            font-size: 15px; transition: border-color .15s;
        }
        .alloc-item:hover { border-color: var(--line-strong); }
        .alloc-item.is-base { background: var(--wash); }
        .alloc-item input[type=checkbox] { width: 17px; height: 17px; accent-color: var(--ink); flex: none; }
        .alloc-item .name { flex: 1; }
        .alloc-item .pct { color: var(--ink-45); font-variant-numeric: tabular-nums; }

        /* ============ Components table ============ */
        .comp-block { margin-top: 24px; }
        .comp-cap { font-size: 11px; letter-spacing: .16em; text-transform: uppercase; color: var(--ink-40); margin-bottom: 8px; }
        .comp-scroll { overflow-x: auto; border: 1px solid var(--line); border-radius: 12px; -webkit-overflow-scrolling: touch; }
        .comp-table { width: 100%; border-collapse: collapse; min-width: 560px; font-size: .9rem; }
        .comp-table th, .comp-table td { text-align: left; padding: 11px 15px; vertical-align: top; }
        .comp-table thead th { background: var(--wash); font-weight: 700; }
        .comp-table tbody tr { border-top: 1px solid var(--line); }
        .comp-table td.name { font-weight: 600; white-space: nowrap; }
        .comp-table td.desc { color: var(--ink-70); }
        .hint { font-size: .78rem; color: var(--ink-40); margin-top: 8px; }

        /* ============ Room builder ============ */
        .rooms-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin: 4px 0 6px; }
        .room-list { display: grid; gap: 10px; }
        .room-row {
            display: grid; grid-template-columns: 1fr; gap: 8px;
            border: 1px solid var(--line); border-radius: 12px; padding: 12px;
        }
        @media (min-width: 640px) {
            .room-row { grid-template-columns: 5fr 3fr 1.4fr 2.4fr 44px; align-items: center; padding: 10px; }
        }
        .room-row select, .room-row input { font-family: inherit; font-size: 15px; border: 1px solid var(--line-strong); border-radius: 10px; padding: 10px 12px; outline: none; background: #fff; width: 100%; }
        .room-row select { -webkit-appearance: none; appearance: none; }
        .room-row select:focus, .room-row input:focus { border-color: var(--ink); }
        .room-del { border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--ink-40); font-size: 20px; line-height: 1; padding: 9px 0; cursor: pointer; transition: .15s; }
        .room-del:hover { color: var(--neg); border-color: rgba(220,38,38,.4); }
        .rooms-empty { font-size: .9rem; color: var(--ink-40); text-align: center; border: 1px dashed var(--line-strong); border-radius: 12px; padding: 22px 16px; }
        .rooms-empty b { color: var(--ink-70); font-weight: 600; }
        .room-area { grid-column: 1 / -1; font-size: 13px; color: var(--ink-55); padding-top: 8px; margin-top: 2px; border-top: 1px dashed var(--line); }
        .room-area b { color: var(--ink); font-weight: 700; }

        /* ============ Regulation live info ============ */
        .reg-info { margin-top: 14px; }
        .reg-table { width: 100%; border-collapse: collapse; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; font-size: .92rem; }
        .reg-table th, .reg-table td { padding: 9px 14px; text-align: left; }
        .reg-table thead th { background: var(--wash); font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: var(--ink-55); }
        .reg-table tbody tr { border-top: 1px solid var(--line); }
        .reg-table td.n { text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; }
        .reg-table tr.total td { background: var(--wash); font-weight: 700; }
        .reg-empty { font-size: .85rem; color: var(--ink-40); }

        /* ============ Size-tier reference ============ */
        .tier-ref { margin: 4px 0 16px; border: 1px solid var(--line); border-radius: 12px; padding: 2px 14px; }
        .tier-ref summary { cursor: pointer; font-size: .9rem; font-weight: 600; color: var(--ink-70); padding: 11px 0; list-style: none; }
        .tier-ref summary::-webkit-details-marker { display: none; }
        .tier-ref summary::before { content: "▸ "; color: var(--ink-40); }
        .tier-ref[open] summary::before { content: "▾ "; }
        .tier-ref[open] summary { border-bottom: 1px solid var(--line); margin-bottom: 6px; }
        .tier-item { padding: 7px 0; font-size: .88rem; border-top: 1px solid rgba(17,17,17,.05); }
        .tier-item:first-of-type { border-top: 0; }
        .tier-item b { color: var(--ink); }
        .tier-item span { color: var(--ink-55); }

        /* ============ Buttons ============ */
        .btn { font-family: inherit; font-size: .95rem; font-weight: 600; border: 0; border-radius: 12px; cursor: pointer; transition: background .15s, opacity .15s; }
        .btn-dark { background: var(--ink); color: #fff; padding: 11px 18px; }
        .btn-dark:hover { background: #000; }
        .btn-add::before { content: "+ "; }

        /* ============ Result panel ============ */
        .aside-col { display: grid; gap: 16px; }
        @media (min-width: 1024px) { .aside-col { position: sticky; top: 24px; } }
        .result { border: 1px solid var(--line); border-radius: var(--radius); padding: 24px; }
        .result-cap { font-size: 11px; letter-spacing: .2em; text-transform: uppercase; color: var(--ink-40); }
        .result-empty { text-align: center; padding: 22px 4px; }
        .result-empty p { margin-top: 12px; color: var(--ink-55); font-size: 15px; }
        .result-empty b { color: var(--ink-70); font-weight: 600; }
        .result h3 { font-size: 1.2rem; margin: 4px 0 16px; }

        .hero { background: var(--ink); color: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .hero-label { font-size: 11px; letter-spacing: .18em; text-transform: uppercase; color: rgba(255,255,255,.55); }
        .hero-value { font-family: var(--font-title); font-size: 2rem; font-weight: 900; margin-top: 4px; font-variant-numeric: tabular-nums; line-height: 1.1; }
        .hero-sub { font-size: .9rem; color: rgba(255,255,255,.6); margin-top: 6px; }

        .metrics { margin-bottom: 22px; }
        .metric { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--line); }
        .metric:last-child { border-bottom: 0; }
        .metric .m-label { font-size: 13px; color: var(--ink-55); }
        .metric .m-value { font-family: var(--font-head); font-weight: 700; font-size: 1rem; text-align: right; font-variant-numeric: tabular-nums; }

        .scen-cap { font-size: 11px; letter-spacing: .2em; text-transform: uppercase; color: var(--ink-40); margin-bottom: 12px; }
        .scen-list { display: grid; gap: 10px; }
        .scen-card { border: 1px solid var(--line); border-radius: 12px; padding: 15px; }
        .scen-title { font-size: 11px; letter-spacing: .12em; text-transform: uppercase; color: var(--ink-40); margin-bottom: 11px; }
        /* Compact 3-column layout; each value sits BELOW its label */
        .scen-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
        .scen-cell { display: block; min-width: 0; }
        .scen-cell .c-label { display: block; font-size: 11px; color: var(--ink-45); margin-bottom: 3px; }
        .scen-cell .c-value { display: block; font-family: var(--font-head); font-weight: 700; font-size: .92rem; font-variant-numeric: tabular-nums; line-height: 1.25; }
        .pos { color: var(--pos); } .neg { color: var(--neg); } .muted { color: var(--ink-40); }

        .pdf-btn { width: 100%; background: var(--ink); color: #fff; border: 0; border-radius: 12px; padding: 15px; font-family: inherit; font-size: 1rem; font-weight: 700; cursor: pointer; transition: background .15s, opacity .15s; }
        .pdf-btn:hover { background: #000; }
        .pdf-btn:disabled { opacity: .4; cursor: not-allowed; }
    </style>
</head>
<body>
<div class="wrap">

    <a href="{{ url('/') }}" class="topbar">&larr; Farka Studio</a>

    <header class="page-head">
        <h1 class="page-title">Budget Estimator</h1>
        <p class="page-sub">
            Estimasi kebutuhan biaya pembangunan berdasarkan preferensi Anda.
            <span class="note">*Angka hanya asumsi dan bukan final.</span>
        </p>
    </header>

    <div class="layout">

        {{-- ================= FORM ================= --}}
        <form id="calc-form" class="form-col" autocomplete="off">

            {{-- 1. General --}}
            <section class="card">
                <div class="step">Langkah 1</div>
                <h2 class="card-title">Informasi Umum</h2>
                <div class="grid2">
                    <label class="field">
                        <span class="field-label">Nama proyek</span>
                        <input name="nama_proyek" class="control" placeholder="mis. Rumah Tinggal Bapak A">
                    </label>
                    <label class="field">
                        <span class="field-label">Luas tanah (m²)</span>
                        <input name="luas_tanah" type="number" step="0.1" min="0" class="control" placeholder="mis. 300">
                    </label>
                    <label class="field col-2">
                        <span class="field-label">Lokasi proyek</span>
                        <input name="lokasi_proyek" class="control" placeholder="Kota / wilayah">
                    </label>
                </div>
            </section>

            {{-- 2. Weighting factors --}}
            <section class="card">
                <div class="step">Langkah 2</div>
                <h2 class="card-title">Faktor Bobot</h2>
                <div class="grid2">
                    @foreach($factorGroups as $group)
                        <label class="field">
                            <span class="field-label">{{ $group->name }}</span>
                            <select name="factor_option_ids[]" class="control factor-select" data-required-factor>
                                <option value="">— Pilih —</option>
                                @foreach($group->options as $opt)
                                    <option value="{{ $opt->id }}" data-note="{{ $opt->note }}">{{ $opt->label }} (×{{ rtrim(rtrim(number_format($opt->multiplier, 2), '0'), '.') }})</option>
                                @endforeach
                            </select>
                            <p class="hint factor-note" style="display:none"></p>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- 3. Allocations --}}
            <section class="card">
                <div class="step">Langkah 3</div>
                <h2 class="card-title tight">Alokasi Dana</h2>
                <p class="card-sub">Pilih komponen biaya yang ingin diperhitungkan.</p>
                <div class="alloc-groups">
                    @foreach($allocations as $category => $items)
                        <div>
                            <div class="alloc-cat">{{ $category }}</div>
                            <div class="alloc-list">
                                @foreach($items as $a)
                                    <label class="alloc-item {{ $a->is_base ? 'is-base' : '' }}">
                                        <input type="checkbox" name="allocation_ids[]" value="{{ $a->id }}"
                                               @checked($a->is_base) @disabled($a->is_base)>
                                        <span class="name">{{ $a->label }}</span>
                                        <span class="pct">{{ rtrim(rtrim(number_format($a->percentage * 100, 2), '0'), '.') }}%</span>
                                        @if($a->is_base)<input type="hidden" name="allocation_ids[]" value="{{ $a->id }}">@endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- 4. Design-to-Budget --}}
            <section class="card">
                <div class="step">Langkah 4</div>
                <h2 class="card-title">Design-to-Budget</h2>
                <div class="grid2">
                    <label class="field">
                        <span class="field-label">Budget</span>
                        <div class="rp-group">
                            <span class="rp-prefix">Rp</span>
                            <input name="budget" type="text" inputmode="numeric" data-rupiah class="rp-input" placeholder="mis. 2.000.000.000">
                        </div>
                        <p class="hint">Biaya ternyaman yang dapat digunakan untuk pembangunan.</p>
                    </label>
                    <label class="field">
                        <span class="field-label">Toleransi</span>
                        <div class="rp-group">
                            <span class="rp-prefix">Rp</span>
                            <input name="toleransi" type="text" inputmode="numeric" data-rupiah class="rp-input" placeholder="0">
                        </div>
                        <p class="hint">Biaya tertinggi yang masih dapat ditoleransi.</p>
                    </label>
                    <label class="field">
                        <span class="field-label">Dana darurat (%)</span>
                        <input name="dana_darurat_pct_display" type="number" step="0.1" min="0" class="control" placeholder="{{ rtrim(rtrim(number_format($settings['dana_darurat_pct'] * 100, 2), '0'), '.') }} (default)">
                        <p class="hint">Untuk kebutuhan masa depan di luar ekspektasi (sinking fund).</p>
                    </label>
                    <label class="field">
                        <span class="field-label">Tipe bangunan</span>
                        <select name="building_type_id" class="control">
                            <option value="">— Pilih —</option>
                            @foreach($buildingTypes as $bt)
                                <option value="{{ $bt->id }}">{{ $bt->name }} — Rp {{ number_format($bt->price_per_m2, 0, ',', '.') }}/m²</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                @if($components->isNotEmpty())
                <div class="comp-block">
                    <div class="comp-cap">Referensi kualitas per tipe bangunan</div>
                    <div class="comp-scroll">
                        <table class="comp-table">
                            <thead>
                                <tr>
                                    <th>Komponen</th><th>Standar</th><th>Optimal</th><th>Premium</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($components as $c)
                                    <tr>
                                        <td class="name">{{ $c->name }}</td>
                                        <td class="desc">{{ $c->standar }}</td>
                                        <td class="desc">{{ $c->optimal }}</td>
                                        <td class="desc">{{ $c->premium }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="hint">*Tidak termasuk furniture, interior, kolam renang, lift, landscape, peralatan elektronik.</p>
                </div>
                @endif
            </section>

            {{-- 5. Design-to-Regulation --}}
            <section class="card">
                <div class="step">Langkah 5</div>
                <h2 class="card-title">Design-to-Regulation</h2>
                <label class="field" style="max-width:420px">
                    <span class="field-label">Zonasi lahan</span>
                    <select name="zonasi_id" class="control">
                        <option value="">— Pilih —</option>
                        @foreach($zonasiList as $z)
                            <option value="{{ $z->id }}">{{ $z->code }} — {{ $z->name }}</option>
                        @endforeach
                    </select>
                </label>
                <div id="reg-info" class="reg-info"></div>
                <p class="hint">KDB / KLB / KTB / RTH dihitung otomatis dari zonasi &times; luas tanah. *Untuk kepastian butuh dokumen SKRK dari Dinas PU / PTSP setempat.</p>
            </section>

            {{-- 6. Design-to-Needs --}}
            <section class="card">
                <div class="step">Langkah 6</div>
                <div class="rooms-head">
                    <h2 class="card-title tight" style="margin-bottom:0">Design-to-Needs</h2>
                    <button type="button" id="add-room" class="btn btn-dark btn-add">Tambah ruangan</button>
                </div>
                <p class="card-sub">Susun kebutuhan ruangan beserta jumlah, tipe luasan, dan prioritasnya.</p>
                @if($sizeTiers->isNotEmpty())
                <details class="tier-ref">
                    <summary>Penjelasan tipe luasan</summary>
                    @foreach($sizeTiers as $t)
                        <div class="tier-item"><b>{{ $t->name }}</b> — <span>{{ $t->description }}</span></div>
                    @endforeach
                </details>
                @endif
                <input type="hidden" name="sirkulasi_pct" value="{{ $settings['sirkulasi_pct'] }}">
                <div id="rooms-body" class="room-list"></div>
                <div id="rooms-empty" class="rooms-empty">
                    Belum ada ruangan. Klik <b>“Tambah ruangan”</b> untuk mulai.
                </div>
                <p class="hint">Sirkulasi {{ (int) round($settings['sirkulasi_pct'] * 100) }}% ditambahkan otomatis pada total luas kebutuhan.</p>
            </section>
        </form>

        {{-- ================= RESULT PANEL ================= --}}
        <aside class="aside-col">
            <div id="result" class="result">
                <div class="result-empty">
                    <div class="result-cap">Ringkasan Estimasi</div>
                    <p>Lengkapi <b>luas tanah, faktor bobot, budget, tipe bangunan,</b> dan <b>zonasi</b> untuk melihat estimasi.</p>
                </div>
            </div>
            <button id="download-pdf" class="pdf-btn" disabled>Download PDF</button>
        </aside>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const ROOMS = @json($rooms);
const TIERS = @json($sizeTiers);
const ZONASI = @json($zonasiList);
const FACTOR_COUNT = {{ $factorGroups->count() }};

// Unit area (m²) for a room at a given size tier, from the catalog.
function roomUnitArea(roomId, tierId){
    const r = ROOMS.find(x => Number(x.id) === Number(roomId));
    if (!r || !r.areas) return null;
    const a = r.areas.find(x => Number(x.size_tier_id) === Number(tierId));
    return a ? Number(a.area) : null;
}

function rupiah(n){ return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }
function m2(n){ return (Math.round(n * 10) / 10).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 1}) + ' m²'; }
function esc(s){ return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function digitsToNumber(v){ const d = String(v == null ? '' : v).replace(/\D/g, ''); return d ? parseInt(d, 10) : 0; }

// --- Rupiah input formatting ---
document.querySelectorAll('input[data-rupiah]').forEach(el => {
    el.addEventListener('input', () => {
        const n = digitsToNumber(el.value);
        el.value = n ? n.toLocaleString('id-ID') : '';
    });
});

// --- Factor advisory notes (show the selected option's note below its dropdown) ---
document.querySelectorAll('.factor-select').forEach(sel => {
    const noteEl = sel.parentElement.querySelector('.factor-note');
    if (!noteEl) return;
    const upd = () => {
        const opt = sel.options[sel.selectedIndex];
        const note = opt ? (opt.getAttribute('data-note') || '') : '';
        noteEl.textContent = note;
        noteEl.style.display = note ? 'block' : 'none';
    };
    sel.addEventListener('change', upd);
    upd();
});

// --- Room builder ---
const roomsBody = document.getElementById('rooms-body');
const roomsEmpty = document.getElementById('rooms-empty');
function refreshRoomsEmpty(){ roomsEmpty.style.display = roomsBody.children.length ? 'none' : 'block'; }

function roomRow(){
    const wrap = document.createElement('div');
    wrap.className = 'room-row';
    const opts = ROOMS.map(r => `<option value="${r.id}">${esc(r.name)} — ${esc(r.category)}</option>`).join('');
    const tierOpts = TIERS.map(t => `<option value="${t.id}">${esc(t.name)}</option>`).join('');
    wrap.innerHTML = `
        <select class="r-room" aria-label="Ruangan"><option value="">— Pilih ruangan —</option>${opts}</select>
        <select class="r-tier" aria-label="Tipe luasan"><option value="">Tipe luasan</option>${tierOpts}</select>
        <input type="number" min="1" value="1" class="r-qty" aria-label="Jumlah">
        <select class="r-prio" aria-label="Prioritas">
            <option value="utama">Utama</option>
            <option value="sekunder">Sekunder</option>
            <option value="tersier">Tersier</option>
        </select>
        <button type="button" class="room-del" aria-label="Hapus">&times;</button>
        <div class="room-area"></div>`;
    const areaEl = wrap.querySelector('.room-area');
    function updArea(){
        const rid = Number(wrap.querySelector('.r-room').value) || 0;
        const tid = Number(wrap.querySelector('.r-tier').value) || 0;
        const qty = Number(wrap.querySelector('.r-qty').value) || 1;
        const unit = roomUnitArea(rid, tid);
        areaEl.innerHTML = (unit === null)
            ? '<span class="muted">Pilih ruangan &amp; tipe luasan untuk melihat luas.</span>'
            : `Luas: <b>${m2(unit)}</b> × ${qty} = <b>${m2(unit * qty)}</b>`;
    }
    wrap.querySelector('.room-del').addEventListener('click', () => { wrap.remove(); refreshRoomsEmpty(); recalc(); });
    wrap.querySelectorAll('select, input').forEach(el => el.addEventListener('change', () => { updArea(); recalc(); }));
    wrap.querySelector('.r-qty').addEventListener('input', updArea);
    updArea();
    return wrap;
}
document.getElementById('add-room').addEventListener('click', () => { roomsBody.appendChild(roomRow()); refreshRoomsEmpty(); });
refreshRoomsEmpty();

// --- Payload ---
function payload(){
    const fd = new FormData(document.getElementById('calc-form'));
    const data = {
        nama_proyek: fd.get('nama_proyek') || '',
        luas_tanah: parseFloat(fd.get('luas_tanah')) || 0,
        lokasi_proyek: fd.get('lokasi_proyek') || '',
        factor_option_ids: fd.getAll('factor_option_ids[]').filter(v => v !== '').map(Number),
        building_type_id: Number(fd.get('building_type_id')) || 0,
        zonasi_id: Number(fd.get('zonasi_id')) || 0,
        budget: digitsToNumber(fd.get('budget')),
        toleransi: digitsToNumber(fd.get('toleransi')),
        sirkulasi_pct: parseFloat(fd.get('sirkulasi_pct')) || 0,
        allocation_ids: fd.getAll('allocation_ids[]').filter(v => v !== '').map(Number),
        rooms: [...document.querySelectorAll('.room-row')]
            .filter(row => row.querySelector('.r-room').value && row.querySelector('.r-tier').value)
            .map(row => ({
                room_id: Number(row.querySelector('.r-room').value),
                size_tier_id: Number(row.querySelector('.r-tier').value),
                jumlah: Number(row.querySelector('.r-qty').value) || 1,
                prioritas: row.querySelector('.r-prio').value,
            })),
    };
    const dd = fd.get('dana_darurat_pct_display');
    if (dd !== '' && dd != null) data.dana_darurat_pct = (parseFloat(dd) || 0) / 100;
    return data;
}

function readyToCalc(p){
    return p.luas_tanah >= 1 && p.budget > 0 && p.building_type_id > 0 && p.zonasi_id > 0
        && p.factor_option_ids.length === FACTOR_COUNT;
}

// --- Live calc ---
const resultEl = document.getElementById('result');
const pdfBtn = document.getElementById('download-pdf');
let timer = null;

function showEmptyState(){
    pdfBtn.disabled = true;
    resultEl.innerHTML =
        '<div class="result-empty"><div class="result-cap">Ringkasan Estimasi</div>' +
        '<p>Lengkapi <b>luas tanah, faktor bobot, budget, tipe bangunan,</b> dan <b>zonasi</b> untuk melihat estimasi.</p></div>';
}

function recalc(){
    clearTimeout(timer);
    const p = payload();
    if (!readyToCalc(p)) { showEmptyState(); return; }
    timer = setTimeout(async () => {
        try {
            const res = await fetch("{{ route('kalkulator.calculate') }}", {
                method: 'POST',
                headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json'},
                body: JSON.stringify(p),
            });
            if (!res.ok) { showEmptyState(); return; }
            render(await res.json());
            pdfBtn.disabled = false;
        } catch (e) { showEmptyState(); }
    }, 300);
}

function metric(label, value){
    return `<div class="metric"><span class="m-label">${label}</span><span class="m-value">${value}</span></div>`;
}

function render(r){
    const selisih = (v) => v === null ? '<span class="muted">—</span>'
        : `<span class="${v < 0 ? 'neg' : 'pos'}">${rupiah(v)}</span>`;
    // Compact 3-column card; each value is placed BELOW its label.
    const cell = (label, value) =>
        `<div class="scen-cell"><span class="c-label">${label}</span><span class="c-value">${value}</span></div>`;
    const cards = r.summary.rows.map(row => `
        <div class="scen-card">
            <div class="scen-title">${esc(row.label)}</div>
            <div class="scen-grid">
                ${cell('Luas', m2(row.area))}
                ${cell('Biaya', rupiah(row.cost))}
                ${cell('Selisih', selisih(row.selisih))}
            </div>
        </div>`).join('');

    resultEl.innerHTML = `
        <div class="result-cap">Ringkasan Estimasi</div>
        <h3>Estimasi Budget</h3>
        <div class="hero">
            <div class="hero-label">Luas terjangkau (budget)</div>
            <div class="hero-value">${m2(r.budget.area)}</div>
            <div class="hero-sub">dari nett construction ${rupiah(r.budget.nett_construction)}</div>
        </div>
        <div class="metrics">
            ${metric('Bobot', '×' + r.weighting.bobot.toFixed(2))}
            ${metric('Harga per m² (berbobot)', rupiah(r.weighting.harga_per_m2_bobot))}
            ${metric('Luas terbangun (regulasi)', m2(r.regulation.luas_terbangun))}
            ${metric('Total kebutuhan ruang', m2(r.needs.grand_total))}
        </div>
        <div class="scen-cap">Perbandingan Skenario</div>
        <div class="scen-list">${cards}</div>`;
}

// --- PDF ---
pdfBtn.addEventListener('click', () => {
    if (pdfBtn.disabled) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('kalkulator.pdf') }}";
    const add = (k, v) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = k; i.value = v; form.appendChild(i); };
    add('_token', CSRF);
    const walk = (obj, prefix) => {
        Object.entries(obj).forEach(([k, v]) => {
            const key = prefix ? `${prefix}[${k}]` : k;
            if (Array.isArray(v)) v.forEach((item, i) => (item && typeof item === 'object') ? walk(item, `${key}[${i}]`) : add(`${key}[${i}]`, item));
            else if (v && typeof v === 'object') walk(v, key);
            else add(key, v);
        });
    };
    walk(payload(), '');
    document.body.appendChild(form);
    form.submit();
    form.remove();
});

// --- Regulation live preview (KDB / KLB / KTB / RTH + luas terbangun) ---
const regInfoEl = document.getElementById('reg-info');
function renderRegulation(){
    const fd = new FormData(document.getElementById('calc-form'));
    const zid = Number(fd.get('zonasi_id')) || 0;
    const land = parseFloat(fd.get('luas_tanah')) || 0;
    const z = ZONASI.find(x => Number(x.id) === zid);
    if (!z || land <= 0) {
        regInfoEl.innerHTML = '<p class="reg-empty">Pilih zonasi &amp; isi luas tanah untuk melihat KDB / KLB / KTB / RTH.</p>';
        return;
    }
    const pct = (f) => Math.round(f * 100) + '%';
    regInfoEl.innerHTML = `
        <table class="reg-table">
            <thead><tr><th>Parameter</th><th style="text-align:right">Koefisien</th><th style="text-align:right">Luas</th></tr></thead>
            <tbody>
                <tr><td>KDB</td><td class="n">${pct(z.kdb)}</td><td class="n">${m2(z.kdb * land)}</td></tr>
                <tr><td>KLB</td><td class="n">${Number(z.klb).toFixed(2)}</td><td class="n">${m2(z.klb * land)}</td></tr>
                <tr><td>KTB</td><td class="n">${pct(z.ktb)}</td><td class="n">${m2(z.ktb * land)}</td></tr>
                <tr><td>RTH</td><td class="n">${pct(z.rth)}</td><td class="n">${m2(z.rth * land)}</td></tr>
                <tr class="total"><td>Luas terbangun (maks. KLB)</td><td class="n"></td><td class="n">${m2(z.klb * land)}</td></tr>
            </tbody>
        </table>`;
}

// --- Init ---
document.getElementById('calc-form').addEventListener('change', () => { renderRegulation(); recalc(); });
document.getElementById('calc-form').addEventListener('input', () => { renderRegulation(); recalc(); });
renderRegulation();
showEmptyState();
</script>
</body>
</html>
