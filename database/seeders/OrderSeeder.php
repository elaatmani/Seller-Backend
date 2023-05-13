<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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
            ['fullname' => 'Alice Johnson',   'phone' => '555-6789',  'city' => 'Seattle',  'price' => 200, 'adresse' => '222 Main St'],
            ['fullname' => 'David Lee',        'phone' => '555-2345',  'price' => 200 ,'city' => 'Houston',  'adresse' => '444 Elm St'],
            ['fullname' => 'Michael Green',     'phone' => '555-4567',  'price' => 200 ,'city' => 'Boston',  'adresse' => '888 Pine St'],
            ['fullname' => 'Maria Perez',     'phone' => '555-0123',  'price' => 200 ,'city' => 'Miami',  'adresse' => '999 Oak St'],

            ['fullname' => 'Robert Johnson', 'phone' => '555-9012', 'price' => 200 ,'city' => 'San Diego', 'adresse' => '321 Oak St'],
            ['fullname' => 'Olivia Davis', 'phone' => '555-7890', 'price' => 200 ,'city' => 'Phoenix', 'adresse' => '555 Pine St'],
            ['fullname' => 'Sophia Garcia', 'phone' => '555-1234', 'price' => 200 ,'city' => 'Las Vegas', 'adresse' => '123 Oak St'],
            ['fullname' => 'Ava Kim', 'phone' => '555-6789', 'price' => 200 ,'city' => 'Washington D.C.', 'adresse' => '777 Elm St'],

            ['fullname' => 'Daniel Wilson', 'phone' => '555-5678', 'price' => 200 ,'city' => 'San Antonio', 'adresse' => '999 Elm St'],
            ['fullname' => 'Karen Brown',  'phone' => '555-8901',  'price' => 200 ,'city' => 'New Orleans',  'adresse' => '777 Oak St'],
            ['fullname' => 'Ethan Brown', 'phone' => '555-2345', 'price' => 200 ,'city' => 'Austin', 'adresse' => '444 Pine St'],
            ['fullname' => 'Lauren Kim', 'phone' => '555-2345',  'price' => 200 ,'city' => 'Atlanta',  'adresse' => '444 Pine St'],
        ];
        foreach($orders as $order){
            $o = Order::create($order);
            $p = Product::inRandomOrder()->first();
            $v = $p->variations;

            for($i = 0; $i <= rand(0, $v->count() - 1); $i++) {

                OrderItem::create([
                    'order_id' => $o->id,
                    'product_id' => $p->id,
                    'product_ref' => $p->ref,
                    'product_variation_id' => $v->get($i)->id,
                    'quantity' => rand(1, $v->get($i)->quantity)
                ]);
            }
        }

    }
}
