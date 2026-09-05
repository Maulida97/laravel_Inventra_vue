<?php

namespace App\Services\Inventory;

use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service: WarehouseService
 * Domain: Inventory / Warehouse & Location Management
 *
 * Responsibility:
 * Menangani logika bisnis gudang dan hierarki lokasi fisik (rack/bin),
 * serta penugasan user ke gudang.
 */
class WarehouseService
{
    /**
     * Membuat Gudang baru beserta user scope yang ditugaskan.
     */
    public function createWarehouse(array $data, array $userIds = []): Warehouse
    {
        return DB::transaction(function () use ($data, $userIds) {
            $warehouse = Warehouse::create($data);

            if (!empty($userIds)) {
                $warehouse->users()->sync($userIds);
            }

            return $warehouse;
        });
    }

    /**
     * Memperbarui data Gudang dan penugasan user.
     */
    public function updateWarehouse(Warehouse $warehouse, array $data, ?array $userIds = null): Warehouse
    {
        return DB::transaction(function () use ($warehouse, $data, $userIds) {
            $warehouse->update($data);

            if ($userIds !== null) {
                $warehouse->users()->sync($userIds);
            }

            return $warehouse;
        });
    }

    /**
     * Menghapus Gudang (Memastikan tidak ada lokasi aktif di dalamnya).
     */
    public function deleteWarehouse(Warehouse $warehouse): bool
    {
        if ($warehouse->locations()->exists()) {
            throw new InvalidArgumentException('Gudang tidak dapat dihapus karena masih memiliki lokasi rak/bin.');
        }

        return DB::transaction(function () use ($warehouse) {
            $warehouse->users()->detach();
            return $warehouse->delete();
        });
    }

    /**
     * Membuat Lokasi baru dalam gudang.
     */
    public function createLocation(array $data): Location
    {
        if (!empty($data['parent_id'])) {
            $parent = Location::findOrFail($data['parent_id']);
            if ($parent->warehouse_id != $data['warehouse_id']) {
                throw new InvalidArgumentException('Parent location harus berada di dalam gudang yang sama.');
            }
        }

        return Location::create($data);
    }

    /**
     * Memperbarui Lokasi dan memvalidasi hirarki agar tidak terjadi circular reference.
     */
    public function updateLocation(Location $location, array $data): Location
    {
        if (!empty($data['parent_id'])) {
            if ($data['parent_id'] == $location->id) {
                throw new InvalidArgumentException('Lokasi tidak boleh menjadi parent untuk dirinya sendiri.');
            }

            $parent = Location::findOrFail($data['parent_id']);
            if ($parent->warehouse_id != ($data['warehouse_id'] ?? $location->warehouse_id)) {
                throw new InvalidArgumentException('Parent location harus berada di dalam gudang yang sama.');
            }

            // Check circular reference (parent is child of location)
            if ($this->isDescendant($location, $parent->id)) {
                throw new InvalidArgumentException('Circular hierarchy terdeteksi: Parent tidak boleh merupakan turunan dari lokasi ini.');
            }
        }

        $location->update($data);
        return $location;
    }

    /**
     * Menghapus Lokasi (Memastikan tidak memiliki sub-lokasi).
     */
    public function deleteLocation(Location $location): bool
    {
        if ($location->children()->exists()) {
            throw new InvalidArgumentException('Lokasi tidak dapat dihapus karena memiliki sub-lokasi.');
        }

        return $location->delete();
    }

    /**
     * Helper privat untuk mengecek apakah targetId merupakan turunan dari location.
     */
    private function isDescendant(Location $location, int $targetId): bool
    {
        $children = $location->children()->get();
        foreach ($children as $child) {
            if ($child->id === $targetId || $this->isDescendant($child, $targetId)) {
                return true;
            }
        }
        return false;
    }
}
