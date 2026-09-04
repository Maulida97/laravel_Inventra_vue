<?php

/**
 * File: UnitController.php
 * Module: Master Data
 * Layer: Controller
 *
 * Purpose:
 * Menangani HTTP request untuk entitas Unit.
 *
 * Responsibilities:
 * - Menerima request CRUD dari pengguna.
 * - Menggunakan Form Request untuk validasi input.
 * - Mengembalikan view Inertia.js.
 * 
 * Related Documentation:
 * - docs/sprints/SPRINT-03-MASTER-DATA.md
 */

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Http\Requests\MasterData\StoreUnitRequest;
use App\Http\Requests\MasterData\UpdateUnitRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $units = Unit::search($search)
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('MasterData/Units/Index', [
            'units' => $units,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('MasterData/Units/Form');
    }

    public function store(StoreUnitRequest $request)
    {
        $validated = $request->validated();

        Unit::create($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit)
    {
        return Inertia::render('MasterData/Units/Form', [
            'unit' => $unit,
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $validated = $request->validated();

        $unit->update($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        // Add dependency check here later when related models exist
        $unit->delete();

        return redirect()->route('units.index')
            ->with('success', 'Unit deleted successfully.');
    }
}


