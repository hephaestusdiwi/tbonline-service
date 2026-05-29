<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'address'         => $this->address,
            'city'            => $this->city,
            'province'        => $this->province,
            'phone'           => $this->phone,
            'google_maps_url' => $this->google_maps_url,
            'operating_hours' => $this->operating_hours ?? [],
            'latitude'        => (float) $this->latitude,
            'longitude'       => (float) $this->longitude,
            'is_active'       => $this->is_active,
            'directions_url'  => "https://www.google.com/maps/dir/?api=1&destination={$this->latitude},{$this->longitude}",
        ];
    }
}