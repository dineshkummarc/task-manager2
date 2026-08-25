<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $v = $request->validate(['project_id' => ['nullable', 'exists:projects,id'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        return DepartmentResource::collection(Department::with('project')->withCount('users')->when($v['project_id'] ?? null, fn ($q, $id) => $q->where('project_id', $id))->orderBy('name')->paginate($v['per_page'] ?? 15));
    }

    public function store(DepartmentRequest $request)
    {
        $department = Department::create($request->validated());

        return (new DepartmentResource($department->load('project')))->response()->setStatusCode(201);
    }

    public function show(Department $department)
    {
        return new DepartmentResource($department->load('project')->loadCount('users'));
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());

        return new DepartmentResource($department->fresh()->load('project')->loadCount('users'));
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return response()->noContent();
    }
}
