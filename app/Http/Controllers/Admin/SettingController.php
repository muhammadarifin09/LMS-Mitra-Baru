<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $kkm = Setting::getValue('kkm_global', 60);

        return view('admin.settings.index', compact('kkm'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'kkm' => 'required|integer|min:0|max:100'
        ]);

        Setting::updateOrCreate(
            ['key' => 'kkm_global'],
            ['value' => $request->kkm]
        );

        return back()->with('success', 'KKM berhasil diperbarui.');
    }
}