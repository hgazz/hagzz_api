<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale(); // Current locale
        $academy = $this->academy; // Assuming 'academy' is already an object in the loaded Training model
        $localizedCommercialName = $academy->getTranslation('commercial_name', $locale) ?? $academy->getTranslation('commercial_name', 'en');
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'description' => $this->description,
            'max_players' => $this->max_players,
            'level' => $this->level,
            'gender' => $this->gender,
            'age_group' => $this->age_group,
            'active' => $this->active,
            'sport_id' => $this->sport_id,
            'discount_price' => $this->discount_price,
            'classes_count' => $this->classes()->count(),
            'joins_count' => $this->joins()->count(),
            'address' => [
                'id' => $this->address->id,
                'address' => $this->address->getTranslation('address', $locale) ?? $this->address->getTranslation('address', 'en'),
                'area' => AreaResource::collection($this->address->area),
                'city' => CityResource::collection($this->address->city),
            ],
            'academy' => [
                'id' => $academy->id,
                'commercial_name' => $localizedCommercialName,
                'logo' => $academy->logo,
                'follows_count' => $academy->follows()->count(),
            ],
            'sport' =>[
                'id' => $this->sport->id,
                'name' => $this->sport->getTranslation('name', $locale) ?? $this->sport->getTranslation('name', 'en'),
                'icon' => $this->sport->icon,
            ],
            'classes' => TClassResource::collection($this->classes),
        ];
    }
}
