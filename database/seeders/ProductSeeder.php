<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'name' => 'Product 1',
            'buying_price' => 5,
            'quantity' => 15,
            'size' => 'Medium',
            'color' =>'Orange',
            'image' => 'product.png',
            'description' => 'Product description',
            'status' => 1
        ]);
    }
}
