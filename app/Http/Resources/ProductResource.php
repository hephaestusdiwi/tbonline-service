<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'sell_price'    => $this->sell_price,
            'market_price'  => $this->market_price,
            'photo'         => $this->photo_1,
            'category'      => $this->category,
            'brand'         => $this->brand,
            'sku'           => $this->sku,
            'stock_qty'     => $this->stock_qty,
            'published'     => (bool) $this->published,
            'has_variants' => $this->whenLoaded('optionTypes', fn() => $this->optionTypes->isNotEmpty(), false),
        ];
    }
}