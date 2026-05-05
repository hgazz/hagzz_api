<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesTranslations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    use ResolvesTranslations;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'address' => $this->translated('address'),
            'city' => new CityResource($this->city),
            'area' => new AreaResource($this->area),
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'active' => $this->active
        ];
    }
}
