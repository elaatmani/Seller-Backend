<?php

namespace App\Services;

use App\Helpers\SteHelper;

use App\Models\City;
use Illuminate\Support\Facades\Http;

class RoadRunnerService
{

    public static function cities() {
        return SteHelper::apiSte([ 'company' => 'Voldo' ], 'getcities/');
    }

    public static function insert($order)
    {
        $city = City::where('name', $order->city)->first();

        if(!$city) return false;

        $data = array(

            "company" => "Voldo",
            "firstName" => $order->fullname,
            "lastName" => "raer",
            "countryPhoneCode" => "961",
            "phoneNumber" => $order->phone,
            "reference_id" => "Voldo-".$order->id,
            "totalLbpPrice" => 10000,
            "totalUsdPrice" => $order->price,
            "orderSize" => 1,
            "zone_id" => $city->roadrunner_zone_id,
            "address" => $order->adresse,
            "note" => $order->note ? $order->note : "No-Note"
        );

        return SteHelper::apiSte($data, 'insert/');
    }
}
