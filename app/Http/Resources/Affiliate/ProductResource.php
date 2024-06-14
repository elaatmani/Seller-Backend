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
        $media = collect($this->getMedia("normal")?->map(fn($m) => $m->getFullUrl()));

        $thumbnail = $this->getMedia("thumbnail")->first()?->getFullUrl();

        if($thumbnail) {
            $media->prepend($thumbnail);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'selling_price' => $this->selling_price,
            'created_at' => $this->created_at,
            'thumbnail' => $thumbnail,
            'media' => $media,
            'tags' => $this->tags,
            'category' => $this->category?->name,
        ];
    }
}
