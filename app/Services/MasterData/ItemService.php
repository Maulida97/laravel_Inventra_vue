<?php

namespace App\Services\MasterData;

use App\Models\Item;
use Illuminate\Support\Facades\DB;

/**
 * Service: ItemService
 * Domain: Master Data / Inventory
 *
 * Responsibility:
 * Menangani business operation terkait entitas Item, seperti penciptaan
 * dan pembaruan item beserta relasi (department_item) dalam satu transaction.
 */
class ItemService
{
    /**
     * Membuat Item baru beserta alokasi departemennya.
     *
     * @param array $data
     * @param array|null $departmentIds
     * @return Item
     * @throws \Exception
     */
    public function createItem(array $data, ?array $departmentIds = null): Item
    {
        return DB::transaction(function () use ($data, $departmentIds) {
            $item = Item::create($data);

            if (!empty($departmentIds)) {
                $item->departments()->sync($departmentIds);
            }

            return $item;
        });
    }

    /**
     * Memperbarui data Item dan sinkronisasi departemennya.
     *
     * @param Item $item
     * @param array $data
     * @param array|null $departmentIds
     * @return Item
     * @throws \Exception
     */
    public function updateItem(Item $item, array $data, ?array $departmentIds = null): Item
    {
        return DB::transaction(function () use ($item, $data, $departmentIds) {
            $item->update($data);

            if ($departmentIds !== null) {
                $item->departments()->sync($departmentIds);
            } else {
                $item->departments()->sync([]);
            }

            return $item;
        });
    }
}
