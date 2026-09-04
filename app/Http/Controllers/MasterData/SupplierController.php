<?php

/**
 * File: SupplierController.php
 * Module: Master Data
 * Layer: Controller
 *
 * Purpose:
 * Menangani HTTP request untuk entitas Supplier.
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
use App\Models\Supplier;
use App\Http\Requests\MasterData\StoreSupplierRequest;
use App\Http\Requests\MasterData\UpdateSupplierRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $suppliers = Supplier::search($search)
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('MasterData/Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('MasterData/Suppliers/Form');
    }

    public function store(StoreSupplierRequest $request)
    {
        $validated = $request->validated();

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return Inertia::render('MasterData/Suppliers/Form', [
            'supplier' => $supplier,
        ]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $validated = $request->validated();

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        // Soft delete
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}


