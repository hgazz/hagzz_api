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
            'name' => $this->name,
            'image' => $this->image,
            'license' => $this->license,
            'academy_id' => $this->academy_id,
            'gender' => $this->gender,
            'description' => $this->description,
            'active' => $this->active,
            'academy' => new PartnerResource($this->academy),
            'total_hours' => $this->getTotalHours(),
        ];
    }
}
