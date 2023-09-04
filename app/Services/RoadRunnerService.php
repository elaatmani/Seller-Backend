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

    public static function orders() {
        return SteHelper::apiSte([ 'company' => 'Voldo', 'daterange' => '2023-07-20 2023-09-04' ], 'list/');
    }

    public static function rates() {
        return SteHelper::apiSte([ 'company' => 'Voldo' ], 'getrates/');
    }

    public static function insert($order)
    {
        $city = City::where('name', $order->city)->first();
        if(!$city) return false;

        $data = array(

            "company" => "Voldo",
            "firstName" => $order->fullname,
            "lastName" => "-",
            "countryPhoneCode" => "961",
            "phoneNumber" => $order->phone,
            "reference_id" => "vld".$order->id,
            "totalLbpPrice" => 0,
            "totalUsdPrice" => self::getPrice($order),
            "orderSize" => 5,
            "zone_id" => $city->roadrunner_zone_id,
            "address" => $order->adresse,
            "note" => self::formatProductString($order)
        );

        return SteHelper::apiSte($data, 'insert/');
    }

    public static function delete($id)
    {
        $data = array(
            "reference_id" => "vld".$id
        );

        return SteHelper::apiSte($data, 'delete/');
    }

    public static function getPrice($order) {
        if (!$order) return 0;
        $total = array_reduce($order['items']->values()->toArray(), function($sum, $item) {
            return $sum + (!$item['price'] ? 0 : $item['price']);
        }, 0);
            return round(floatval(!$order['price'] ? 0 : $order['price']) + floatval($total), 2);
    }


    public static function formatProductString($order) {
        $order = json_decode($order, true);

        $result = '';

        if (!empty($order['items'])) {
            foreach ($order['items'] as $item) {
                $productName = isset($item['product']['name']) ? $item['product']['name'] : 'Unknown Product';
                $quantity = isset($item['quantity']) ? $item['quantity'] : 0;
                $variationSize = isset($item['product_variation']['size']) ? $item['product_variation']['size'] : '';
                $variationColor = isset($item['product_variation']['color']) ? $item['product_variation']['color'] : '';

                $result .= "[product=\"$productName\";quantity=\"$quantity\";variation=\"$variationSize $variationColor\"],]";
            }

            $note = $order['note'];
            $result .= " [NOTE=\" $note \"]";

            $result = rtrim($result, ', '); // Remove the trailing comma and space
        }

        return $result;
    }

}
