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
            'commercial_name' => $this->translated('app_name'),
            'logo' => $this->logo,
            'sports' => SportResource::collection($this->sports),
           // 'trainings' => TrainingResource::collection($this->trainings),
            'addresses' => AddressResource::collection($this->addresses),
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'galleries' => $this->galleries,
            'follows_count' => $this->follows()->count(),
            'coaches_count' => $this->coaches()->count(),
            'trainings_count' => $this->trainings()->count(),
            'addresses_count' => $this->addresses()->count(),
            'website' => $this->website,
        ];
    }
}
