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
            ['fullname' => 'Alice Johnson',  'product_name' => 'Backpack',  'phone' => '555-6789',  'city' => 'Seattle',  'price' => 200, 'adresse' => '222 Main St',  'quantity' => 2],
            ['fullname' => 'David Lee',      'product_name' => 'Backpack',   'phone' => '555-2345',  'price' => 200 ,'city' => 'Houston',  'adresse' => '444 Elm St',  'quantity' => 1],
            ['fullname' => 'Michael Green',  'product_name' => 'Backpack',    'phone' => '555-4567',  'price' => 200 ,'city' => 'Boston',  'adresse' => '888 Pine St',  'quantity' => 1],
            ['fullname' => 'Maria Perez',    'product_name' => 'Backpack',  'phone' => '555-0123',  'price' => 200 ,'city' => 'Miami',  'adresse' => '999 Oak St',  'quantity' => 2],

            ['fullname' => 'Robert Johnson', 'product_name' => 'Backpack', 'phone' => '555-9012', 'price' => 200 ,'city' => 'San Diego', 'adresse' => '321 Oak St', 'quantity' => 1],
            ['fullname' => 'Olivia Davis', 'product_name' => 'Backpack', 'phone' => '555-7890', 'price' => 200 ,'city' => 'Phoenix', 'adresse' => '555 Pine St', 'quantity' => 2],
            ['fullname' => 'Sophia Garcia', 'product_name' => 'Iphone', 'phone' => '555-1234', 'price' => 200 ,'city' => 'Las Vegas', 'adresse' => '123 Oak St', 'quantity' => 3],
            ['fullname' => 'Ava Kim', 'product_name' => 'Iphone', 'phone' => '555-6789', 'price' => 200 ,'city' => 'Washington D.C.', 'adresse' => '777 Elm St', 'quantity' => 1],

            ['fullname' => 'Daniel Wilson', 'product_name' => 'Iphone', 'phone' => '555-5678', 'price' => 200 ,'city' => 'San Antonio', 'adresse' => '999 Elm St', 'quantity' => 1],
            ['fullname' => 'Karen Brown',    'product_name' => 'Iphone',  'phone' => '555-8901',  'price' => 200 ,'city' => 'New Orleans',  'adresse' => '777 Oak St',  'quantity' => 3],
            ['fullname' => 'Ethan Brown', 'product_name' => 'Iphone', 'phone' => '555-2345', 'price' => 200 ,'city' => 'Austin', 'adresse' => '444 Pine St', 'quantity' => 2],
            ['fullname' => 'Lauren Kim',     'product_name' => 'Iphone',   'phone' => '555-2345',  'price' => 200 ,'city' => 'Atlanta',  'adresse' => '444 Pine St',  'quantity' => 3],
        ];
        foreach($orders as $order){
            Order::create($order);
        }
        
    }
}
