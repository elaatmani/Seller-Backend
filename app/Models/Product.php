<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;


    protected $fillable = [
        'user_id',
        'name',
        'ref',
        'link_video',
        'link_store',
        'transport_mode',
        'expedition_date',
        'country_of_purchase',
        'selling_price',
        'buying_price',
        'description',
        'status',
        'category_id',
        'note',
        'product_type',
        'delivery_rate',
        'confirmation_rate'
    ];


    protected $casts = [
        'user_id' => 'integer',
        'name' => 'string',
        'ref' => 'string',
        'link_video' => 'string',
        'link_store' => 'string',
        'transport_mode' => 'string',
        'expedition_date' => 'datetime',
        'country_of_purchase' => 'string',
        'selling_price' => 'float',
        'buying_price' => 'float',
        'description' => 'string',
        'delivery_rate' => 'float',
        'confirmation_rate' => 'float'
    ];

    protected $appends = [
        'image'
    ];

    protected $with = [ 'seller_user','deliveries', 'offers' ];

    public function variations(){
        return $this->hasMany('App\Models\ProductVariation');
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, ProductAgente::class, 'product_id', 'id', 'id', 'agente_id');
    }

    public function deliveries(){
        return $this->hasMany(ProductDelivery::class,'product_id');
    }

    public function product_image(){
        return $this->hasOne(ProductImage::class, 'product_id', 'id');
    }

    public function getImageAttribute() {
        return !!$this->product_image ? $this->product_image->image_path : null;
    }

    public function offers() {
        return $this->hasMany(ProductOffer::class);
    }

    public function formatForOrder($seller_id) {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'ref' => $this->ref,
            'video' =>  $this->product_type == 'affiliate' ? $this->metadata()->where(['meta_key' => 'video_url_seller_' . $seller_id])->first()?->meta_value ?? '...' : $this->link_video,
            'store' => $this->product_type == 'affiliate' ? $this->metadata()->where(['meta_key' => 'store_url_seller_' . $seller_id])->first()?->meta_value ?? '...' : $this->link_store,
            'variations' => $this->variations->map->formatForOrder(),
            'image' => $this->image,
            'available_with' => $this->deliveries->map(fn($d) => $d->delivery_id),
            'offers' => $this->offers()->when($this->product_type == 'affiliate', function($q) use($seller_id) {
                $q->where('user_id', $seller_id);
            })->get(),
            'created_at' => $this->created_at,
        ];
    }

    public function formatForShow() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ref' => $this->ref,
            'seller_user' => $this->seller_user,
            'description' => $this->description,
            'buying_price' => $this->buying_price,
            'selling_price' => $this->selling_price,
            'variations' => $this->variations->map->formatForShow(),
            'image' => $this->image,
            'status' => $this->status,
            'offers' => $this->offers,
            'created_at' => $this->created_at,
            'type' => $this->product_type,
            'imported_count' => $this->imported_users()->count(),
            'wishlisted_count' => $this->wishlisted_users()->count(),
        ];
    }


    public function seller_user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function metadata()
    {
        return $this->morphMany(Metadata::class, 'model');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', 'model_tag');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function order_items() {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function orders() {
        return $this->belongsToMany(Order::class, OrderItem::class, 'product_id', 'order_id');
    }

    public function imported_users() {
        return $this->belongsToMany(User::class, UserProduct::class, 'product_id', 'user_id')->where('type', 'import');
    }

    public function wishlisted_users() {
        return $this->belongsToMany(User::class, UserProduct::class, 'product_id', 'user_id')->where('type', 'wishlist');
    }
}
