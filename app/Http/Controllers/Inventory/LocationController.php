<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreLocationRequest;
use App\Http\Requests\Inventory\UpdateLocationRequest;
use App\Models\Location;
use App\Models\Warehouse;
use App\Services\Inventory\WarehouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Exception;

class LocationController extends Controller
{
    public function __construct(
        private WarehouseService $warehouseService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $warehouses = Warehouse::accessibleByUser($user)->select('id', 'name', 'code')->get();
        $warehouseIds = $warehouses->pluck('id')->toArray();

        $locations = Location::whereIn('warehouse_id', $warehouseIds)
            ->with(['warehouse:id,name,code', 'parent:id,name,code'])
            ->search($request->input('search'))
            ->when($request->input('warehouse_id'), function ($query, $warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Inventory/Locations/Index', [
            'locations' => $locations,
            'warehouses' => $warehouses,
            'filters' => $request->only(['search', 'warehouse_id']),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();
        $warehouses = Warehouse::accessibleByUser($user)->where('status', 'ACTIVE')->get();
        
        $selectedWarehouseId = $request->input('warehouse_id') ?? ($warehouses->first()?->id);
        $parentLocations = $selectedWarehouseId
            ? Location::where('warehouse_id', $selectedWarehouseId)->where('status', 'ACTIVE')->get()
            : collect([]);

        return Inertia::render('Inventory/Locations/Form', [
            'warehouses' => $warehouses,
            'parentLocations' => $parentLocations,
            'location' => new Location([
                'status' => 'ACTIVE',
                'warehouse_id' => $selectedWarehouseId,
            ]),
        ]);
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $this->warehouseService->createLocation($request->validated());

        return redirect()->route('locations.index')->with('success', 'Lokasi rak/bin berhasil dibuat.');
    }

    public function edit(Request $request, Location $location): Response
    {
        $user = $request->user();
        if (!$user->hasWarehouseAccess($location->warehouse_id)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi gudang ini.');
        }

        $warehouses = Warehouse::accessibleByUser($user)->where('status', 'ACTIVE')->get();
        $parentLocations = Location::where('warehouse_id', $location->warehouse_id)
            ->where('id', '!=', $location->id)
            ->where('status', 'ACTIVE')
            ->get();

        return Inertia::render('Inventory/Locations/Form', [
            'warehouses' => $warehouses,
            'parentLocations' => $parentLocations,
            'location' => $location,
        ]);
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $user = $request->user();
        if (!$user->hasWarehouseAccess($location->warehouse_id)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi gudang ini.');
        }

        try {
            $this->warehouseService->updateLocation($location, $request->validated());
            return redirect()->route('locations.index')->with('success', 'Lokasi rak/bin berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, Location $location): RedirectResponse
    {
        $user = $request->user();
        if (!$user->hasWarehouseAccess($location->warehouse_id)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi gudang ini.');
        }

        try {
            $this->warehouseService->deleteLocation($location);
            return redirect()->route('locations.index')->with('success', 'Lokasi rak/bin berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
