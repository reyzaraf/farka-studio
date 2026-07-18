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
