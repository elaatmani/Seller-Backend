<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $inventoryMovement = [
            ['product_id'=>1,'delivery_id'=>3],
            ['product_id'=>2,'delivery_id'=>4]
        ];
        foreach($inventoryMovement as $movement){
            InventoryMovement::create($movement);
        }
    }
}
