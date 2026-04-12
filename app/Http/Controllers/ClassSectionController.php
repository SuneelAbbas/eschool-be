<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassSectionRequest;
use App\Http\Resources\ClassSectionResource;
use App\Models\ClassSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassSectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $classSections = ClassSection::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->whereHas('grade', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        })->with('grade', 'section')->get();

        return response()->json([
            'success' => true,
            'data' => ClassSectionResource::collection($classSections),
        ]);
    }

    public function store(ClassSectionRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $classSection = ClassSection::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Class section created successfully',
            'data' => new ClassSectionResource($classSection->load('grade', 'section')),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $classSection = ClassSection::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->whereHas('grade', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        })->with('grade', 'section')->find($id);

        if (!$classSection) {
            return response()->json([
                'success' => false,
                'message' => 'Class section not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ClassSectionResource($classSection),
        ]);
    }

    public function update(ClassSectionRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $classSection = ClassSection::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->whereHas('grade', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        })->find($id);

        if (!$classSection) {
            return response()->json([
                'success' => false,
                'message' => 'Class section not found',
            ], 404);
        }

        $data = $request->validated();
        $classSection->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Class section updated successfully',
            'data' => new ClassSectionResource($classSection->load('grade', 'section')),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $classSection = ClassSection::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->whereHas('grade', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        })->find($id);

        if (!$classSection) {
            return response()->json([
                'success' => false,
                'message' => 'Class section not found',
            ], 404);
        }

        $classSection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class section deleted successfully',
        ]);
    }
}
