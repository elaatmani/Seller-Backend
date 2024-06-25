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
        $media = collect($this->getMedia("normal")?->map(fn($m) => ['url' => $m->getFullUrl(), 'type' => $m->mime_type]));

        $media = collect($this->getMedia("normal")?->map(fn($m) => [
            'url' => $m->getFullUrl(), 
            'type' => $m->mime_type,
            'category' => explode('/', $m->mime_type)[0] ?? null,
        ]));

        $thumbnail = $this->getMedia("thumbnail")->first();

        if($thumbnail) {
            $thumbnail = [
                'url' => $thumbnail->getFullUrl(),
                'type' => $thumbnail->mime_type,
                'category' => explode('/', $thumbnail->mime_type)[0] ?? null,
            ];
            $media->prepend($thumbnail);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->ref,
            'selling_price' => $this->selling_price,
            'created_at' => $this->created_at,
            'thumbnail' => $thumbnail,
            'media' => $media,
            'tags' => $this->tags,
            'tags' => [ 'car', 'mirror', 'sun' ],
            'category' => $this->category?->name,
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
