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
