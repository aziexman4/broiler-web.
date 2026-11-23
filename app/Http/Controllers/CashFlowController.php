<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use Illuminate\Http\Request;

class CashFlowController extends Controller
{
    /**
     * Display cash flow history and metrics with optional period filtering.
     */
    public function index(Request $request)
    {
        $summary = CashFlow::getFilteredSummary($request, 20);

        return view('cash_flows.index', [
            'records' => $summary['records'],
            'total_in' => $summary['total_in'],
            'total_out' => $summary['total_out'],
            'balance' => $summary['balance'],
            'activeFilterType' => $summary['activeFilterType'],
            'periodLabel' => $summary['periodLabel'],
        ]);
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
