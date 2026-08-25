<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PermissionRequest;
use App\Http\Resources\PermissionResource;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return PermissionResource::collection(Permission::orderBy('name')->get());
    }

    public function store(PermissionRequest $request)
    {
        $p = Permission::create(['name' => $request->name, 'guard_name' => 'web']);

        return (new PermissionResource($p))->response()->setStatusCode(201);
    }

    public function update(PermissionRequest $request, Permission $permission)
    {
        $permission->update($request->validated());

        return new PermissionResource($permission);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->noContent();
    }
}
