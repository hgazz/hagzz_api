<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSportResource extends JsonResource
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
            'name' => $this->name,
            'phone' => $this->phone,
            'is_verify' => $this->is_verify,
            'language' => $this->language,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date,
            'image' => $this->image,
            'country_id' => $this->country_id,
            'city_id' => $this->city_id,
            'area_id' => $this->area_id,
            'user_type' => $this->user_type,
            'sports' => UserSportsResource::collection($this->whenLoaded('sports')),
        ];
    }
}
