<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Tag;
use App\Models\User;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductOffer;
use App\Models\ProductAgente;
use App\Models\TemporaryMedia;
use App\Models\ProductDelivery;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;
use App\Services\RoadRunnerCODSquad;
use App\Repositories\Interfaces\AffiliateRepositoryInterface;

class AffiliateRepository  implements AffiliateRepositoryInterface
{

    public static function productsForOrder($id = null)
    {
        return  Product::when($id != null && !auth()->user()->hasRole('admin') && !auth()->user()->hasRole('agente'), fn ($q) => $q->where('user_id', $id))->get()->map->formatForOrder();
    }

    public function all($options = [])
    {
        $query = Product::query();


        $query->where(function ($q) use ($options) {

            foreach (data_get($options, 'orWhere', []) as $w) {
                $q->orWhere($w[0], $w[1], $w[2]);
            }
        });

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
        $query = Product::query()
        ->where('product_type', 'affiliate')
        ->where('status', 1)
        ->orderBy($sortBy, $sortOrder);

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


    public function store($data)
    {

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

                'status' => data_get($data, 'status'),
                'ref' => data_get($data, 'sku'),
                'category_id' => data_get($data, 'category_id'),
                'product_type' => 'affiliate',
                'video_url' => '...',
                'store_url' => '...',
            ]);

            self::storeVariations($product, data_get($data, 'variations', []), data_get($data, 'has_variations', false), data_get($data, 'initial_quantity', 0));
            self::storeTags($product, data_get($data, 'tags', []));
            self::storeMediaFromUUID($product, data_get($data, 'media', []));
            self::storeDeliveries($product, data_get($data, 'deliveries', []));
            self::storeMetadata($product, data_get($data, 'metadata', []));

            $users = User::where('having_all',1)->get('id');
            if($users){
                foreach($users as $user){
                    ProductAgente::create([
                        'agente_id'=> $user->id,
                        'product_id' => $product->id
                    ]);
                }
            }

            DB::commit();

