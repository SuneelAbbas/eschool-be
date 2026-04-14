<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Role::withCount('users')->with('permissions')
            ->whereNotIn('slug', Role::getSystemSlugs());

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $roles = $query->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => RoleResource::collection($roles),
        ]);
    }

    public function store(RoleRequest $request): JsonResponse
    {
        $data = $request->validated();

        $role = Role::create($data);

        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => new RoleResource($role),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $role = Role::with(['permissions', 'users'])->withCount('users')->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new RoleResource($role),
        ]);
    }

    public function update(RoleRequest $request, int $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
            ], 404);
        }

        if ($role->isProtected()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify protected system role',
            ], 422);
        }

        $data = $request->validated();

        $role->update($data);

        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => new RoleResource($role),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::withCount('users')->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
            ], 404);
        }

        if ($role->isProtected()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete protected system role',
            ], 422);
        }

        if ($role->users_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete role. {$role->users_count} user(s) are assigned to this role.",
            ], 422);
        }

        $role->permissions()->detach();
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully',
        ]);
    }
}
