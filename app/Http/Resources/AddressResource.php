<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'address' => $this->getTranslation('address', app()->getLocale()),
            'city' => new CityResource($this->city),
            'area' => new AreaResource($this->area),
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'active' => $this->active
        ];
    }
}
