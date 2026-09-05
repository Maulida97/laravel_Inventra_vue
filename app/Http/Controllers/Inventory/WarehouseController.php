<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreWarehouseRequest;
use App\Http\Requests\Inventory\UpdateWarehouseRequest;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Inventory\WarehouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Exception;

class WarehouseController extends Controller
{
    public function __construct(
        private WarehouseService $warehouseService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $warehouses = Warehouse::accessibleByUser($user)
            ->with(['users:id,name,email'])
            ->withCount('locations')
            ->search($request->input('search'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Inventory/Warehouses/Index', [
            'warehouses' => $warehouses,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        $users = User::select('id', 'name', 'email')->get();

        return Inertia::render('Inventory/Warehouses/Form', [
            'users' => $users,
            'warehouse' => new Warehouse(['status' => 'ACTIVE']),
        ]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $this->warehouseService->createWarehouse(
            $request->validated(),
            $request->input('user_ids', [])
        );

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dibuat.');
    }

    public function edit(Request $request, Warehouse $warehouse): Response
    {
        if (!$request->user()->hasWarehouseAccess($warehouse)) {
            abort(403, 'Anda tidak memiliki akses ke gudang ini.');
        }

        $warehouse->load('users:id,name,email');
        $warehouse->user_ids = $warehouse->users->pluck('id')->toArray();
        $users = User::select('id', 'name', 'email')->get();

        return Inertia::render('Inventory/Warehouses/Form', [
            'users' => $users,
            'warehouse' => $warehouse,
        ]);
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        if (!$request->user()->hasWarehouseAccess($warehouse)) {
            abort(403, 'Anda tidak memiliki akses ke gudang ini.');
        }

        $this->warehouseService->updateWarehouse(
            $warehouse,
            $request->validated(),
            $request->input('user_ids')
        );

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Request $request, Warehouse $warehouse): RedirectResponse
    {
        if (!$request->user()->hasWarehouseAccess($warehouse)) {
            abort(403, 'Anda tidak memiliki akses ke gudang ini.');
        }

        try {
            $this->warehouseService->deleteWarehouse($warehouse);
            return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
