<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesTranslations;
use App\Models\Join;
use App\Models\TClass;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachResource extends JsonResource
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
            'name' => $this->translated('name'),
            'image' => $this->image,
            'license' => $this->translated('license'),
            'license_type' => $this->translated('license_type'),
            'academy_id' => $this->academy_id,
            'gender' => $this->gender,
            'description' => $this->translated('description'),
            'active' => $this->active,
            'academy' => new PartnerResource($this->academy),
            'total_hours' => $this->getTotalHours(),
            'trainings_count' => $this->trainings()->count(),
            'trainees_count' => $this->getCountUsersJoined(),
            'sports' => SportResource::collection($this->whenLoaded('sports')),
        ];
    }

    public function getTotalHours()
    {
        // Compute total hours correctly

        return TClass::whereHas('training', function($query) {
            $query->where('coach_id', $this->id);
        })->get()->sum('duration_in_hours');
    }

    protected function getCountUsersJoined()
    {
      return  Join::whereHas('training', function($query) {
            $query->where('coach_id', $this->id);
        })->count();
    }
}
