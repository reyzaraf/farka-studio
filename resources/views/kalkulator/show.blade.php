<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kalkulator Budget | Farka Studio</title>
    <link rel="icon" href="{{ asset('farkalogo.svg') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Noto+Sans+JP:wght@400;500;700&family=Source+Sans+3:wght@400;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                fontFamily: { sans: ['"Noto Sans JP"', 'sans-serif'] },
                extend: {
                    fontFamily: {
                        title: ['Montserrat', 'sans-serif'],
                        header: ['"Source Sans 3"', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        @layer base {
            h1, h2, .font-title { @apply font-title; }
            h3, h4, .font-header { @apply font-header; }
        }
    </style>
    <style>
        body { font-family: 'Noto Sans JP', sans-serif; }
        /* Remove number-input spinners for a cleaner look */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
    </style>
</head>
<body class="bg-white text-neutral-900 antialiased">

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">

    {{-- Top bar --}}
    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-[11px] uppercase tracking-[0.2em] text-black/40 hover:text-black transition">
        &larr; Farka Studio
    </a>

    <header class="mt-5 mb-8 sm:mb-10">
        <h1 class="font-title text-3xl sm:text-4xl font-extrabold tracking-tight">Kalkulator Budget Proyek</h1>
        <p class="mt-2 text-base sm:text-lg text-black/60 max-w-2xl">
            Estimasi kebutuhan biaya pembangunan berdasarkan preferensi Anda.
            <span class="text-black/40">*Angka hanya asumsi dan bukan final.</span>
        </p>
    </header>

    <div class="grid lg:grid-cols-3 gap-6 lg:gap-8 items-start">

        {{-- ================= FORM ================= --}}
        <form id="calc-form" class="lg:col-span-2 space-y-5 sm:space-y-6" autocomplete="off">

            {{-- 1. General --}}
            <section class="rounded-2xl border border-black/10 p-6 sm:p-7">
                <div class="text-[11px] uppercase tracking-[0.2em] text-black/40">Langkah 1</div>
                <h2 class="text-xl sm:text-2xl font-bold mt-1 mb-5">Informasi Umum</h2>
                <div class="grid sm:grid-cols-2 gap-4 sm:gap-5">
                    <label class="block">
                        <span class="text-sm font-medium text-black/70">Nama proyek</span>
                        <input name="nama_proyek" placeholder="mis. Rumah Tinggal Bapak A" class="input mt-1.5">
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-black/70">Luas tanah (m²)</span>
                        <input name="luas_tanah" type="number" step="0.1" min="0" placeholder="mis. 300" class="input mt-1.5">
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="text-sm font-medium text-black/70">Lokasi proyek</span>
                        <input name="lokasi_proyek" placeholder="Kota / wilayah" class="input mt-1.5">
                    </label>
                </div>
            </section>

            {{-- 2. Weighting factors --}}
            <section class="rounded-2xl border border-black/10 p-6 sm:p-7">
                <div class="text-[11px] uppercase tracking-[0.2em] text-black/40">Langkah 2</div>
                <h2 class="text-xl sm:text-2xl font-bold mt-1 mb-5">Faktor Bobot</h2>
                <div class="grid sm:grid-cols-2 gap-4 sm:gap-5">
                    @foreach($factorGroups as $group)
                        <label class="block">
                            <span class="text-sm font-medium text-black/70">{{ $group->name }}</span>
                            <select name="factor_option_ids[]" class="input mt-1.5" data-required-factor>
                                <option value="">— Pilih —</option>
                                @foreach($group->options as $opt)
                                    <option value="{{ $opt->id }}">{{ $opt->label }} (×{{ rtrim(rtrim(number_format($opt->multiplier, 2), '0'), '.') }})</option>
                                @endforeach
                            </select>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- 3. Allocations --}}
            <section class="rounded-2xl border border-black/10 p-6 sm:p-7">
                <div class="text-[11px] uppercase tracking-[0.2em] text-black/40">Langkah 3</div>
                <h2 class="text-xl sm:text-2xl font-bold mt-1 mb-1">Alokasi Dana</h2>
                <p class="text-sm text-black/50 mb-5">Pilih komponen biaya yang ingin diperhitungkan.</p>
                <div class="space-y-5">
                    @foreach($allocations as $category => $items)
                        <div>
                            <div class="text-[11px] uppercase tracking-[0.18em] text-black/40 mb-2">{{ $category }}</div>
                            <div class="grid sm:grid-cols-2 gap-2.5">
                                @foreach($items as $a)
                                    <label class="flex items-center gap-3 text-[15px] rounded-xl border border-black/10 px-4 py-3 cursor-pointer hover:border-black/30 transition {{ $a->is_base ? 'bg-black/[0.03]' : '' }}">
                                        <input type="checkbox" name="allocation_ids[]" value="{{ $a->id }}"
                                               class="h-4 w-4 accent-black shrink-0"
                                               @checked($a->is_base) @disabled($a->is_base)>
                                        <span class="flex-1">{{ $a->label }}</span>
                                        <span class="text-black/45 tabular-nums">{{ rtrim(rtrim(number_format($a->percentage * 100, 2), '0'), '.') }}%</span>
                                        @if($a->is_base)<input type="hidden" name="allocation_ids[]" value="{{ $a->id }}">@endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- 4. Design-to-Budget --}}
            <section class="rounded-2xl border border-black/10 p-6 sm:p-7">
                <div class="text-[11px] uppercase tracking-[0.2em] text-black/40">Langkah 4</div>
                <h2 class="text-xl sm:text-2xl font-bold mt-1 mb-5">Design-to-Budget</h2>
                <div class="grid sm:grid-cols-2 gap-4 sm:gap-5">
                    <label class="block">
                        <span class="text-sm font-medium text-black/70">Budget</span>
                        <div class="mt-1.5 flex items-stretch rounded-xl border border-black/15 focus-within:border-black overflow-hidden">
                            <span class="grid place-items-center px-3.5 text-black/50 bg-black/[0.03] border-r border-black/10">Rp</span>
                            <input name="budget" type="text" inputmode="numeric" data-rupiah placeholder="mis. 2.000.000.000" class="w-full px-4 py-3 text-base outline-none">
                        </div>
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-black/70">Toleransi</span>
                        <div class="mt-1.5 flex items-stretch rounded-xl border border-black/15 focus-within:border-black overflow-hidden">
                            <span class="grid place-items-center px-3.5 text-black/50 bg-black/[0.03] border-r border-black/10">Rp</span>
                            <input name="toleransi" type="text" inputmode="numeric" data-rupiah placeholder="0" class="w-full px-4 py-3 text-base outline-none">
                        </div>
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-black/70">Dana darurat (%)</span>
                        <input name="dana_darurat_pct_display" type="number" step="0.1" min="0" placeholder="{{ rtrim(rtrim(number_format($settings['dana_darurat_pct'] * 100, 2), '0'), '.') }} (default)" class="input mt-1.5">
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-black/70">Tipe bangunan</span>
                        <select name="building_type_id" class="input mt-1.5">
                            <option value="">— Pilih —</option>
                            @foreach($buildingTypes as $bt)
                                <option value="{{ $bt->id }}">{{ $bt->name }} — Rp {{ number_format($bt->price_per_m2, 0, ',', '.') }}/m²</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                {{-- Quality components reference (from calc_components, admin-managed) --}}
                @if($components->isNotEmpty())
                <div class="mt-6">
                    <div class="text-[11px] uppercase tracking-[0.18em] text-black/40 mb-2">Referensi kualitas per tipe bangunan</div>
                    <div class="overflow-x-auto rounded-xl border border-black/10">
                        <table class="w-full text-sm border-collapse min-w-[560px]">
                            <thead>
                                <tr class="bg-black/[0.03] text-left">
                                    <th class="px-4 py-3 font-semibold">Komponen</th>
                                    <th class="px-4 py-3 font-semibold">Standar</th>
                                    <th class="px-4 py-3 font-semibold">Optimal</th>
                                    <th class="px-4 py-3 font-semibold">Premium</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($components as $c)
                                    <tr class="border-t border-black/10 align-top">
                                        <td class="px-4 py-3 font-medium whitespace-nowrap">{{ $c->name }}</td>
                                        <td class="px-4 py-3 text-black/70">{{ $c->standar }}</td>
                                        <td class="px-4 py-3 text-black/70">{{ $c->optimal }}</td>
                                        <td class="px-4 py-3 text-black/70">{{ $c->premium }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-black/40 mt-2">*Tidak termasuk furniture, interior, kolam renang, lift, landscape, peralatan elektronik.</p>
                </div>
                @endif
            </section>

            {{-- 5. Design-to-Regulation --}}
            <section class="rounded-2xl border border-black/10 p-6 sm:p-7">
                <div class="text-[11px] uppercase tracking-[0.2em] text-black/40">Langkah 5</div>
                <h2 class="text-xl sm:text-2xl font-bold mt-1 mb-5">Design-to-Regulation</h2>
                <label class="block sm:max-w-md">
                    <span class="text-sm font-medium text-black/70">Zonasi lahan</span>
                    <select name="zonasi_id" class="input mt-1.5">
                        <option value="">— Pilih —</option>
                        @foreach($zonasiList as $z)
                            <option value="{{ $z->id }}">{{ $z->code }} — {{ $z->name }}</option>
                        @endforeach
                    </select>
                </label>
                <p class="text-xs text-black/40 mt-3">KDB/KLB/KTB/RTH dihitung otomatis dari zonasi &times; luas tanah. *Untuk kepastian butuh dokumen SKRK dari Dinas PU / PTSP setempat.</p>
            </section>

            {{-- 6. Design-to-Needs (room builder) --}}
            <section class="rounded-2xl border border-black/10 p-6 sm:p-7">
                <div class="text-[11px] uppercase tracking-[0.2em] text-black/40">Langkah 6</div>
                <div class="flex flex-wrap items-center justify-between gap-3 mt-1 mb-2">
                    <h2 class="text-xl sm:text-2xl font-bold">Design-to-Needs</h2>
                    <button type="button" id="add-room" class="inline-flex items-center gap-1.5 text-sm font-medium bg-black text-white rounded-xl px-4 py-2.5 hover:bg-black/85 transition">
                        + Tambah ruangan
                    </button>
                </div>
                <p class="text-sm text-black/50 mb-4">Susun kebutuhan ruangan beserta jumlah, tipe luasan, dan prioritasnya.</p>
                <input type="hidden" name="sirkulasi_pct" value="{{ $settings['sirkulasi_pct'] }}">
                <div id="rooms-body" class="space-y-2.5"></div>
                <div id="rooms-empty" class="text-sm text-black/40 rounded-xl border border-dashed border-black/15 px-4 py-6 text-center">
                    Belum ada ruangan. Klik <span class="font-medium text-black/60">“Tambah ruangan”</span> untuk mulai.
                </div>
                <p class="text-xs text-black/40 mt-3">Sirkulasi {{ (int) round($settings['sirkulasi_pct'] * 100) }}% ditambahkan otomatis pada total luas kebutuhan.</p>
            </section>
        </form>

        {{-- ================= RESULT PANEL ================= --}}
        <aside class="lg:col-span-1 lg:sticky lg:top-6 space-y-4">
            <div id="result" class="rounded-2xl border border-black/10 bg-white p-6 sm:p-7">
                <div id="result-empty" class="py-6 text-center">
                    <div class="text-[11px] uppercase tracking-[0.2em] text-black/40">Ringkasan Estimasi</div>
                    <p class="mt-3 text-black/50 text-[15px] leading-relaxed">Lengkapi <span class="font-medium text-black/70">luas tanah, faktor bobot, budget, tipe bangunan,</span> dan <span class="font-medium text-black/70">zonasi</span> untuk melihat estimasi.</p>
                </div>
            </div>
            <button id="download-pdf" disabled class="w-full bg-black text-white rounded-xl py-3.5 text-base font-semibold hover:bg-black/85 transition disabled:opacity-40 disabled:cursor-not-allowed">
                Download PDF
            </button>
        </aside>
    </div>
</div>

{{-- Shared input styling --}}
<style>
    .input {
        display: block; width: 100%;
        border: 1px solid rgba(0,0,0,0.15); border-radius: 0.75rem;
        padding: 0.75rem 1rem; font-size: 1rem; background: #fff; outline: none;
        transition: border-color .15s;
    }
    .input:focus { border-color: #000; }
    select.input { -webkit-appearance: none; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-opacity='0.5' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 0.9rem center; padding-right: 2.5rem;
    }
</style>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const ROOMS = @json($rooms);
const TIERS = @json($sizeTiers);

// --- Formatters ---
function rupiah(n){ return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }
function m2(n){ return (Math.round(n * 10) / 10).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 1}) + ' m²'; }
function esc(s){ return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function digitsToNumber(v){ const d = String(v).replace(/\D/g, ''); return d ? parseInt(d, 10) : 0; }

// --- Rupiah input formatting (thousand separators, value stays digits+dots) ---
document.querySelectorAll('input[data-rupiah]').forEach(el => {
    el.addEventListener('input', () => {
        const d = el.value.replace(/\D/g, '');
        el.value = d ? parseInt(d, 10).toLocaleString('id-ID') : '';
        recalc();
    });
});

// --- Room builder ---
const roomsBody = document.getElementById('rooms-body');
const roomsEmpty = document.getElementById('rooms-empty');

function refreshRoomsEmpty(){
    roomsEmpty.style.display = roomsBody.children.length ? 'none' : 'block';
}

function roomRow(){
    const wrap = document.createElement('div');
    wrap.className = 'room-row grid grid-cols-1 sm:grid-cols-12 gap-2 sm:items-center rounded-xl border border-black/10 p-3 sm:p-2.5';
    const opts = ROOMS.map(r => `<option value="${r.id}">${esc(r.name)} — ${esc(r.category)}</option>`).join('');
    const tierOpts = TIERS.map(t => `<option value="${t.id}">${esc(t.name)}</option>`).join('');
    wrap.innerHTML = `
        <select class="r-room sm:col-span-5 rounded-lg border border-black/15 px-3 py-2.5 text-[15px] outline-none focus:border-black" aria-label="Ruangan">
            <option value="">— Pilih ruangan —</option>${opts}
        </select>
        <select class="r-tier sm:col-span-3 rounded-lg border border-black/15 px-3 py-2.5 text-[15px] outline-none focus:border-black" aria-label="Tipe luasan">
            <option value="">Tipe luasan</option>${tierOpts}
        </select>
        <input type="number" min="1" value="1" class="r-qty sm:col-span-1 rounded-lg border border-black/15 px-3 py-2.5 text-[15px] outline-none focus:border-black" aria-label="Jumlah">
        <select class="r-prio sm:col-span-2 rounded-lg border border-black/15 px-3 py-2.5 text-[15px] outline-none focus:border-black" aria-label="Prioritas">
            <option value="utama">Utama</option>
            <option value="sekunder">Sekunder</option>
            <option value="tersier">Tersier</option>
        </select>
        <button type="button" class="r-del sm:col-span-1 rounded-lg border border-black/10 py-2.5 text-black/40 hover:text-red-600 hover:border-red-300 transition" aria-label="Hapus">&times;</button>`;
    wrap.querySelector('.r-del').addEventListener('click', () => { wrap.remove(); refreshRoomsEmpty(); recalc(); });
    wrap.querySelectorAll('select, input').forEach(el => el.addEventListener('change', recalc));
    return wrap;
}
document.getElementById('add-room').addEventListener('click', () => {
    roomsBody.appendChild(roomRow());
    refreshRoomsEmpty();
});
refreshRoomsEmpty();

// --- Payload ---
function payload(){
    const f = document.getElementById('calc-form');
    const fd = new FormData(f);
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
    // dana darurat: only send when the user typed a value; otherwise backend applies the setting default.
    const dd = fd.get('dana_darurat_pct_display');
    if (dd !== '' && dd != null) data.dana_darurat_pct = (parseFloat(dd) || 0) / 100;
    return data;
}

// Minimum inputs required before we attempt a calculation.
function readyToCalc(p){
    return p.luas_tanah >= 1
        && p.budget > 0
        && p.building_type_id > 0
        && p.zonasi_id > 0
        && p.factor_option_ids.length === {{ $factorGroups->count() }};
}

// --- Live calc ---
const resultEl = document.getElementById('result');
const pdfBtn = document.getElementById('download-pdf');
let timer = null;

function showEmptyState(){
    pdfBtn.disabled = true;
    resultEl.innerHTML = `
        <div class="py-6 text-center">
            <div class="text-[11px] uppercase tracking-[0.2em] text-black/40">Ringkasan Estimasi</div>
            <p class="mt-3 text-black/50 text-[15px] leading-relaxed">Lengkapi <span class="font-medium text-black/70">luas tanah, faktor bobot, budget, tipe bangunan,</span> dan <span class="font-medium text-black/70">zonasi</span> untuk melihat estimasi.</p>
        </div>`;
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
    return `<div class="flex items-baseline justify-between gap-3 py-2 border-b border-black/[0.06]">
        <span class="text-[13px] text-black/50">${label}</span>
        <span class="font-header font-semibold text-[15px] sm:text-base tabular-nums text-right">${value}</span>
    </div>`;
}

function render(r){
    const selisih = (v) => v === null
        ? '<span class="text-black/30">—</span>'
        : `<span class="${v < 0 ? 'text-red-600' : 'text-emerald-600'} tabular-nums">${rupiah(v)}</span>`;

    // On mobile each metric is a "Label …… Value" row (values right-aligned, never cramped);
    // on sm+ it becomes a 3-column label-over-value grid.
    const cell = (label, value) => `
        <div class="flex items-baseline justify-between gap-2 sm:block">
            <div class="text-black/40 text-[13px] sm:text-[11px]">${label}</div>
            <div class="font-header font-semibold text-[15px] tabular-nums text-right sm:text-left">${value}</div>
        </div>`;
    const cards = r.summary.rows.map(row => `
        <div class="rounded-xl border border-black/10 p-4">
            <div class="text-[11px] uppercase tracking-[0.15em] text-black/40 mb-2.5">${esc(row.label)}</div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-2">
                ${cell('Luas', m2(row.area))}
                ${cell('Biaya', rupiah(row.cost))}
                ${cell('Selisih', selisih(row.selisih))}
            </div>
        </div>`).join('');

    resultEl.innerHTML = `
        <div class="text-[11px] uppercase tracking-[0.2em] text-black/40">Ringkasan Estimasi</div>
        <h3 class="font-title text-lg font-bold mt-1 mb-4">Estimasi Budget</h3>

        <div class="rounded-xl bg-black text-white p-5 mb-5">
            <div class="text-[11px] uppercase tracking-[0.18em] text-white/50">Luas terjangkau (budget)</div>
            <div class="font-title text-3xl font-extrabold mt-1 tabular-nums">${m2(r.budget.area)}</div>
            <div class="text-sm text-white/60 mt-1">dari nett construction ${rupiah(r.budget.nett_construction)}</div>
        </div>

        <div class="mb-6">
            ${metric('Bobot', '×' + r.weighting.bobot.toFixed(2))}
            ${metric('Harga per m² (berbobot)', rupiah(r.weighting.harga_per_m2_bobot))}
            ${metric('Luas terbangun (regulasi)', m2(r.regulation.luas_terbangun))}
            ${metric('Total kebutuhan ruang', m2(r.needs.grand_total))}
        </div>

        <div class="text-[11px] uppercase tracking-[0.2em] text-black/40 mb-3">Perbandingan Skenario</div>
        <div class="space-y-2.5">${cards}</div>`;
}

// --- PDF: submit collected payload to the pdf route ---
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

// --- Init: empty state, recalc only on interaction ---
document.getElementById('calc-form').addEventListener('change', recalc);
document.getElementById('calc-form').addEventListener('input', recalc);
showEmptyState();
</script>
</body>
</html>
