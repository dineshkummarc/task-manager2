<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'email' => $this->email, 'phone' => $this->phone, 'role' => $this->role, 'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->values()), 'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('name')->values()), 'department' => $this->whenLoaded('department', fn () => ['id' => $this->department?->id, 'name' => $this->department?->name]), 'project' => $this->whenLoaded('project', fn () => ['id' => $this->project?->id, 'name' => $this->project?->name]), 'job_responsibility' => $this->job_responsibility, 'created_at' => $this->created_at?->toISOString()];
    }
}
