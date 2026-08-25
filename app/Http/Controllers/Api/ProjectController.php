<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $projects = Project::query()
            ->where(function ($query) use ($user) {
                $query->whereKey($user->project_id)
                    ->orWhereHas('tasks', fn ($taskQuery) => $taskQuery->where('assigned_to', $user->id));
            })
            ->orderBy('name')
            ->get();

        return ProjectResource::collection($projects);
    }
}
