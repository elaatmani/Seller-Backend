<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WarehouseSeeder extends Seeder
{
    public function run()
    {
        Warehouse::create([
            'name' => 'Marrakech'
        ]);
    }
}
