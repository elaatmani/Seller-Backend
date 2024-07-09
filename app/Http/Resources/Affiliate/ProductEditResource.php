<?php

namespace App\Http\Resources\Affiliate;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductEditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $media = collect($this->getMedia("*")?->map(fn($m) => [
            'id' => $m->id,
            'url' => $m->getFullUrl(), 
            'type' => $m->mime_type,
            'category' => explode('/', $m->mime_type)[0],
            'collection_name' => $m->collection_name,
        ]));

        // $thumbnail = $this->getMedia("thumbnail")->first()?->getFullUrl();

        // if($thumbnail) {
        //     $media->prepend($thumbnail);
        // }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'selling_price' => $this->selling_price,
            'buying_price' => $this->buying_price,
            'media' => $media,
            'tags' => $this->tags,
            'category_id' => $this->category_id,
            'metadata' => $this->metadata,
            'has_variations' => true,
            'variations' => $this->variations,
            'confirmation_rate' => $this->confirmation_rate,
            'delivery_rate' => $this->delivery_rate,
            'sku' => $this->ref,
        ];
    }
}
