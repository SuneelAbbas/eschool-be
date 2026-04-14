<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\ToggleUserStatusRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = User::with(['roles', 'institute']);

        if (!$user->isSuperAdmin()) {
            $query->where('institute_id', $user->institute_id);
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(UserRequest $request): JsonResponse
    {
        $admin = $request->user();
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);
        $data['status'] = $data['status'] ?? 'active';

        if (!$admin->isSuperAdmin()) {
            $data['institute_id'] = $admin->institute_id;
        }

        $user = User::create($data);

        $user->assignRole(Role::where('slug', $user->user_type)->first());

        $user->load(['roles', 'institute']);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $query = User::with(['roles.permissions', 'permissions', 'institute']);

        if (!$user->isSuperAdmin()) {
            $query->where('institute_id', $user->institute_id);
        }

        $targetUser = $query->find($id);

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new UserResource($targetUser),
        ]);
    }

    public function update(UserRequest $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        $query = User::where('id', $id);

        if (!$currentUser->isSuperAdmin()) {
            $query->where('institute_id', $currentUser->institute_id);
        }

        $user = $query->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        if (isset($data['user_type'])) {
            $role = Role::where('slug', $data['user_type'])->first();
            if ($role) {
                $user->syncRoles([$role->id], $currentUser->id);
            }
        }

        $user->load(['roles', 'institute']);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->id === $id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account',
            ], 422);
        }

        $query = User::where('id', $id);

        if (!$currentUser->isSuperAdmin()) {
            $query->where('institute_id', $currentUser->institute_id);
        }

        $user = $query->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $user->roles()->detach();
        $user->permissions()->detach();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    public function assignRoles(AssignRoleRequest $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        $query = User::where('id', $id);

        if (!$currentUser->isSuperAdmin()) {
            $query->where('institute_id', $currentUser->institute_id);
        }

        $user = $query->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $user->syncRoles($request->role_ids, $currentUser->id);

        if ($request->filled('user_type')) {
            $user->update(['user_type' => $request->user_type]);
        }

        $user->load(['roles']);

        return response()->json([
            'success' => true,
            'message' => 'Roles assigned successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function toggleStatus(ToggleUserStatusRequest $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->id === $id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change your own status',
            ], 422);
        }

        $query = User::where('id', $id);

        if (!$currentUser->isSuperAdmin()) {
            $query->where('institute_id', $currentUser->institute_id);
        }

        $user = $query->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $user->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        $query = User::where('id', $id);

        if (!$currentUser->isSuperAdmin()) {
            $query->where('institute_id', $currentUser->institute_id);
        }

        $user = $query->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }

    public function myPermissions(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles.permissions', 'permissions']);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
                'permissions' => $user->getAllPermissions()->pluck('slug'),
            ],
        ]);
    }
}
