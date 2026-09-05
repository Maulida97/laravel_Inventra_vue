<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Departments
        $departments = [
            ['code' => 'IT', 'name' => 'Information Technology', 'description' => 'IT Department'],
            ['code' => 'HR', 'name' => 'Human Resources', 'description' => 'HR Department'],
            ['code' => 'FIN', 'name' => 'Finance', 'description' => 'Finance Department'],
            ['code' => 'OPS', 'name' => 'Operations', 'description' => 'Operations Department'],
            ['code' => 'MKT', 'name' => 'Marketing', 'description' => 'Marketing Department'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }

        // 2. Categories
        $categories = [
            ['code' => 'CAT-ELEC', 'name' => 'Electronics', 'description' => 'Electronic devices and accessories'],
            ['code' => 'CAT-FURN', 'name' => 'Furniture', 'description' => 'Office furniture'],
            ['code' => 'CAT-STAT', 'name' => 'Stationery', 'description' => 'Office stationery'],
            ['code' => 'CAT-SFW', 'name' => 'Software', 'description' => 'Software licenses'],
            ['code' => 'CAT-VEH', 'name' => 'Vehicles', 'description' => 'Company vehicles'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['code' => $cat['code']], $cat);
        }

        // 3. Units
        $units = [
            ['code' => 'PCS', 'name' => 'Pieces', 'description' => 'Individual items'],
            ['code' => 'BOX', 'name' => 'Box', 'description' => 'Box of items'],
            ['code' => 'PACK', 'name' => 'Pack', 'description' => 'Pack of items'],
            ['code' => 'SET', 'name' => 'Set', 'description' => 'Set of items'],
            ['code' => 'UNIT', 'name' => 'Unit', 'description' => 'Single unit'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['code' => $unit['code']], $unit);
        }

        // 4. Items (Sprint 4)
        $itDept = Department::where('code', 'IT')->first();
        $elecCat = Category::where('code', 'CAT-ELEC')->first();
        $furnCat = Category::where('code', 'CAT-FURN')->first();
        $pcsUnit = Unit::where('code', 'PCS')->first();
        $unitUnit = Unit::where('code', 'UNIT')->first();

        $items = [
            [
                'category_id' => $elecCat->id,
                'code' => 'ITM-LPT-001',
                'sku' => 'SKU-LPT-001',
                'barcode' => '8991234567890',
                'name' => 'ThinkPad T14 Gen 3',
                'description' => 'Lenovo ThinkPad T14 Gen 3 Core i7',
                'brand' => 'Lenovo',
                'item_type' => 'asset',
                'base_unit_id' => $unitUnit->id,
                'minimum_stock' => 5,
            ],
            [
                'category_id' => $elecCat->id,
                'code' => 'ITM-MOU-001',
                'sku' => 'SKU-MOU-001',
                'barcode' => '8991234567891',
                'name' => 'Logitech MX Master 3S',
                'description' => 'Wireless Mouse',
                'brand' => 'Logitech',
                'item_type' => 'quantity',
                'base_unit_id' => $pcsUnit->id,
                'minimum_stock' => 10,
            ],
            [
                'category_id' => $elecCat->id,
                'code' => 'ITM-MON-001',
                'sku' => 'SKU-MON-001',
                'barcode' => '8991234567892',
                'name' => 'Dell UltraSharp 27',
                'description' => '27 inch 4K Monitor',
                'brand' => 'Dell',
                'item_type' => 'asset',
                'base_unit_id' => $unitUnit->id,
                'minimum_stock' => 2,
            ],
            [
                'category_id' => $furnCat->id,
                'code' => 'ITM-CHR-001',
                'sku' => 'SKU-CHR-001',
                'barcode' => '8991234567893',
                'name' => 'Ergonomic Office Chair',
                'description' => 'Mesh Ergonomic Chair',
                'brand' => 'Herman Miller',
                'item_type' => 'asset',
                'base_unit_id' => $unitUnit->id,
                'minimum_stock' => 5,
            ],
            [
                'category_id' => $furnCat->id,
                'code' => 'ITM-DSK-001',
                'sku' => 'SKU-DSK-001',
                'barcode' => '8991234567894',
                'name' => 'Standing Desk',
                'description' => 'Motorized Standing Desk',
                'brand' => 'IKEA',
                'item_type' => 'asset',
                'base_unit_id' => $unitUnit->id,
                'minimum_stock' => 3,
            ],
        ];

        foreach ($items as $itemData) {
            $item = Item::firstOrCreate(['code' => $itemData['code']], $itemData);
            
            // Assign some items to IT Department specifically
            if (str_contains($item->code, 'ITM-LPT') || str_contains($item->code, 'ITM-MON')) {
                if (!$item->departments->contains($itDept->id)) {
                    $item->departments()->attach($itDept->id);
                }
            }
        }
    }
}
