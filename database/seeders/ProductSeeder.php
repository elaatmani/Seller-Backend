<?php

namespace Database\Seeders;

use App\Models\InventoryState;
use App\Models\Product;
use App\Models\ProductVariation;
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
                'name' => 'Product 1',
                'ref' => 'P001',
                'selling_price' => 10.0,
                'buying_price' => 5.0,
                'description' => 'Description for product 1',
                'variations' => [
                    [
                        'product_id' => 1,
                        'product_ref' => 'P001',
                        'quantity' => 100,
                        'warehouse_id' => 1,
                        'size' => 'M',
                        'color' => 'Red',
                        'stockAlert' => 50,
                    ],
                    [
                        'product_id' => 1,
                        'product_ref' => 'P001',
                        'quantity' => 50,
                        'warehouse_id' => 1,
                        'size' => 'L',
                        'color' => 'Blue',
                        'stockAlert' => 20,
                    ],
                    [
                        'product_id' => 1,
                        'product_ref' => 'P001',
                        'quantity' => 80,
                        'warehouse_id' => 1,
                        'size' => 'S',
                        'color' => 'Green',
                        'stockAlert' => 40,
                    ]
                ]
            ],
            [
                'name' => 'Product 2',
                'ref' => 'P002',
                'selling_price' => 20.0,
                'buying_price' => 10.0,
                'description' => 'Description for product 2',
                'variations' => [
                    [
                        'product_id' => 2,
                        'product_ref' => 'P002',
                        'quantity' => 50,
                        'warehouse_id' => 1,
                        'size' => 'S',
                        'color' => 'Black',
                        'stockAlert' => 25,
                    ],
                    [
                        'product_id' => 2,
                        'product_ref' => 'P002',
                        'quantity' => 80,
                        'warehouse_id' => 1,
                        'size' => 'M',
                        'color' => 'White',
                        'stockAlert' => 40,
                    ],
                    [
                        'product_id' => 2,
                        'product_ref' => 'P002',
                        'quantity' => 30,
                        'warehouse_id' => 1,
                        'size' => 'L',
                        'color' => 'Red',
                        'stockAlert' => 15,
                    ],
                    [
                        'product_id' => 2,
                        'product_ref' => 'P002',
                        'quantity' => 60,
                        'warehouse_id' => 1,
                        'size' => 'XL',
                        'color' => 'Blue',
                        'stockAlert' => 30,
                    ]
                ]
                    ],
                    [
                        'name' => 'Product 3',
                        'ref' => 'P003',
                        'selling_price' => 30.0,
                        'buying_price' => 10.0,
                        'description' => 'Description for product 3',
                        'variations' => [
                            [
                                'product_id' => 3,
                                'product_ref' => 'P003',
                                'quantity' => 50,
                                'warehouse_id' => 1,
                                'size' => 'S',
                                'color' => 'Black',
                                'stockAlert' => 35,
                            ],
                            [
                                'product_id' => 3,
                                'product_ref' => 'P003',
                                'quantity' => 80,
                                'warehouse_id' => 1,
                                'size' => 'M',
                                'color' => 'White',
                                'stockAlert' => 40,
                            ],
                            [
                                'product_id' => 3,
                                'product_ref' => 'P003',
                                'quantity' => 30,
                                'warehouse_id' => 1,
                                'size' => 'L',
                                'color' => 'Red',
                                'stockAlert' => 15,
                            ],
                            [
                                'product_id' => 3,
                                'product_ref' => 'P003',
                                'quantity' => 60,
                                'warehouse_id' => 1,
                                'size' => 'XL',
                                'color' => 'Blue',
                                'stockAlert' => 30,
                            ]
                        ]
                    ]
        ];

        foreach ($products as $product) {

            $p = Product::create([
                'name' => $product['name'],
                'ref' => $product['ref'],
                'selling_price' => $product['selling_price'],
                'buying_price' => $product['buying_price'],
                'description' => $product['description'],
            ]);

            foreach ($product['variations'] as $v) {
                ProductVariation::create([
                    'product_id' => $v['product_id'],
                    'product_ref' => $v['product_ref'],
                    'quantity' => $v['quantity'],
                    'warehouse_id' => $v['warehouse_id'],
                    'size' => $v['size'],
                    'color' => $v['color'],
                    'stockAlert' => $v['stockAlert'],
                ]);
            }
        }
    }
}
