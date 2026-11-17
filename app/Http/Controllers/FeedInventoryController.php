<?php

namespace App\Http\Controllers;

use App\Models\FeedInventory;
use App\Models\Flock;
use Illuminate\Http\Request;

class FeedInventoryController extends Controller
{
    /**
     * Display feed inventory stock and history with optional period filtering.
     */
    public function index(Request $request)
    {
        $summary = FeedInventory::getFilteredSummary($request);
        $flocks = Flock::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('feed.index', [
            'inventories' => $summary['records'],
            'totalIn' => $summary['totalIn'],
            'totalOut' => $summary['totalOut'],
            'currentStock' => $summary['currentStock'],
            'activeFilterType' => $summary['activeFilterType'],
            'periodLabel' => $summary['periodLabel'],
            'flocks' => $flocks,
        ]);
    }

    /**
     * Store new feed stock entry (Stock In or Stock Out).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'feed_name' => 'required|string|max:255',
            'transaction_type' => 'required|in:in,out',
            'quantity_kg' => 'required|numeric|min:0.01',
            'price_per_kg' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:255',
            'flock_id' => 'nullable|exists:flocks,id',
        ]);

        FeedInventory::create($validated);

        return redirect()->route('feed.index')->with('success', 'Transaksi pakan berhasil disimpan!');
    }

    /**
     * Inline update for Feed Inventory.
     */
    public function inlineUpdate(Request $request, FeedInventory $feed)
    {
        $validated = $request->validate([
            'field' => 'required|in:notes,quantity_kg',
            'value' => 'nullable',
        ]);

        $field = $validated['field'];
        $value = $validated['value'];

        if ($field === 'quantity_kg') {
            $request->validate(['value' => 'required|numeric|min:0.01']);
            $feed->quantity_kg = $value;
        } else {
            $request->validate(['value' => 'nullable|string|max:255']);
            $feed->notes = $value;
        }

        $feed->save();

        // Calculate new KPIs
        $totalIn = FeedInventory::where('transaction_type', 'in')->sum('quantity_kg');
        $totalOut = FeedInventory::where('transaction_type', 'out')->sum('quantity_kg');
        $currentStock = $totalIn - $totalOut;

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui!',
            'kpis' => [
                'totalIn' => number_format($totalIn, 2, ',', '.'),
                'totalOut' => number_format($totalOut, 2, ',', '.'),
                'currentStock' => number_format($currentStock, 2, ',', '.'),
                'isCritical' => $currentStock < 500
            ],
            'newValue' => $field === 'quantity_kg' ? number_format($feed->quantity_kg, 2, ',', '.') : $feed->notes
        ]);
    }
}
