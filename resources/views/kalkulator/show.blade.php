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
