<?php

namespace App\Http\Controllers;

use App\Models\OvkInventory;
use Illuminate\Http\Request;

class OvkController extends Controller
{
    /**
     * Display OVK inventory transactions and summary.
     */
    public function index()
    {
        $records = OvkInventory::orderBy('record_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalSpending = OvkInventory::where('transaction_type', 'in')->sum('total_price');

        return view('ovk.index', compact('records', 'totalSpending'));
    }

    /**
     * Store a newly created OVK transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'record_date' => 'required|date',
            'item_name' => 'required|string|max:255',
            'category' => 'required|in:obat,vaksin,kimia',
            'transaction_type' => 'required|in:in,out',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'total_price' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        // If transaction_type is 'out', clear total_price
        if ($validated['transaction_type'] === 'out') {
            $validated['total_price'] = null;
        }

        OvkInventory::create($validated);

        return redirect()->route('ovk.index')->with('success', 'Transaksi OVK berhasil dicatat!');
    }
}
