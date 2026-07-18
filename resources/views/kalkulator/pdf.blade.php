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
