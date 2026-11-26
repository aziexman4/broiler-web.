<?php

namespace App\Http\Controllers;

use App\Models\FeedRequest;
use App\Models\Flock;
use App\Models\FeedInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $errorMsg = null;

        DB::transaction(function () use ($feedRequest, &$errorMsg) {
            $lockedRequest = FeedRequest::lockForUpdate()->findOrFail($feedRequest->id);

            if ($lockedRequest->status !== 'pending') {
                $errorMsg = 'Permintaan pakan ini sudah diproses dan tidak dapat dikirim ulang.';
                return;
            }

            $totalIn = (float) FeedInventory::where('transaction_type', 'in')->sum('quantity_kg');
            $totalOut = (float) FeedInventory::where('transaction_type', 'out')->sum('quantity_kg');
            $availableStock = $totalIn - $totalOut;

            if ($availableStock < (float) $lockedRequest->amount_kg) {
                $errorMsg = 'Stok Gudang Pusat tidak mencukupi untuk pengiriman ini (Tersedia: '
                    . number_format($availableStock, 2)
                    . ' kg, Diminta: '
                    . number_format((float) $lockedRequest->amount_kg, 2)
                    . ' kg).';
                return;
            }

            FeedInventory::create([
                'transaction_date' => now(),
                'feed_name' => $lockedRequest->feed_type . ' (Kirim ke Kandang)',
                'transaction_type' => 'out',
                'quantity_kg' => $lockedRequest->amount_kg,
                'flock_id' => $lockedRequest->flock_id,
                'notes' => 'Dikirim ke Kandang: ' . $lockedRequest->flock->name
            ]);

            $lockedRequest->update(['status' => 'shipped']);
        });

        if ($errorMsg !== null) {
            return back()->with('error', $errorMsg);
        }

        return back()->with('success', 'Pakan berhasil dikirim ke Kandang.');
    }

    public function reject(FeedRequest $feedRequest)
    {
        $user = auth()->user();
        if (!$user->isOwner() && !Gate::allows('update', $feedRequest->flock)) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan permintaan pakan ini.');
        }

        $this->ensureFlockIsActive($feedRequest->flock);

        $errorMsg = null;

        DB::transaction(function () use ($feedRequest, &$errorMsg) {
            $lockedRequest = FeedRequest::lockForUpdate()->findOrFail($feedRequest->id);

            if ($lockedRequest->status !== 'pending') {
                $errorMsg = 'Hanya permintaan pakan berstatus Pending yang dapat ditolak atau dibatalkan.';
                return;
            }

            $lockedRequest->update(['status' => 'rejected']);
        });

        if ($errorMsg !== null) {
            return back()->with('error', $errorMsg);
        }

        $msg = $user->isOwner()
            ? 'Permintaan pakan berhasil ditolak.'
            : 'Permintaan pakan berhasil dibatalkan.';

        return back()->with('success', $msg);
    }

    public function receive(FeedRequest $feedRequest)
    {
        Gate::authorize('update', $feedRequest->flock);
        $this->ensureFlockIsActive($feedRequest->flock);

        DB::transaction(function () use ($feedRequest) {
            $lockedRequest = FeedRequest::lockForUpdate()->findOrFail($feedRequest->id);

            if ($lockedRequest->status !== 'shipped') {
                abort(400, 'Status tidak valid.');
            }

            $lockedRequest->update(['status' => 'completed']);

            $flock = $lockedRequest->flock;

            // Increment specific stock based on feed type
            if ($lockedRequest->feed_type === 'Pre-Starter') {
                $flock->increment('stock_pre_starter_kg', $lockedRequest->amount_kg);
            } elseif ($lockedRequest->feed_type === 'Starter') {
                $flock->increment('stock_starter_kg', $lockedRequest->amount_kg);
            } elseif ($lockedRequest->feed_type === 'Finisher') {
                $flock->increment('stock_finisher_kg', $lockedRequest->amount_kg);
            }
        });

        return back()->with('success', 'Pakan telah diterima dan masuk ke stok Kandang.');
    }
}
