<?php

/**
 * File: CategoryController.php
 * Module: Master Data
 * Layer: Controller
 *
 * Purpose:
 * Menangani HTTP request untuk entitas Category.
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
use App\Models\Category;
use App\Http\Requests\MasterData\StoreCategoryRequest;
use App\Http\Requests\MasterData\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $categories = Category::search($search)
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('MasterData/Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('MasterData/Categories/Form');
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('MasterData/Categories/Form', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Add dependency check here later when related models exist
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}


