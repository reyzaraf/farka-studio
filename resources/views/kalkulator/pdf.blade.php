@php
    $rp  = fn ($n) => 'Rp ' . number_format(round($n), 0, ',', '.');
    $ar  = fn ($n) => number_format(round($n, 1), 1, ',', '.') . ' m²';
    $pct = fn ($n) => rtrim(rtrim(number_format($n * 100, 2), '0'), '.') . '%';
    $prio = ['utama' => 'Primary', 'sekunder' => 'Secondary', 'tersier' => 'Tertiary'];

    // Full program (last summary row) vs budget baseline, for the verdict line.
    $summaryRows = $result['summary']['rows'];
    $lastNeed = end($summaryRows);
    $baseline = $result['summary']['baseline'];
@endphp
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 28px 34px 46px; }
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #1a1a1a; line-height: 1.45; }

    .head { background: #111; color: #fff; padding: 16px 18px; }
    .head .brand { font-size: 9px; letter-spacing: 3px; text-transform: uppercase; color: #b8b8b8; }
    .head .title { font-size: 20px; font-weight: bold; margin-top: 2px; }
    .head .meta { font-size: 9px; color: #cfcfcf; }
    .head td { vertical-align: top; }
    .disclaimer { color: #8a8a8a; font-size: 9px; margin: 8px 2px 0; }

    .cap { font-size: 8.5px; letter-spacing: 2px; text-transform: uppercase; color: #9a9a9a; margin: 18px 0 2px; }
    h2 { font-size: 13px; font-weight: bold; margin: 0 0 7px; padding-bottom: 4px; border-bottom: 1.5px solid #111; }

    table { width: 100%; border-collapse: collapse; }
    td, th { text-align: left; }
    .kv td { padding: 5px 8px; border-bottom: 1px solid #ededed; }
    .kv td.k { color: #555; width: 55%; }
    .kv td.v { text-align: right; font-weight: bold; }

    .data th { font-size: 8.5px; text-transform: uppercase; letter-spacing: .5px; color: #666; background: #f4f4f4; padding: 6px 8px; border-bottom: 1.5px solid #111; }
    .data td { padding: 5px 8px; border-bottom: 1px solid #ededed; }
    .right { text-align: right; }
    .num { text-align: right; font-weight: bold; }
    .neg { color: #c0392b; } .pos { color: #1f8f4e; } .muted { color: #9a9a9a; }

    .hero { background: #111; color: #fff; padding: 13px 16px; margin-bottom: 8px; }
    .hero .hl { font-size: 8.5px; letter-spacing: 2px; text-transform: uppercase; color: #b8b8b8; }
    .hero .hv { font-size: 24px; font-weight: bold; }
    .hero .hs { font-size: 9px; color: #cccccc; }

    .cols td { vertical-align: top; }
    .cols td.left { padding-right: 12px; }
    .cols td.rightcol { padding-left: 12px; }

    .note { background: #f4f4f4; border-left: 3px solid #111; padding: 9px 12px; margin-top: 8px; font-size: 10px; }
    .foot { color: #9a9a9a; font-size: 8.5px; text-align: center; margin-top: 20px; border-top: 1px solid #e5e5e5; padding-top: 8px; }
</style>
</head>
<body>

    {{-- ===== Header ===== --}}
    <table class="head">
        <tr>
            <td>
                <div class="brand">Farka Studio</div>
                <div class="title">Project Budget Estimate</div>
            </td>
            <td style="text-align:right;">
                <div class="meta">{{ $generatedAt }}</div>
                <div class="meta">{{ $input['nama_proyek'] ?: 'Untitled Project' }}</div>
            </td>
        </tr>
    </table>
    <div class="disclaimer">*Figures in this document are indicative only and not a final quotation.</div>

    {{-- ===== Two-column: Overview + Weighting ===== --}}
    <table class="cols">
        <tr>
            <td class="left" width="50%">
                <div class="cap">Overview</div>
                <h2>Project</h2>
                <table class="kv">
                    <tr><td class="k">Project name</td><td class="v">{{ $input['nama_proyek'] ?: '-' }}</td></tr>
                    <tr><td class="k">Location</td><td class="v">{{ $input['lokasi_proyek'] ?: '-' }}</td></tr>
                    <tr><td class="k">Land area</td><td class="v">{{ $ar($input['luas_tanah']) }}</td></tr>
                    <tr><td class="k">Building tier</td><td class="v">{{ $buildingType?->name ?? '-' }}</td></tr>
                    <tr><td class="k">Base price / m²</td><td class="v">{{ $buildingType ? $rp($buildingType->price_per_m2) : '-' }}</td></tr>
                </table>
            </td>
            <td class="rightcol" width="50%">
                <div class="cap">Weighting</div>
                <h2>Weight &times;{{ number_format($result['weighting']['bobot'], 2) }}</h2>
                <table class="kv">
                    @foreach($result['weighting']['factors'] as $f)
                        <tr><td class="k">{{ $f['label'] }}</td><td class="v">&times;{{ rtrim(rtrim(number_format($f['multiplier'], 2), '0'), '.') }}</td></tr>
                    @endforeach
                    <tr><td class="k">Price / m² (weighted)</td><td class="v">{{ $rp($result['weighting']['harga_per_m2_bobot']) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ===== Design-to-Budget ===== --}}
    <div class="cap">Design-to-Budget</div>
    <h2>Budget &amp; Affordable Area</h2>
    <div class="hero">
        <div class="hl">Affordable area within budget</div>
        <div class="hv">{{ $ar($result['budget']['area']) }}</div>
        <div class="hs">from a net construction budget of {{ $rp($result['budget']['nett_construction']) }}</div>
    </div>
    <table class="kv">
        <tr><td class="k">Budget</td><td class="v">{{ $rp($input['budget']) }}</td></tr>
        <tr><td class="k">Tolerance</td><td class="v">{{ $rp($input['toleransi']) }}</td></tr>
        <tr><td class="k">Gross budget</td><td class="v">{{ $rp($result['budget']['gross']) }}</td></tr>
        <tr><td class="k">Contingency ({{ $pct($input['dana_darurat_pct']) }})</td><td class="v">&minus; {{ $rp($result['budget']['dana_darurat']) }}</td></tr>
        <tr><td class="k">Net budget</td><td class="v">{{ $rp($result['budget']['nett']) }}</td></tr>
        <tr><td class="k">Total allocation (excl. building)</td><td class="v">{{ $pct($result['budget']['total_alokasi']) }}</td></tr>
        <tr><td class="k">Net construction budget</td><td class="v">{{ $rp($result['budget']['nett_construction']) }}</td></tr>
    </table>

    {{-- ===== Selected allocations ===== --}}
    @if($allocations->isNotEmpty())
    <div class="cap">Selected Allocations</div>
    <table class="data">
        <thead><tr><th>Group</th><th>Component</th><th class="right">Percentage</th></tr></thead>
        <tbody>
        @foreach($allocations as $category => $items)
            @foreach($items as $i => $a)
                <tr>
                    <td>{{ $i === 0 ? ucfirst($category) : '' }}</td>
                    <td>{{ $a->label }}{!! $a->is_base ? ' <span class="muted">(base)</span>' : '' !!}</td>
                    <td class="right">{{ $pct($a->percentage) }}</td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
    @endif

    {{-- ===== Design-to-Regulation ===== --}}
    <div class="cap">Design-to-Regulation</div>
    <h2>Zoning Regulation &mdash; {{ $zonasi?->code ?? $result['regulation']['code'] }}{{ $zonasi && $zonasi->name ? ' · ' . $zonasi->name : '' }}</h2>
    <table class="data">
        <thead><tr><th>Parameter</th><th class="right">Coefficient</th><th class="right">Area</th></tr></thead>
        <tbody>
            <tr><td>KDB — Building Coverage Ratio</td><td class="right">{{ $zonasi ? $pct($zonasi->kdb) : '-' }}</td><td class="num">{{ $ar($result['regulation']['kdb']) }}</td></tr>
            <tr><td>KLB — Floor Area Ratio</td><td class="right">{{ $zonasi ? rtrim(rtrim(number_format($zonasi->klb, 2), '0'), '.') : '-' }}</td><td class="num">{{ $ar($result['regulation']['klb']) }}</td></tr>
            <tr><td>KTB — Basement Coverage Ratio</td><td class="right">{{ $zonasi ? $pct($zonasi->ktb) : '-' }}</td><td class="num">{{ $ar($result['regulation']['ktb']) }}</td></tr>
            <tr><td>RTH — Green Open Space</td><td class="right">{{ $zonasi ? $pct($zonasi->rth) : '-' }}</td><td class="num">{{ $ar($result['regulation']['rth']) }}</td></tr>
        </tbody>
    </table>
    <table class="kv" style="margin-top:6px;">
        <tr><td class="k">Buildable area (max KLB)</td><td class="v">{{ $ar($result['regulation']['luas_terbangun']) }}</td></tr>
        <tr><td class="k">Estimated cost per zoning</td><td class="v">{{ $rp($result['regulation']['cost']) }}</td></tr>
    </table>

    {{-- ===== Design-to-Needs ===== --}}
    <div class="cap">Design-to-Needs</div>
    <h2>Room Program</h2>
    @if(count($result['needs']['rows']))
    <table class="data">
        <thead>
            <tr><th>Room</th><th>Priority</th><th class="right">Qty</th><th>Size tier</th><th class="right">Unit</th><th class="right">Total</th></tr>
        </thead>
        <tbody>
        @foreach($result['needs']['rows'] as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td>{{ $prio[$row['prioritas']] ?? ucfirst($row['prioritas']) }}</td>
                <td class="right">{{ $row['jumlah'] }}</td>
                <td>{{ $row['tier'] }}</td>
                <td class="right">{{ $ar($row['area_unit']) }}</td>
                <td class="num">{{ $ar($row['total']) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <table class="kv" style="margin-top:6px;">
        <tr><td class="k">Subtotal Primary / Secondary / Tertiary</td><td class="v">{{ $ar($result['needs']['subtotals']['utama']) }} / {{ $ar($result['needs']['subtotals']['sekunder']) }} / {{ $ar($result['needs']['subtotals']['tersier']) }}</td></tr>
        <tr><td class="k">Total room area</td><td class="v">{{ $ar($result['needs']['rooms_total']) }}</td></tr>
        <tr><td class="k">Circulation ({{ $pct($input['sirkulasi_pct']) }})</td><td class="v">+ {{ $ar($result['needs']['sirkulasi']) }}</td></tr>
        <tr><td class="k">Grand total required</td><td class="v">{{ $ar($result['needs']['grand_total']) }}</td></tr>
    </table>
    @else
    <div class="muted" style="padding:6px 2px;">No rooms selected.</div>
    @endif

    {{-- ===== Summary ===== --}}
    <div class="cap">Summary</div>
    <h2>Scenario Comparison</h2>
    <table class="data">
        <thead><tr><th>Scenario</th><th class="right">Area</th><th class="right">Estimated cost</th><th class="right">Difference vs budget</th></tr></thead>
        <tbody>
        @foreach($summaryRows as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td class="right">{{ $ar($row['area']) }}</td>
                <td class="num">{{ $rp($row['cost']) }}</td>
                <td class="num {{ $row['selisih'] !== null ? ($row['selisih'] < 0 ? 'neg' : 'pos') : 'muted' }}">
                    {!! $row['selisih'] === null ? '&mdash; (baseline)' : ($row['selisih'] < 0 ? '&minus; ' . $rp(abs($row['selisih'])) : '+ ' . $rp($row['selisih'])) !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @if($lastNeed && $lastNeed['selisih'] !== null)
    <div class="note">
        @if($lastNeed['selisih'] < 0)
            The full room program ({{ $ar($lastNeed['area']) }}) requires {{ $rp($lastNeed['cost']) }} —
            <b>exceeding</b> the net construction budget by <b>{{ $rp(abs($lastNeed['selisih'])) }}</b>.
            Consider adjusting the building tier, room priorities, or increasing the budget.
        @else
            The full room program ({{ $ar($lastNeed['area']) }}) at {{ $rp($lastNeed['cost']) }}
            <b>stays within</b> the net construction budget ({{ $rp($lastNeed['selisih']) }} remaining).
        @endif
    </div>
    @endif

    <div class="foot">Farka Studio · Budget Estimator · generated automatically on {{ $generatedAt }}</div>

</body>
</html>
