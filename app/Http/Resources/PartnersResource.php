<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesTranslations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnersResource extends JsonResource
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
            'commercial_name' => $this->translated('commercial_name'),
            'logo' => $this->logo,
            'sports' => SportResource::collection($this->whenLoaded('sports')),
           // 'trainings' => TrainingResource::collection($this->trainings),
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'galleries' => $this->whenLoaded('galleries'),
            'follows_count' => $this->follows_count ?? 0,
            'coaches_count' => $this->coaches_count ?? 0,
            'trainings_count' => $this->trainings_count ?? 0,
            'addresses_count' => $this->addresses_count ?? 0,
            'website' => $this->website,
        ];
    }
}
