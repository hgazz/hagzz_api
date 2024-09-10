<?php

namespace App\Http\Resources;

use App\Models\TClass;
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
            'license_type' => $this->getTranslation('license_type', app()->getLocale()) ?? $this->getTranslation('license_type', 'en'),
            'academy_id' => $this->academy_id,
            'gender' => $this->gender,
            'description' => $this->getTranslation('description', app()->getLocale()) ?? $this->getTranslation('description', 'en'),
            'active' => $this->active,
            'academy' => new PartnerResource($this->academy),
            'total_hours' => $this->getTotalHours(),
            'trainings_count' => $this->trainings()->count(),
            'trainees_count' => $this->total_users_joined,
            'sports' => SportResource::collection($this->whenLoaded('sports')),
        ];
    }

    public function getTotalHours()
    {
        $totalHours = TClass::whereHas('training', function($query) {
            $query->where('coach_id', $this->id)
                ->where('end_date', '<', now());
        })->sum('duration_in_hours');

        return $totalHours > 0 ? ceil($totalHours) : 0;
    }
}
