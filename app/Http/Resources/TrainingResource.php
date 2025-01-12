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
        $localizedCommercialName = $academy->getTranslation('app_name', $locale) ?? $academy->getTranslation('app_name', 'en');
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'description' => $this->description,
            'max_players' => $this->max_players,
            'level' => $this->level,
            'gender' => $this->gender,
//            'age_group' => $this->age_group,
//            'active' => $this->active,
//            'sport_id' => $this->sport_id,
//            'discount_price' => $this->discount_price != 0 ? $this->price - $this->discount_price : 0,
//            'classes_count' => $this->classes()->count(),
//            'joins_count' => $this->joins()->count(),
//            'is_fav' => $this->is_fav,
//            'address' => new AddressResource($this->whenLoaded('address')),
//            'academy' => new PartnerResource($this->whenLoaded('academy')),
//            'sport' => new SportResource($this->whenLoaded('sport')),
//            'classes' => TClassResource::collection($this->whenLoaded('classes')),
//            'coach' => new CoachResource($this->whenLoaded('coach')),
        ];
    }
}
