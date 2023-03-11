<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $orders = [
            ['fullname' => 'John Doe',        'product_name' => 'Laptop',        'phone' => '1234567890',        'city' => 'New York',        'adresse' => '123 Main St',        'quantity' => 2,        'note' => 'Please deliver after 5 pm.',],
            ['fullname' => 'Jane Smith',        'product_name' => 'Laptop',        'phone' => '555-1234',        'city' => 'Los Angeles',        'adresse' => '456 Elm St',        'quantity' => 1,        'note' => '',],
            ['fullname' => 'Bob Johnson',        'product_name' => 'T-shirt',        'phone' => '555-5678',        'city' => 'Chicago',        'adresse' => '789 Oak St',        'quantity' => 3,        'note' => 'Please include a gift receipt.',],
            ['fullname' => 'Emily Davis',        'product_name' => 'Sneakers',        'phone' => '555-9012',        'city' => 'San Francisco',        'adresse' => '321 Pine St',        'quantity' => 1,        'note' => 'Please call before delivering.',],
        ];

        Order::insert($orders);
    }
}
