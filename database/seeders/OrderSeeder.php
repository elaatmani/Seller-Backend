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
            ['fullname' => 'Alice Johnson',  'product_name' => 'Backpack',  'phone' => '555-6789',  'city' => 'Seattle',  'adresse' => '222 Main St',  'quantity' => 2,  'note' => 'Please leave at front door.', 'agente_id' => 2, 'confirmation' => '10day'],
            ['fullname' => 'David Lee',      'product_name' => 'Backpack',   'phone' => '555-2345',  'city' => 'Houston',  'adresse' => '444 Elm St',  'quantity' => 1,  'note' => '', 'agente_id' => 2, 'confirmation' => '10day'],
            ['fullname' => 'Michael Green',  'product_name' => 'Backpack',    'phone' => '555-4567',  'city' => 'Boston',  'adresse' => '888 Pine St',  'quantity' => 1,  'note' => 'Please deliver to back entrance.', 'agente_id' => 2, 'confirmation' => '10day'],
            ['fullname' => 'Maria Perez',    'product_name' => 'Backpack',  'phone' => '555-0123',  'city' => 'Miami',  'adresse' => '999 Oak St',  'quantity' => 2,  'note' => 'Please leave at front desk.', 'agente_id' => 2, 'confirmation' => '10day'],

            ['fullname' => 'Robert Johnson', 'product_name' => 'Backpack', 'phone' => '555-9012', 'city' => 'San Diego', 'adresse' => '321 Oak St', 'quantity' => 1, 'note' => 'Please leave at the front door.', 'agente_id' => 2, 'confirmation' => 'confirme'],
            ['fullname' => 'Olivia Davis', 'product_name' => 'Backpack', 'phone' => '555-7890', 'city' => 'Phoenix', 'adresse' => '555 Pine St', 'quantity' => 2, 'note' => 'Please call before delivering.', 'agente_id' => 2, 'confirmation' => 'confirme'],
            ['fullname' => 'Sophia Garcia', 'product_name' => 'Backpack', 'phone' => '555-1234', 'city' => 'Las Vegas', 'adresse' => '123 Oak St', 'quantity' => 3, 'note' => '', 'agente_id' => 2, 'confirmation' => 'confirme'],
            ['fullname' => 'Ava Kim', 'product_name' => 'Backpack', 'phone' => '555-6789', 'city' => 'Washington D.C.', 'adresse' => '777 Elm St', 'quantity' => 1, 'note' => 'Please deliver to the front desk.', 'agente_id' => 2, 'confirmation' => 'confirme'],

            ['fullname' => 'Daniel Wilson', 'product_name' => 'Backpack', 'phone' => '555-5678', 'city' => 'San Antonio', 'adresse' => '999 Elm St', 'quantity' => 1, 'note' => 'Please deliver after 2 pm.', 'agente_id' => null, 'confirmation' => null],
            ['fullname' => 'Karen Brown',    'product_name' => 'Backpack',  'phone' => '555-8901',  'city' => 'New Orleans',  'adresse' => '777 Oak St',  'quantity' => 3,  'note' => 'Please call 30 minutes before delivery.', 'agente_id' => null, 'confirmation' => null],
            ['fullname' => 'Ethan Brown', 'product_name' => 'Backpack', 'phone' => '555-2345', 'city' => 'Austin', 'adresse' => '444 Pine St', 'quantity' => 2, 'note' => 'Please leave at the back entrance.', 'agente_id' => null, 'confirmation' => null],
            ['fullname' => 'Lauren Kim',     'product_name' => 'Backpack',   'phone' => '555-2345',  'city' => 'Atlanta',  'adresse' => '444 Pine St',  'quantity' => 3,  'note' => 'Please deliver to side entrance.', 'agente_id' => null, 'confirmation' => null],


          
            


        ];

        Order::insert($orders);
    }
}
