<?php

namespace App\Http\Controllers;

use App\Models\FeedRequest;
use App\Models\Flock;
use App\Models\FeedInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FeedRequestController extends Controller
{
    public function store(Request $request, Flock $flock)
    {
        Gate::authorize('update', $flock);
        $this->ensureFlockIsActive($flock);

        $validated = $request->validate([
            'feed_type' => 'required|string|in:Pre-Starter,Starter,Finisher',
            'amount_kg' => 'required|numeric|min:1'
        ]);

        FeedRequest::create([
            'flock_id' => $flock->id,
            'feed_type' => $validated['feed_type'],
            'amount_kg' => $validated['amount_kg'],
            'status' => 'pending'
        ]);

        return back()->with('success', 'Permintaan pakan berhasil dikirim ke Pemilik.');
    }

    /**
     * Reject feed request actions when a flock cycle has been closed.
     */
    private function ensureFlockIsActive(Flock $flock): void
    {
        abort_if(
            $flock->status === 'closed',
            403,
            'Siklus kandang sudah ditutup (Closed). Permintaan dan penerimaan pakan tidak dapat diproses lagi.'
        );
    }

    public function ship(FeedRequest $feedRequest)
    {
        if (!auth()->user()->isOwner()) {
            abort(403, 'Hanya Pemilik yang dapat menyetujui pengiriman.');
        }

        $this->ensureFlockIsActive($feedRequest->flock);

        FeedInventory::create([
            'transaction_date' => now(),
            'feed_name' => $feedRequest->feed_type . ' (Kirim ke Kandang)',
            'transaction_type' => 'out',
            'quantity_kg' => $feedRequest->amount_kg,
            'flock_id' => $feedRequest->flock_id,
            'notes' => 'Dikirim ke Kandang: ' . $feedRequest->flock->name
        ]);

        $feedRequest->update(['status' => 'shipped']);

        return back()->with('success', 'Pakan berhasil dikirim ke Kandang.');
    }

    public function receive(FeedRequest $feedRequest)
    {
        Gate::authorize('update', $feedRequest->flock);
        $this->ensureFlockIsActive($feedRequest->flock);

        if ($feedRequest->status !== 'shipped') {
            abort(400, 'Status tidak valid.');
        }

        $feedRequest->update(['status' => 'completed']);
        
        $flock = $feedRequest->flock;

        // Increment specific stock based on feed type
        if ($feedRequest->feed_type === 'Pre-Starter') {
            $flock->increment('stock_pre_starter_kg', $feedRequest->amount_kg);
        } elseif ($feedRequest->feed_type === 'Starter') {
            $flock->increment('stock_starter_kg', $feedRequest->amount_kg);
        } elseif ($feedRequest->feed_type === 'Finisher') {
            $flock->increment('stock_finisher_kg', $feedRequest->amount_kg);
        }

        return back()->with('success', 'Pakan telah diterima dan masuk ke stok Kandang.');
    }
}
