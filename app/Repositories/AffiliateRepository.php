<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductOffer;
use App\Repositories\Interfaces\AffiliateRepositoryInterface;

class AffiliateRepository  implements AffiliateRepositoryInterface {

    public static function productsForOrder($id = null) {
        return  Product::when($id !=null && !auth()->user()->hasRole('admin') && !auth()->user()->hasRole('agente'),fn($q) => $q->where('user_id', $id))->get()->map->formatForOrder();
    }

    public function all($options = []) {
        $query = Product::query();


        $query->where(function($q) use($options){

            foreach(data_get($options, 'orWhere', []) as $w) {
                    $q->orWhere($w[0], $w[1], $w[2]);
            }
        });

        foreach(data_get($options, 'whereDate', []) as $wd) {
            if(!$wd[2]) continue;
            $query->whereDate($wd[0], $wd[1], Carbon::make($wd[2])->toDate());
        }

        foreach(data_get($options, 'orderBy', []) as $wd) {
            $query->orderBy($wd[0], $wd[1]);
        }

        foreach(data_get($options, 'where', []) as $w ) {
            $query->where($w[0], $w[1], $w[2]);
        }

        if($with = data_get($options, 'with', [])) {
            $query->with($with);
        }

        foreach(data_get($options, 'whereHas', []) as $w) {

            $query->when($w[2] != 'all', fn($q) => $q->whereHas($w[3], fn($oq) => $oq->where($w[0], $w[1], $w[2])));
        }


        if(data_get($options, 'get', true)) {
            return $query->get()->map->formatForShow();
        }
        return $query;

    }


    public function paginate(
        int $perPage = 10,
        string $sortBy = 'created_at',
        string $sortOrder = 'desc',
        array $options = []
    ) {
         // Number of records per page
        $options['get'] = false;

        // Get the query builder instance for the 'users' table
        $query = $this->all($options);

        $validSortOrders = ['asc', 'desc'];

        if (!in_array($sortOrder, $validSortOrders)) {
            $sortOrder = 'desc'; // Set default if the provided sort order is invalid
        }

        // Apply the sorting to the query
        $query->orderBy($sortBy, $sortOrder);

        // $query->where('agente_id', 18);

        // Retrieve the paginated results
        $products = $query->paginate($perPage);

        return $products;
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

    }

}
