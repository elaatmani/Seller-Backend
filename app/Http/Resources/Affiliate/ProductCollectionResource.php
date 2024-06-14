<?php

namespace App\Http\Resources\Affiliate;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductCollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'selling_price' => $this->selling_price,
            'created_at' => $this->created_at,
            'thumbnail' => $this->getMedia("thumbnail")->first()?->getFullUrl(),
            // 'tags' => $this->tags,
            'tags' => [ 'car', 'mirror', 'sun' ],
            'category' => $this->category?->name,
        ];
    }
}
