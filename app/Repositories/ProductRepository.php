<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductOffer;

class ProductRepository {

    public static function productsForOrder($id = null) {
        return  Product::when($id !=null && !auth()->user()->hasRole('admin'),fn($q) => $q->where('user_id', $id))->get()->map->formatForOrder();
    }


    public static function createProductOffers($id, $offers) {

        foreach($offers as $offer) {
            $offer = ProductOffer::create([
                'product_id' => $id,
                'quantity' => $offer['quantity'],
                'price' => $offer['price'],
                'note' => $offer['note'],
            ]);
        }

        return ProductOffer::where('product_id', $id)->get();
    }


    public static function updateProductOffers($id, $offers) {

        // find the deleted offer ids
        $old_offers = ProductOffer::where('product_id', $id)->get();
        $old_ids = $old_offers->pluck('id')->values()->toArray();
        $current_ids = array_map(fn($o) => $o['id'], $offers);
        $to_delete = array_diff($old_ids, $current_ids);

        // handle deleted offers
        ProductOffer::where('product_id', $id)->whereIn('id', $to_delete)->delete();


        // create or update new offers
        foreach($offers as $offer) {
            ProductOffer::updateOrCreate(
                [
                    'id' => $offer['id'],
                    'product_id' => $id,
                ],
                [
                    'quantity' => $offer['quantity'],
                    'price' => $offer['price'],
                    'note' => $offer['note']
                ]
            );
        }
    }

}
