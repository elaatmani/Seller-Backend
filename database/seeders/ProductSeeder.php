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
            'ref' => 'Test',
            'name' => 'Test',
            'buying_price' => 5,
            'selling_price' => 15,
            'description' => 'Product description',
            'status' => 1
        ]);

    }
}
