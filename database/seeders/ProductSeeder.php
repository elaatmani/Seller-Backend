<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::create([
            'ref' => 'backpack',
            'name' => 'Backpack',
            'buying_price' => 5,
            'selling_price' => 15,
            'description' => 'Product Backpack',
            'status' => 1
        ]);

    }
}
