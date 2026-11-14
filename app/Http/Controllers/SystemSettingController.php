<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function updatePrice(Request $request)
    {
        $request->validate([
            'global_market_price' => 'required|numeric|min:0'
        ]);

        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'global_market_price'],
            ['value' => $request->global_market_price]
        );

        return redirect()->back()->with('success', 'Harga Pasar Global berhasil diperbarui!');
    }
}