            return $product;
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }

    public static function storeVariations($product, $data, $has_variations, $initial_quantity = 0)
    {

        $variations = [];
        $warehouse_id = Warehouse::first()?->id;

        if (!$has_variations) {
            ProductVariation::create([
                'product_id' => $product->id,
                'product_ref' => $product->ref,
                'warehouse_id' => $warehouse_id,
                'color' => '',
                'size' => '',
                'quantity' => $initial_quantity,
                'stockAlert' => data_get($data, 'stock_alert', 0)
            ]);
            return;
        }

        if (count($data) == 0) {
            return;
        };

        foreach ($data as $variation) {
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

    public static function storeTags($product, $data)
    {
        foreach ($data as $t) {
            $tag = Tag::where('name', $t)->first();
            if (!$tag) {
                $tag = Tag::create([
                    'name' => $t,
                ]);
            }
            $product->tags()->attach($tag);
        }
    }

    public static function storeDeliveries($product, $deliveries = [])
    {
        return ProductDelivery::create([
            'product_id' => $product->id,
            'delivery_id' => RoadRunnerCODSquad::ROADRUNNER_ID,
        ]);
    }

    public static function storeMetadata($product, $metadata = [])
    {
        foreach ($metadata as $value) {
            $product->metadata()->create([
                'meta_key' => $value['meta_key'],
                'meta_value' => $value['meta_value']
            ]);
        }
    }


    public static function storeMediaFromUUID($product, $media)
    {

        foreach ($media as $file) {
            $temporaryMedia = TemporaryMedia::where('uuid', data_get($file, 'uuid'))->first();
            $collection_name = data_get($file, 'collection_name', 'default');

            if ($temporaryMedia) {
                $mediaItem = $temporaryMedia->getMedia($collection_name)->first();
                if ($mediaItem) {
                    $movedMediaItem = $mediaItem->move($product, $collection_name);
                }
            }
        }
    }

    public function get($id)
    {
        return Product::where([
            'product_type' => 'affiliate',
            'id' => $id
        ])->first();
    }

    public function details($id)
    {
        $product = Product::where('id', $id)->first();

        if (!$product) {
            return null;
        }

        $results = [
            'name' => $product->name,
            'description' => $product->description,
            'selling_price' => $product->selling_price,
            'buying_price' => $product->buying_price,
            'variations' => $product->variations,
            'wishlist_count' => $product->wishlisted_users()->count(),
            'import_count' => $product->imported_users()->count(),

            'sellers' => $product->imported_users->map(
                function ($u) use ($product) {

                    $orders = $product->orders()->where('user_id', $u->id)->count();

                    $ids = $product->orders()->where('user_id', $u->id)->select('orders.id')->get()->pluck('id')->toArray();

                    $revenue = $product->order_items()->whereIn('order_id', $ids)->sum('price');

                    $quantity = $product->order_items()->whereIn('order_id', $ids)->sum('quantity');
                    $product_expenses = round($quantity * $product->buying_price, 2);
                    $product_profit = round($quantity * $product->selling_price - $product_expenses, 2);

                    $confirmation_count = $product->orders()->where('user_id', $u->id)->whereIn('confirmation', ['confirmer', 'change'])->count();
                    $confirmation_rate = $confirmation_count > 0 ? round(($confirmation_count / $orders) * 100, 2) : 0;

                    $delivery_count = $product->orders()->where('user_id', $u->id)->where('confirmation', ['confirmer', 'change'])->whereIn('delivery', ['livrer', 'paid', 'cleared'])->count();

                    $delivery_rate = $delivery_count > 0 ? round(($delivery_count / $orders) * 100, 2) : 0;

                    return [
                        'id' => $u->id,
                        'username' => $u->username,
                        'orders_count' => $product->orders()->where('user_id', $u->id)->count(),
                        'total_quantity' => $quantity,
                        'confirmation_rate' => $confirmation_rate,
                        'delivery_rate' => $delivery_rate,
                        'revenue' => round($revenue, 2),
                        'product_expenses' => $product_expenses,
                        'product_profit' => $product_profit,
                        'profit' => round($quantity * $product->selling_price, 2),
                    ];
                }
            ),

            'orders_count' => $product->orders()->count(),
        ];
        return $results;
    }



    public function update($id, $data)
    {
        try {
            DB::beginTransaction();

            $product = Product::where([
                'product_type' => 'affiliate',
                'id' => $id
            ])->first();

            if (!$product) {
                return null;
            }

            $product->update([
                'name' => data_get($data, 'name', $product->name),
                'description' => data_get($data, 'description', $product->description),
                'buying_price' => data_get($data, 'buying_price', $product->buying_price),
                'selling_price' => data_get($data, 'selling_price', $product->selling_price),

                'delivery_rate' => data_get($data, 'delivery_rate', $product->delivery_rate),
                'confirmation_rate' => data_get($data, 'confirmation_rate', $product->confirmation_rate),

                'status' => data_get($data, 'status', $product->status),
                'category_id' => data_get($data, 'category_id', $product->category_id),
            ]);

            self::updateVariations($product, data_get($data, 'variations', []));
            self::updateMedia($product, data_get($data, 'media', []));
            self::updateMetadata($product, data_get($data, 'metadata', []));


            // self::updateTags($product, data_get($data, 'tags', []));
            // self::updateDeliveries($product, data_get($data, 'deliveries', []));

            DB::commit();
            $product->refresh();
            return $product;
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }

    public static function updateVariations($product, $variations)
    {


        $all_variations = collect($variations);

        // old variations
        $old_variations = $product->variations;

        // select only new added variation from request
        $new_variations = array_values($all_variations->whereNotIn('id', $old_variations->pluck('id'))->all());

        // 1 - handle deleted variations
        $deleted_variations = array_values($old_variations->whereNotIn('id', $all_variations->pluck('id'))->all());

        foreach ($deleted_variations as $v) {
            $v->delete();
        }

        foreach ($product->variations as $v) {
            // get the actual variation
            $variation = ProductVariation::find($v->id);
            // get the same variation but from the request
            $updated_variation = $all_variations->where('id', $v->id)->first();

            

            // update the old variation with new values
            $variation->size = $updated_variation['size'];
            $variation->color = $updated_variation['color'];

            $variation->quantity = (int) $updated_variation['quantity'];
            $variation->save();

        }
        
        $warehouse_id = Warehouse::first()?->id;

        foreach($new_variations as $v) {
            ProductVariation::create([
                'product_id' => $product->id,
                'product_ref' => $product->ref,
                'warehouse_id' => $warehouse_id,
                'size' => data_get($v, 'size', null),
                'color' => data_get($v, 'color', null),
                'quantity' => (int) data_get($v, 'quantity', 0),
                'stockAlert' => 0
            ]);
        }
    }

    public static function updateTags($product, $data)
    {
        // Detach existing tags
        $product->tags()->detach();

        foreach ($data as $t) {
            $tag = Tag::where('name', $t)->first();
            if (!$tag) {
                $tag = Tag::create([
                    'name' => $t,
                ]);
            }
            $product->tags()->attach($tag);
        }
    }

    public static function updateMetadata($product, $metadata = [])
    {
        // Delete existing metadata
        $product->metadata()->delete();

        foreach ($metadata as $value) {
            $product->metadata()->create([
                'meta_key' => $value['meta_key'],
                'meta_value' => $value['meta_value']
            ]);
        }
    }

    public static function updateMedia($product, $media)
    {
        $allMedia = $product->getMedia("*");
        $media = collect($media);
        $newMedia = $media->whereNotNull('uuid');
        $deletedMedia = $allMedia->whereNull('uuid')->whereNotIn('id', $media->pluck('id'));

        // Delete media
        $allMediaIds = $allMedia->pluck('id');
        $currentIds = $media->whereNull('uuid')->pluck('id');

        // detect diffrent between current and all media
        $deletedMediaIds = $allMediaIds->diff($currentIds)->values()->toArray();

        foreach ($allMedia->whereIn('id', $deletedMediaIds) as $m) {
            $m->delete();
        }

        // abort(200, json_encode($deletedMediaIds));
        // abort(200, json_encode());

        foreach ($newMedia as $file) {
            $temporaryMedia = TemporaryMedia::where('uuid', data_get($file, 'uuid'))->first();
            $collection_name = data_get($file, 'collection_name', 'default');

            if ($temporaryMedia) {
                $mediaItem = $temporaryMedia->getMedia($collection_name)->first();
                if ($mediaItem) {
                    $mediaItem->move($product, $collection_name);
                }
            }
        }
    }
}
