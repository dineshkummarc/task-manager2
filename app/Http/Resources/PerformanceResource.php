<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['employee' => ['id' => $this->id, 'name' => $this->name, 'email' => $this->email], 'completed_tasks' => (int) ($this->completed_tasks_count ?? 0), 'performance_percentage' => $this->getPerformancePercentage(), 'project' => $this->whenLoaded('project', fn () => ['id' => $this->project?->id, 'name' => $this->project?->name])];
    }
}
