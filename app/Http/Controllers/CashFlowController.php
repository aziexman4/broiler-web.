<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use Illuminate\Http\Request;

class CashFlowController extends Controller
{
    /**
     * Display cash flow history and metrics.
     */
    public function index()
    {
        $records = CashFlow::orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $total_in = CashFlow::where('type', 'in')->sum('amount');
        $total_out = CashFlow::where('type', 'out')->sum('amount');
        $balance = $total_in - $total_out;

        return view('cash_flows.index', compact('records', 'total_in', 'total_out', 'balance'));
    }

    /**
     * Store a newly created cash flow entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'type' => 'required|in:in,out',
            'amount' => 'required|integer|min:1',
        ]);

        CashFlow::create($validated);

        return redirect()->route('cash-flows.index')->with('success', 'Catatan transaksi kas berhasil disimpan!');
    }
}
