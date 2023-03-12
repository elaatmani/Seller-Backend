<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $products = [
            [
                'ref' => 'backpack',
                'name' => 'Backpack',
                'buying_price' => 5,
                'selling_price' => 15,
                'description' => 'Product Backpack',
                'status' => 1
            ],
            [
                'ref' => 'iphone',
                'name' => 'Iphone',
                'buying_price' => 5,
                'selling_price' => 15,
                'description' => 'Product Iphone',
                'status' => 1 
            ]
        ];

        foreach ($products as $product) {
            $productModel = Product::create($product);
            DB::table('product_agentes')->insert([
                [
                    'agente_id' => 2,
                    'product_id' => $productModel->id,
                ],
                [
                    'agente_id' => 2,
                    'product_id' => $productModel->id,
                ],
            ]);
        }
    }
}
