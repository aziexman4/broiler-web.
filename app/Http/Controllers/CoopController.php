<?php

namespace App\Http\Controllers;

use App\Models\Coop;
use Illuminate\Http\Request;

class CoopController extends Controller
{
    /**
     * Display a listing of coop units and locations.
     */
    public function index()
    {
        $coops = Coop::withCount('flocks')
            ->orderByDesc('is_headquarters')
            ->orderBy('name', 'asc')
            ->get();

        return view('coops.index', compact('coops'));
    }

    /**
     * Store a newly created coop unit in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'manager_name' => ['required', 'string', 'max:255'],
            'is_headquarters' => ['nullable', 'boolean'],
        ]);

        $isHeadquarters = $request->boolean('is_headquarters');
        $validated['is_headquarters'] = $isHeadquarters;

        if ($isHeadquarters) {
            Coop::where('is_headquarters', true)->update(['is_headquarters' => false]);
        }

        $coop = Coop::create($validated);

        return redirect()->route('coops.index')
            ->with('success', "Unit lokasi kandang {$coop->name} berhasil ditambahkan!");
    }

    /**
     * Update the specified coop unit in storage.
     */
    public function update(Request $request, Coop $coop)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'manager_name' => ['required', 'string', 'max:255'],
            'is_headquarters' => ['nullable', 'boolean'],
        ]);

        $isHeadquarters = $request->boolean('is_headquarters');
        $validated['is_headquarters'] = $isHeadquarters;

        if ($isHeadquarters) {
            Coop::where('id', '!=', $coop->id)
                ->where('is_headquarters', true)
                ->update(['is_headquarters' => false]);
        }

        $coop->update($validated);

        return redirect()->route('coops.index')
            ->with('success', "Data unit lokasi {$coop->name} berhasil diperbarui!");
    }

    /**
     * Remove the specified coop unit from storage.
     */
    public function destroy(Coop $coop)
    {
        if ($coop->is_headquarters === true) {
            return redirect()->route('coops.index')
                ->with('error', "Unit lokasi {$coop->name} berstatus Kantor Pusat (HQ) dan tidak dapat dihapus.");
        }

        if ($coop->flocks()->exists()) {
            return redirect()->route('coops.index')
                ->with('error', "Unit lokasi {$coop->name} tidak dapat dihapus karena masih memiliki relasi siklus kandang.");
        }

        $name = $coop->name;
        $coop->delete();

        return redirect()->route('coops.index')
            ->with('success', "Unit lokasi {$name} telah dihapus dari sistem.");
    }
}
