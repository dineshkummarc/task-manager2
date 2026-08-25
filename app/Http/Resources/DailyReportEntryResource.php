<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyReportEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'report_date' => $this->report_date?->toDateString(), 'slot_start' => $this->slot_start, 'slot_label' => $this->slot_label, 'task_title' => $this->task_title, 'description' => $this->description, 'employee' => $this->whenLoaded('user', fn () => ['id' => $this->user?->id, 'name' => $this->user?->name, 'email' => $this->user?->email]), 'project' => $this->whenLoaded('project', fn () => ['id' => $this->project?->id, 'name' => $this->project?->name])];
    }
}
