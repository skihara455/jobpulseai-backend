<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function fetchRoles(): JsonResponse
    {
        $roles = Role::orderBy('id')->get();
        return response()->json($roles);
    }

    public function fetchRole(Role $role): JsonResponse
    {
        $this->authorize('view', $role);
        return response()->json($role);
    }

    public function saveRole(Request $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $role = Role::create($data);
        return response()->json($role, 201);
    }

    public function updateRole(Request $request, Role $role): JsonResponse
    {
        $this->authorize('update', $role);

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'sometimes|nullable|string|max:1000',
        ]);

        $role->update($data);
        return response()->json($role);
    }

    public function deleteRole(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);
        $role->delete();

        return response()->json(['message' => 'Role deleted']);
    }
}


