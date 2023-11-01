<?php

namespace App\Helpers;

class OrderHelper {
    public static function getPrice($order) {
        if (!$order) return 0;
        $total = array_reduce($order['items']->values()->toArray(), function($sum, $item) {
            return $sum + (!$item['price'] ? 0 : $item['price']);
        }, 0);
            return round(floatval(!$order['price'] ? 0 : $order['price']) + floatval($total), 2);

    }
}
