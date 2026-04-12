<?php

namespace App\Http\Controllers;

use App\Http\Requests\SectionRequest;
use App\Http\Resources\SectionResource;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $sections = Section::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->whereHas('grade', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        })->with('grade')->get();

        return response()->json([
            'success' => true,
            'data' => SectionResource::collection($sections),
        ]);
    }

    public function store(SectionRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $section = Section::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully',
            'data' => new SectionResource($section->load('grade')),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $section = Section::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->whereHas('grade', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        })->with('grade')->find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SectionResource($section),
        ]);
    }

    public function update(SectionRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $section = Section::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->whereHas('grade', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        })->find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        $data = $request->validated();
        $section->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully',
            'data' => new SectionResource($section->load('grade')),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $section = Section::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->whereHas('grade', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        })->find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully',
        ]);
    }
}
