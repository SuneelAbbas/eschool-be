<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Permission::query();

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $permissions = $query->orderBy('group')->orderBy('name')->get();

        $grouped = $permissions->groupBy('group');

        return response()->json([
            'success' => true,
            'data' => PermissionResource::collection($permissions),
            'grouped' => $grouped->map(fn($group) => PermissionResource::collection($group)),
        ]);
    }

    public function groups(): JsonResponse
    {
        $groups = Permission::distinct()->pluck('group')->filter()->values()->sort();

        return response()->json([
            'success' => true,
            'data' => $groups,
        ]);
    }
}
