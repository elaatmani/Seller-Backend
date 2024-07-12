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
        $hasTikTok = $this->getMedia("tiktok_ads")?->count() > 0;
        $hasFacebook = $this->getMedia("facebook_ads")?->count() > 0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'selling_price' => $this->selling_price,
            'created_at' => $this->created_at,
            'confirmation_rate' => $this->confirmation_rate,
            'delivery_rate' => $this->delivery_rate,
            'thumbnail' => str_replace('/media', 'public/media', $this->getMedia("thumbnail")->first()?->getFullUrl()),
            // 'tags' => $this->tags,
            'media' => [
                'facebook_ads' => $hasFacebook,
                'tiktok_ads' => $hasTikTok,
            ],
            'tags' => [ 'car', 'mirror', 'sun' ],
            'category' => $this->category?->name,
            'imported' => $this->imported_users()->where('user_id', auth()->id())->exists(),
            'wishlisted' => $this->wishlisted_users()->where('user_id', auth()->id())->exists(),
        ];
    }
}
