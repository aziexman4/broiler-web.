<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WorkerController extends Controller
{
    /**
     * Display a listing of worker users.
     */
    public function index()
    {
        $workers = User::where('role', 'worker')
            ->orderBy('name', 'asc')
            ->get();

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
     * Remove the specified worker user from storage.
     */
    public function destroy(User $worker)
    {
        // Prevent deleting non-worker users for safety
        if ($worker->role !== 'worker') {
            return redirect()->route('workers.index')->with('error', 'Hanya akun dengan role worker yang dapat dihapus.');
        }

        $worker->delete();

        return redirect()->route('workers.index')->with('success', 'Akun pekerja berhasil diberhentikan dan dihapus.');
    }
}
