<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Gate;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Only owner can access activity logs
        if (auth()->user()->role !== 'owner') {
            abort(403, 'Unauthorized access.');
        }

        $query = ActivityLog::with('user')->latest();

        if ($search = $request->query('search')) {
            $query->where('description', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $logs = $query->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return view('activity_logs.partials.table_data', compact('logs'))->render();
        }

        return view('activity_logs.index', compact('logs'));
    }
}
