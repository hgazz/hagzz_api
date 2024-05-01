<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'type' => $this->type,
            'title' => $this->title,
            'data' => $this->data,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at->toDateTimeString(), // Format date
            'details' => $this->decodeDetails($this->details),
            'image' => $this->image
        ];
    }

    protected function decodeDetails($details)
    {
        $decoded = json_decode($details, true);
        return $decoded === null ? null : $decoded;
    }
}
