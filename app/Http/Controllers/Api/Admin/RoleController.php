<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RoleRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return RoleResource::collection(Role::with('permissions')->orderBy('name')->get());
    }

    public function store(RoleRequest $request)
    {
        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        $role->syncPermissions($request->input('permissions', []));

        return (new RoleResource($role->load('permissions')))->response()->setStatusCode(201);
    }

    public function show(Role $role)
    {
        return new RoleResource($role->load('permissions'));
    }

    public function update(RoleRequest $request, Role $role)
    {
        abort_if($role->name === 'admin' && $request->name !== 'admin', 422, 'The admin role name cannot be changed.');
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->input('permissions', []));

        return new RoleResource($role->fresh()->load('permissions'));
    }

    public function destroy(Role $role)
    {
        abort_if(in_array($role->name, ['admin', 'manager', 'employee'], true), 422, 'Default roles cannot be deleted.');
        $role->delete();

        return response()->noContent();
    }

    public function permissions(Request $request)
    {
        $v = $request->validate(['per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        return PermissionResource::collection(Permission::orderBy('name')->paginate($v['per_page'] ?? 100));
    }
}
