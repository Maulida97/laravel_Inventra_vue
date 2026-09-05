<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run database seeds with 5 dummy warehouses and location hierarchies.
     */
    public function run(): void
    {
        $dummyWarehouses = [
            [
                'code' => 'WH-JKT-01',
                'name' => 'Gudang Utama Jakarta',
                'address' => 'Jl. Industri Raya No. 45, Sunter, Jakarta Utara',
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'WH-SBY-01',
                'name' => 'Gudang Cabang Surabaya',
                'address' => 'Kawasan Industri Rungkut Industri III No. 12, Surabaya',
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'WH-BDG-01',
                'name' => 'Gudang Transit Bandung',
                'address' => 'Jl. Soekarno-Hatta No. 210, Bandung',
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'WH-MED-01',
                'name' => 'Gudang Regional Medan',
                'address' => 'Kawasan Industri Medan (KIM) II, Medan',
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'WH-MKS-01',
                'name' => 'Gudang Distribusi Makassar',
                'address' => 'Kawasan Industri Makassar (KIMA) IX, Makassar',
                'status' => 'ACTIVE',
            ],
        ];

        $items = Item::all();
        $users = User::all();

        foreach ($dummyWarehouses as $index => $whData) {
            $warehouse = Warehouse::firstOrCreate(
                ['code' => $whData['code']],
                $whData
            );

            // Assign users (if any exist)
            if ($users->isNotEmpty()) {
                $warehouse->users()->sync($users->pluck('id')->toArray());
            }

            // Create Hierarchical Locations for this warehouse
            // 1. Root Aisle
            $aisle = Location::firstOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'code' => 'AISLE-0' . ($index + 1),
                ],
                [
                    'name' => 'Lorong Utama ' . ($index + 1),
                    'description' => 'Aisle area utama penyimpan barang',
                    'status' => 'ACTIVE',
                ]
            );

            // 2. Parent Rack under Aisle
            $rack = Location::firstOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'code' => 'RAK-A' . ($index + 1),
                ],
                [
                    'parent_id' => $aisle->id,
                    'name' => 'Rak A Baris ' . ($index + 1),
                    'description' => 'Rak besi bertingkat',
                    'status' => 'ACTIVE',
                ]
            );

            // 3. Child Bin under Rack
            $bin = Location::firstOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'code' => 'BIN-01',
                ],
                [
                    'parent_id' => $rack->id,
                    'name' => 'Kotak Bin 01',
                    'description' => 'Wadah kompartemen barang kecil',
                    'status' => 'ACTIVE',
                ]
            );

            // Bind items with primary location per warehouse (Option B)
            foreach ($items as $item) {
                $warehouse->items()->syncWithoutDetaching([
                    $item->id => ['primary_location_id' => $bin->id]
                ]);
            }
        }
    }
}
