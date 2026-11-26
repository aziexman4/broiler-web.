<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Flock;
use App\Models\OvkInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OvkController extends Controller
{
    /**
     * Display OVK inventory transactions, per-item available stock, and summary.
     */
    public function index()
    {
        $records = OvkInventory::with('flock.coop')
            ->orderBy('record_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $totalSpending = OvkInventory::where('transaction_type', 'in')->sum('total_price');
        $activeFlocks = Flock::with('coop')
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        $stockBalances = OvkInventory::selectRaw("
                LOWER(TRIM(item_name)) as item_key,
                MAX(item_name) as item_name,
                MAX(category) as category,
                LOWER(TRIM(unit)) as unit_key,
                MAX(unit) as unit,
                SUM(CASE WHEN transaction_type = 'in' THEN quantity ELSE -quantity END) as balance
            ")
            ->groupByRaw('LOWER(TRIM(item_name)), LOWER(TRIM(unit))')
            ->orderBy('item_name')
            ->get();

        return view('ovk.index', compact('records', 'totalSpending', 'activeFlocks', 'stockBalances'));
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
            'flock_id' => 'nullable|exists:flocks,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'total_price' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'sync_cash_flow' => 'nullable|boolean',
        ]);

        // Clear total_price on usage out; clear flock_id on warehouse purchase in
        if ($validated['transaction_type'] === 'out') {
            $validated['total_price'] = null;
        } else {
            $validated['flock_id'] = null;
        }

        $syncCashFlow = $request->boolean('sync_cash_flow');
        $errorMessage = null;

        DB::transaction(function () use ($validated, $syncCashFlow, &$errorMessage) {
            if ($validated['transaction_type'] === 'out') {
                $itemKey = strtolower(trim($validated['item_name']));
                $unitKey = strtolower(trim($validated['unit']));

                $totalIn = (float) OvkInventory::whereRaw('LOWER(TRIM(item_name)) = ?', [$itemKey])
                    ->whereRaw('LOWER(TRIM(unit)) = ?', [$unitKey])
                    ->where('transaction_type', 'in')
                    ->sum('quantity');

                $totalOut = (float) OvkInventory::whereRaw('LOWER(TRIM(item_name)) = ?', [$itemKey])
                    ->whereRaw('LOWER(TRIM(unit)) = ?', [$unitKey])
                    ->where('transaction_type', 'out')
                    ->sum('quantity');

                $available = $totalIn - $totalOut;
                if ($available < (float) $validated['quantity']) {
                    $errorMessage = "Stok OVK {$validated['item_name']} ({$validated['unit']}) tidak mencukupi (Tersedia: "
                        . number_format($available, 2) . ", Diminta: "
                        . number_format((float) $validated['quantity'], 2) . ").";
                    return;
                }
            }

            $record = OvkInventory::create(collect($validated)->except('sync_cash_flow')->all());

            if ($syncCashFlow && $validated['transaction_type'] === 'in' && (int) ($validated['total_price'] ?? 0) > 0) {
                CashFlow::create([
                    'transaction_date' => $record->record_date,
                    'description' => "Belanja OVK: {$record->item_name} ({$record->quantity} {$record->unit})",
                    'type' => 'out',
                    'amount' => (int) $validated['total_price'],
                ]);
            }
        });

        if ($errorMessage !== null) {
            return redirect()->route('ovk.index')->with('error', $errorMessage)->withInput();
        }

        $msg = ($syncCashFlow && $validated['transaction_type'] === 'in' && (int) ($validated['total_price'] ?? 0) > 0)
            ? 'Transaksi OVK berhasil dicatat & pengeluaran disinkronkan ke Arus Kas!'
            : 'Transaksi OVK berhasil dicatat!';

        return redirect()->route('ovk.index')->with('success', $msg);
    }
}
