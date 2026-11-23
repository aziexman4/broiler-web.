<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    /**
     * Update enterprise pricing settings (live market price, DOC price, and feed reference price).
     */
    public function updatePrice(Request $request)
    {
        $validated = $request->validate([
            'global_market_price' => 'required|numeric|min:0',
            'doc_price' => 'nullable|numeric|min:0',
            'feed_price' => 'nullable|numeric|min:0',
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'global_market_price'],
            ['value' => $validated['global_market_price']]
        );

        if (isset($validated['doc_price'])) {
            SystemSetting::updateOrCreate(
                ['key' => 'doc_price'],
                ['value' => $validated['doc_price']]
            );
        }

        if (isset($validated['feed_price'])) {
            SystemSetting::updateOrCreate(
                ['key' => 'feed_price'],
                ['value' => $validated['feed_price']]
            );
        }

        return redirect()->back()->with('success', 'Pengaturan harga acuan peternakan berhasil diperbarui!');
    }
}
