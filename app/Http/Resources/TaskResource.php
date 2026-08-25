<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->is_blocked ? 'blockage' : $this->status,
            'is_blocked' => (bool) $this->is_blocked,
            'block_reason' => $this->block_reason,
            'completion_note' => $this->completion_note,
            'due_date' => $this->due_date?->toISOString(),
            'estimated_minutes' => (int) $this->estimated_minutes,
            'estimated_time' => $this->estimated_time,
            'project' => $this->whenLoaded('project', fn () => [
                'id' => $this->project?->id,
                'name' => $this->project?->name,
            ]),
            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department?->id,
                'name' => $this->department?->name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
