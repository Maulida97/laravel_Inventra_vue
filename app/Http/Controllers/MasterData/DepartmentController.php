<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Department;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:departments,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

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

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

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
