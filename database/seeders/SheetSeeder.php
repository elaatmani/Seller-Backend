<?php

namespace Database\Seeders;

use App\Models\Sheet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SheetSeeder extends Seeder
{
    public function run()
    {

        $sheet =
            [
                'name' => 'Youcan Orders',
                'sheet_id' => '1RBisw6xN9ifUYHoQImqKE5B0h2j6JDlDUkyeAiCJSmE',
                'sheet_name' => 'Sheet1'

            ];
        Sheet::create($sheet);
    }
}
