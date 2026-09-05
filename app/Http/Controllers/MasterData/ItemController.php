<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreItemRequest;
use App\Http\Requests\MasterData\UpdateItemRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\Unit;
use App\Services\MasterData\ItemService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function __construct(
        private ItemService $itemService
    ) {}

    public function index(): Response
    {
        $items = Item::with(['category', 'baseUnit'])
            ->search(request('search'))
            ->when(request('item_type'), function ($q, $type) {
                return $q->where('item_type', $type);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('MasterData/Items/Index', [
            'items' => $items,
            'filters' => request()->only(['search', 'item_type']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('MasterData/Items/Form', [
            'categories' => Category::where('status', 'ACTIVE')->get(),
            'units' => Unit::where('status', 'ACTIVE')->get(),
            'departments' => Department::where('status', 'ACTIVE')->get(),
            'item' => new Item(['status' => 'ACTIVE', 'item_type' => 'quantity', 'minimum_stock' => 0]),
        ]);
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        $this->itemService->createItem(
            $request->validated(),
            $request->input('department_ids')
        );

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    public function edit(Item $item): Response
    {
        $item->load('departments');
        $item->department_ids = $item->departments->pluck('id')->toArray();

        return Inertia::render('MasterData/Items/Form', [
            'categories' => Category::where('status', 'ACTIVE')->get(),
            'units' => Unit::where('status', 'ACTIVE')->get(),
            'departments' => Department::where('status', 'ACTIVE')->get(),
            'item' => $item,
        ]);
    }

    public function update(UpdateItemRequest $request, Item $item): RedirectResponse
    {
        $this->itemService->updateItem(
            $item,
            $request->validated(),
            $request->input('department_ids')
        );

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }
}
