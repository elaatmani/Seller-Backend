<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use App\Services\RoadRunnerService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CityLebanonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $response = RoadRunnerService::cities();
        $cities = collect($response)->map(function($c) {
            return ['roadrunner_zone_id' => $c['zone_id'], 'name' => $c['zone_name'], 'roadrunner_city_id' => $c['city_id']];
        });

        City::insert($cities->values()->toArray());
    }
}
