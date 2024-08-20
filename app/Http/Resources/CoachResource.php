<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachResource extends JsonResource
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
            'name' => $this->getTranslation('name', app()->getLocale()) ?? $this->getTranslation('name', 'en'),
            'image' => $this->image,
            'license' => $this->getTranslation('license', app()->getLocale()) ?? $this->getTranslation('license', 'en'),
            'academy_id' => $this->academy_id,
            'gender' => $this->gender,
            'description' => $this->getTranslation('description', app()->getLocale()) ?? $this->getTranslation('description', 'en'),
            'active' => $this->active,
            'academy' => new PartnerResource($this->academy),
            'total_hours' => $this->total_hours,
            'trainings_count' => $this->trainings()->count(),
            'trainees_count' => $this->total_users_joined,
            'sports' => SportResource::collection($this->whenLoaded('sports')),
        ];
    }
}
