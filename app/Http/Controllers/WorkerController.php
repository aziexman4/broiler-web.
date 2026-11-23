<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FlockMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WorkerController extends Controller
{
    /**
     * Display a listing of worker users with productivity & discipline metrics.
     */
    public function index()
    {
        $workers = User::where('role', 'worker')
            ->withCount(['flocks', 'activityLogs'])
            ->with(['flocks.dailyRecords', 'flocks.harvests'])
            ->orderBy('name', 'asc')
            ->get();

        foreach ($workers as $worker) {
            $totalDailyRecords = 0;
            $closedFlocksCount = 0;
            $totalIp = 0;
            $validIpFlockCount = 0;

            foreach ($worker->flocks as $flock) {
                $dailyRecords = $flock->dailyRecords;
                $harvests = $flock->harvests;

                $totalDailyRecords += $dailyRecords->count();

                if ($flock->status === 'closed' && $harvests->isNotEmpty()) {
                    $closedFlocksCount++;

                    $metrics = FlockMetricsService::calculateForFlock($flock, $dailyRecords, $harvests);
                    $ip = $metrics['ip'];

                    if ($ip > 0) {
                        $totalIp += $ip;
                        $validIpFlockCount++;
                    }
                }
            }

            $worker->closed_flocks_count = $closedFlocksCount;
            $worker->daily_records_count = $totalDailyRecords;
            $worker->discipline_logs_count = $totalDailyRecords + $worker->activity_logs_count;
            $worker->average_ip = $validIpFlockCount > 0 ? round($totalIp / $validIpFlockCount, 2) : 0;
        }

        return view('workers.index', compact('workers'));
    }

    /**
     * Store a newly created worker user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'base_salary' => 'required|integer|min:0',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'worker',
            'base_salary' => $validated['base_salary'],
        ]);

        return redirect()->route('workers.index')->with('success', 'Akun pekerja baru berhasil ditambahkan!');
    }

    /**
     * Update the specified worker user in storage.
     */
    public function update(Request $request, User $worker)
    {
        if ($worker->role !== 'worker') {
            return redirect()->route('workers.index')->with('error', 'Hanya akun dengan role worker yang dapat diedit.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $worker->id,
            'base_salary' => 'required|integer|min:0',
            'password' => 'nullable|string|min:8',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'base_salary' => $validated['base_salary'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $worker->update($updateData);

        return redirect()->route('workers.index')->with('success', 'Data pegawai ' . $worker->name . ' berhasil diperbarui!');
    }

    /**
     * Remove the specified worker user from storage.
     */
    public function destroy(User $worker)
    {
        // Prevent deleting non-worker users for safety
        if ($worker->role !== 'worker') {
            return redirect()->route('workers.index')->with('error', 'Hanya akun dengan role worker yang dapat dihapus.');
        }

        if ($worker->payrolls()->exists()) {
            return redirect()->route('workers.index')
                ->with('error', "Akun pekerja {$worker->name} tidak dapat dihapus karena sudah memiliki riwayat penggajian (payroll).");
        }

        if ($worker->dailyRecords()->exists()) {
            return redirect()->route('workers.index')
                ->with('error', "Akun pekerja {$worker->name} tidak dapat dihapus karena sudah memiliki riwayat recording harian kandang.");
        }

        $name = $worker->name;
        $worker->delete();

        return redirect()->route('workers.index')->with('success', "Akun pekerja {$name} berhasil diberhentikan dan dihapus.");
    }
}
