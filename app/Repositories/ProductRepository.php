<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductOffer;
use Illuminate\Support\Carbon;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository  implements ProductRepositoryInterface
{

    public static function productsForOrder($id = null)
    {
        return  Product::when(
            $id != null
                && !auth()->user()->hasRole('admin')
                && !auth()->user()->hasRole('agente'),
            function ($q) use ($id) {
                $q->where('user_id', $id)
                    ->orWhere(function ($query) use ($id) {
                        $query->whereIn('id', function ($subQuery) use ($id) {
                            $subQuery->select('product_id')
                                ->from('user_product')
                                ->where('user_id', $id);
                        });
                    });
            }
        )->get()->map->formatForOrder();
    }

    public function all($options = [])
    {
        $query = Product::query();


        $query->where(function ($q) use ($options) {
            foreach (data_get($options, 'orWhere', []) as $w) {
                $q->orWhere($w[0], $w[1], $w[2]);
            }
        });

        if (data_get($options, 'query', null)) {
            $query->where(data_get($options, 'query'));
        }
        foreach (data_get($options, 'whereDate', []) as $wd) {
            if (!$wd[2]) continue;
            $query->whereDate($wd[0], $wd[1], Carbon::make($wd[2])->toDate());
        }

        foreach (data_get($options, 'orderBy', []) as $wd) {
            $query->orderBy($wd[0], $wd[1]);
        }

        foreach (data_get($options, 'where', []) as $w) {
            $query->where($w[0], $w[1], $w[2]);
        }

        if ($with = data_get($options, 'with', [])) {
            $query->with($with);
        }

        foreach (data_get($options, 'whereHas', []) as $w) {

            $query->when($w[2] != 'all', fn ($q) => $q->whereHas($w[3], fn ($oq) => $oq->where($w[0], $w[1], $w[2])));
        }


        if (data_get($options, 'get', true)) {
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

    public static function createProductOffers($id, $offers)
    {

        foreach ($offers as $offer) {
            $offer = ProductOffer::create([
                'product_id' => $id,
                'quantity' => $offer['quantity'],
                'price' => $offer['price'],
                'note' => $offer['note'],
            ]);
        }

        return ProductOffer::where('product_id', $id)->get();
    }


    public static function updateProductOffers($id, $offers)
    {

        // find the deleted offer ids
        $old_offers = ProductOffer::where('product_id', $id)->get();
        $old_ids = $old_offers->pluck('id')->values()->toArray();
        $current_ids = array_map(fn ($o) => $o['id'], $offers);
        $to_delete = array_diff($old_ids, $current_ids);

        // handle deleted offers
        ProductOffer::where('product_id', $id)->whereIn('id', $to_delete)->delete();


        // create or update new offers
        foreach ($offers as $offer) {
            ProductOffer::updateOrCreate(
                [
                    'id' => $offer['id'],
                    'product_id' => $id,
                ],
                [
                    'quantity' => $offer['quantity'],
                    'price' => $offer['price'],
                    'note' => $offer['note'],
                    'user_id' => auth()->id()
                ]
            );
        }
    }

}
