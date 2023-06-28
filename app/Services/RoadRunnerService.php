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
            'company' => 'Voldo',
            "firstName" => $order->fullname,
            "lastName" => "",
            "countryPhoneCode" => "961",
            "phoneNumber" => $order->phone,
            "reference_id" => $order->cmd,
            "totalLbpPrice" => 0,
            "totalUsdPrice" => $order->price,
            "orderSize" => "1",
            "zone_id" => $city->roadrunner_zone_id,
            "address" => $order->adresse,
            "note" => $order->note
        );

        return SteHelper::apiSte($data, 'insert/');
    }
}
