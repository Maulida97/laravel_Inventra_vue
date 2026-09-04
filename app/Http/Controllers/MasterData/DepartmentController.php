<?php

/**
 * File: DepartmentController.php
 * Module: Master Data
 * Layer: Controller
 *
 * Purpose:
 * Menangani HTTP request untuk entitas Department.
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
use App\Models\Department;
use App\Http\Requests\MasterData\StoreDepartmentRequest;
use App\Http\Requests\MasterData\UpdateDepartmentRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $departments = Department::search($search)
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('MasterData/Departments/Index', [
            'departments' => $departments,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('MasterData/Departments/Form');
    }

    public function store(StoreDepartmentRequest $request)
    {
        $validated = $request->validated();

        Department::create($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return Inertia::render('MasterData/Departments/Form', [
            'department' => $department,
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $validated = $request->validated();

        $department->update($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        // Add dependency check here later when related models exist
        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}


