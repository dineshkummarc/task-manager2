<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EmployeeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $v = $request->validate(['search' => ['nullable', 'string'], 'role' => ['nullable', 'exists:roles,name'], 'project_id' => ['nullable', 'exists:projects,id'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $users = User::with(['roles', 'department', 'project'])->when($v['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")))->when($v['role'] ?? null, fn ($q, $r) => $q->role($r))->when($v['project_id'] ?? null, fn ($q, $id) => $q->where('project_id', $id))->latest()->paginate($v['per_page'] ?? 15);

        return UserResource::collection($users);
    }

    public function store(EmployeeRequest $request)
    {
        $data = $request->safe()->except(['roles', 'permissions']);
        $roles = $request->input('roles');
        $permissions = $request->input('permissions', []);
        $user = DB::transaction(function () use ($data, $roles, $permissions) {
            $data['role'] = $roles[0];
            $user = User::create($data);
            $user->syncRoles($roles);
            $user->syncPermissions($permissions);

            return $user;
        });

        return (new UserResource($user->load(['roles', 'permissions', 'department', 'project'])))->response()->setStatusCode(201);
    }

    public function show(User $employee)
    {
        return new UserResource($employee->load(['roles', 'permissions', 'department', 'project']));
    }

    public function update(EmployeeRequest $request, User $employee)
    {
        $data = $request->safe()->except(['roles', 'permissions']);
        $roles = $request->input('roles');
        $permissions = $request->input('permissions', []);
        DB::transaction(function () use ($employee, $data, $roles, $permissions) {
            $data['role'] = $roles[0];
            $employee->update($data);
            $employee->syncRoles($roles);
            $employee->syncPermissions($permissions);
        });

        return new UserResource($employee->fresh()->load(['roles', 'permissions', 'department', 'project']));
    }

    public function destroy(Request $request, User $employee)
    {
        abort_if($employee->is($request->user()), 422, 'You cannot delete your own account.');
        $employee->delete();

        return response()->noContent();
    }
}
