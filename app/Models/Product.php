<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'ref',
        'selling_price',
        'buying_price',
        'description'
    ];


    protected $casts = [
        'name' => 'string',
        'ref' => 'string',
        'selling_price' => 'float',
        'buying_price' => 'float',
        'description' => 'string'
    ];

    protected $appends = [
        'image'
    ];

    protected $with = [ 'deliveries', 'offers' ];

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

    public function formatForOrder() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ref' => $this->ref,
            'variations' => $this->variations->map->formatForOrder(),
            'image' => $this->image,
            'available_with' => $this->deliveries->map(fn($d) => $d->delivery_id),
            'offers' => $this->offers
        ];
    }
}
