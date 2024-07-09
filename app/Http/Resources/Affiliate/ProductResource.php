<?php

namespace App\Http\Resources\Affiliate;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // $media = collect($this->getMedia("normal")?->map(fn($m) => ['url' => $m->getFullUrl(), 'type' => $m->mime_type]));

        $media = collect($this->getMedia("*")?->map(fn($m) => [
            'url' => $m->getFullUrl(), 
            'id' => $m->id,
            'type' => $m->mime_type,
            'category' => explode('/', $m->mime_type)[0] ?? null,
            'collection_name' => $m->collection_name
        ]));

        $thumbnail = $this->getMedia("thumbnail")->first();

        if($thumbnail) {
            $thumbnail = [
                'url' => $thumbnail->getFullUrl(),
                'id' => $thumbnail->id,
                'type' => $thumbnail->mime_type,
                'category' => explode('/', $thumbnail->mime_type)[0] ?? null,
                'collection_name' => $thumbnail->collection_name
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'imported' => $this->imported_users()->where('user_id', auth()->id())->exists(),
            'wishlisted' => $this->wishlisted_users()->where('user_id', auth()->id())->exists(),
            'description' => $this->description,
            'sku' => $this->ref,
            'selling_price' => $this->selling_price,
            'confirmation_rate' => $this->confirmation_rate,
            'delivery_rate' => $this->delivery_rate,
            'created_at' => $this->created_at,
            'thumbnail' => $thumbnail,
            'media' => $media,
            'tags' => $this->tags,
            'tags' => [ 'car', 'mirror', 'sun' ],
            'category' => $this->category?->name,
            'metadata' => $this->metadata->map(fn($m) => [
                'meta_key' => $m->meta_key,
                'meta_value' => $m->meta_value,
            ]),
            'variations' => $this->variations->map(fn($v) => [
                'id' => $v->id,
                'size' => $v->size,
                'color' => $v->color,
                'quantity' => $v->quantity,
                'available_quantity' => $v->available_quantity,
            ])
        ];
    }
}
