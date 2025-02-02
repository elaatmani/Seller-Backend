<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'buying_price' => $this->buying_price,
            'selling_price' => $this->selling_price,
            'ref' => $this->ref,
            'link_video' => $this->link_video,
            'link_store' => $this->link_store,
            'expedition_date' => $this->expedition_date,
            'transport_mode' => $this->transport_mode,
            'country_of_purchase' => $this->country_of_purchase,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'image' => $this->image,
            'variations' => VariationResource::collection($this->whenLoaded('variations')),
            'deliveries' => DeliveryResource::collection($this->whenLoaded('deliveries')),
            'offers' => OfferResource::collection($this->whenLoaded('offers')),
        ];
    }
}