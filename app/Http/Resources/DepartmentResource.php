<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'code' => $this->code, 'responsibilities' => $this->responsibilities ?? [], 'project' => $this->whenLoaded('project', fn () => ['id' => $this->project?->id, 'name' => $this->project?->name]), 'employees_count' => $this->whenCounted('users'), 'created_at' => $this->created_at?->toISOString()];
    }
}
