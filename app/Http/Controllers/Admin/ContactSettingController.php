<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use Illuminate\Http\Request;

class ContactSettingController extends Controller
{
    public function edit()
    {
        // Get the first record, or instantiate a new one if it doesn't exist
        $setting = ContactSetting::first() ?? new ContactSetting();
        return view('admin.contact-settings.form', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        $setting = ContactSetting::first();
        
        if ($setting) {
            $setting->update($validated);
        } else {
            ContactSetting::create($validated);
        }

        return redirect()->route('admin.contact-settings.edit')->with('success', 'Contact settings updated successfully.');
    }
}
