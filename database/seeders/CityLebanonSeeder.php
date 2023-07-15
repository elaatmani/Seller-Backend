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
        // $response = RoadRunnerService::cities();
        // $cities = collect($response)->map(function($c) {
        //     return ['roadrunner_zone_id' => $c['zone_id'], 'name' => $c['zone_name'], 'roadrunner_city_id' => $c['city_id']];
        // });
        // City::insert($cities->values()->toArray());

        $cities = array(
            array("roadrunner_zone_id"=>"1","name"=>"Inside Beirut (From shwaifat - dahye - hazmieh - sinelfil- dekwaneh - dawra - hamra - ras beirut)"),
            array("roadrunner_zone_id"=>"2","name"=>"Bekaa Bekaa"),
            array("roadrunner_zone_id"=>"3","name"=>"Saida Saida"),
            array("roadrunner_zone_id"=>"4","name"=>"Tripoli Tripoli"),
            array("roadrunner_zone_id"=>"5","name"=>"Outside Beirut (From Antilias To Jounieh -armoun -khalde Baabda- Alay)")
        );
        City::insert($cities);

    }
}
