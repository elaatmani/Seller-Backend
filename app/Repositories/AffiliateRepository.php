<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Tag;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductOffer;
use App\Models\TemporaryMedia;
use App\Models\ProductDelivery;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;
use App\Services\RoadRunnerCODSquad;
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
        $query = Product::query()->where('product_type', 'affiliate')->orderBy($sortBy, $sortOrder);

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


    public function store($data) {

        try {
            DB::beginTransaction();
            $product = Product::create([
                'name' => data_get($data, 'name', ''),
                'description' => data_get($data, 'description', ''),
                'buying_price' => data_get($data, 'buying_price', 0),
                'selling_price' => data_get($data, 'selling_price', 0),

                'delivery_rate' => data_get($data, 'delivery_rate', 0),
                'confirmation_rate' => data_get($data, 'confirmation_rate', 0),

                'user_id' => auth()->id(),

                'status' => data_get($data, 'status') == 'true',
                'ref' => data_get($data, 'sku'),
                'category_id' => data_get($data, 'category_id'),
                'product_type' => 'affiliate',
            ]);

            self::storeVariations($product, data_get($data, 'variations', []), data_get($data, 'has_variations', false), data_get($data, 'initial_quantity', 0));
            self::storeTags($product, data_get($data, 'tags', []));
            self::storeMediaFromUUID($product, data_get($data,'media', []));
            self::storeDeliveries($product, data_get($data,'deliveries', []));
            self::storeMetadata($product, data_get($data,'metadata', []));

            DB::commit();

            return $product;
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }


    }

    public static function storeVariations($product, $data, $has_variations, $initial_quantity = 0) {

        $variations = [];
        $warehouse_id = Warehouse::first()?->id;

        if(!$has_variations) {
            ProductVariation::create([
                'product_id' => $product->id,
                'product_ref' => $product->ref,
                'warehouse_id' => $warehouse_id,
                'color' => '',
                'size' => '',
                'quantity' => $initial_quantity,
                'stockAlert' => data_get($data,'stock_alert', 0)
            ]);
            return;
        }
        
        if(count($data) == 0) {
            return;
        };

        foreach($data as $variation) {
            $variation = ProductVariation::create([
                'product_id' => $product->id,
                'product_ref' => $product->ref,
                'warehouse_id' => $warehouse_id,
                'size' => data_get($variation, 'size', ''),
                'color' => data_get($variation, 'color', ''),
                'quantity' => data_get($variation, 'quantity', 0),
                'stockAlert' => data_get($variation, 'stock_alert', 0)
            ]);
            $variations[] = $variation;
        }

        return $variations;
    }

    public static function storeTags($product, $data) {
        foreach($data as $t) {
            $tag = Tag::where('name', $t)->first();
            if(!$tag) {
                $tag = Tag::create([
                    'name' => $t,
                ]);
            }
            $product->tags()->attach($tag);
        }
    }

    public static function storeDeliveries($product, $deliveries = []) {	
        return ProductDelivery::create([
            'product_id' => $product->id,
            'delivery_id' => RoadRunnerCODSquad::ROADRUNNER_ID,
        ]);
    }

    public static function storeMetadata($product, $metadata = []) {	
        foreach ($metadata as $value) {
            $product->metadata()->create([
                'meta_key' => $value['meta_key'],
                'meta_value' => $value['meta_value']
            ]);
        }
    }


    public static function storeMediaFromUUID($product, $media) {

        foreach ($media as $file) {
            $temporaryMedia = TemporaryMedia::where('uuid', data_get($file, 'uuid'))->first();
            $collection_name = data_get($file, 'collection_name', 'default');
            
            if ($temporaryMedia) {
                $mediaItem = $temporaryMedia->getMedia($collection_name)->first();
                if($mediaItem) {
                    $movedMediaItem = $mediaItem->move($product, $collection_name);
                }
            }
        }
    
    }

    public function get($id) {
        return Product::where([
            'product_type' => 'affiliate',
            'id' => $id
        ])->first();
    }

}
