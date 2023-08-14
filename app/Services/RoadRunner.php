<?php

namespace App\Services;

use Exception;

use App\Models\City;
use App\Helpers\SteHelper;
use Illuminate\Support\Facades\Http;

class RoadRunner
{

    const NOTIFY_ROADRUNNER = false;
    const ROADRUNNER_ID = 4;
    const TEST_URL = "https://systemtunes.com/apivoldo/";
    const LIVE_URL = "https://roadrunner-lb.com/api/vooldo/";
    const TEST_MODE = true;


    public static function sync(&$order) {
        if(!self::NOTIFY_ROADRUNNER) return true;

        $oldAttributes = $order->getOriginal(); // Old values
        $newAttributes = $order->getAttributes(); // New values
        // $order->note = 'from observer';
        // throw new Exception('hello from sync');
        if(
            $oldAttributes['affectation'] != self::ROADRUNNER_ID
            && $newAttributes['affectation'] == self::ROADRUNNER_ID
            && $newAttributes['confirmation'] == "confirmer"
        ) {
            $response = self::insert($order);

            if($response['success'] && $response['code'] == 200) return true;
            throw new Exception(json_encode($response['response']));

        }

        if(
            $oldAttributes['affectation'] == self::ROADRUNNER_ID
            && $newAttributes['affectation'] != self::ROADRUNNER_ID
        ) {
            $response = self::delete($order['id']);

            if($response['success'] && $response['code'] == 200) return true;
            throw new Exception(json_encode($response['response']));
        }

        return false;
    }

    public static function http($endpoint, $params) {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('API_STE', ''),
            'X-Access-Token' => 'oHD-w3=GD3sKBZcLF]CMb#!rjj)Azs;-?wnZsqf43:0WlQO:8U%&-Y-dpzCgua5HJT?tyxJ={Q{+9hJ[dN?|t?-tZ7F[J1'
            ])->post(self::endpoint($endpoint), $params);

        if ($response->successful()) {
            // Order created successfully.
            $responseData = [
                'success' => true,
                'response' => $response->json(),
                'code' => $response->status()
            ];

            return $responseData;
        }

        return [
            'success' => false,
            'response' => $response->json(),
            'code' => $response->status()
        ];
    }


    public static function endpoint($path) {
        if(self::TEST_MODE) return self::TEST_URL . $path;
        return self::LIVE_URL . $path;
    }

    public static function decodeId($reference_id) {
        $id = substr($reference_id, 3);
        // $id = $idBefore - 2000;
        $prefix = strtolower(substr($reference_id, 0, 3));

        if($prefix == 'vld' && is_numeric($id)) {
            return $id;
        }

        return null;
    }

    public static function encodeId($id) {
        return "vld".$id;
    }

    public static function cities() {
        return self::http('getcities/', [ 'company' => 'Voldo' ]);
    }


    public static function orders() {
        return self::http('list/', [ 'company' => 'Voldo', 'daterange' => '2023/07/20 - 2023/08/01' ]);
    }

    public static function rates() {
        return self::http('getrates/', [ 'company' => 'Voldo' ]);
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
            "reference_id" => self::encodeId($order->id),
            "totalLbpPrice" => 0,
            "totalUsdPrice" => self::getPrice($order),
            "orderSize" => 5,
            "zone_id" => $city->roadrunner_zone_id,
            "address" => $order->adresse,
            "note" => self::formatProductString($order)
        );

        return self::http('insert/', $data);
    }

    public static function delete($id)
    {
        $data = array(
            "reference_id" => self::encodeId($id)
        );

        return self::http('delete/', $data);
    }

    public static function getPrice($order) {
        if (!$order) return 0;
        $total = array_reduce($order['items']->values()->toArray(), function($sum, $item) {
            return $sum + (!$item['price'] ? 0 : $item['price']);
        }, 0);
            return floatval(!$order['price'] ? 0 : $order['price']) + floatval($total);
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

                $result .= "[product=\"$productName\";quantity=$quantity;variation=$variationSize/$variationColor]";
            }

            $result = rtrim($result, ', '); // Remove the trailing comma and space
        }

        return $result;
    }

}
