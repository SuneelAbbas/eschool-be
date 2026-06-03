<?php

namespace App\Http\Controllers;

use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InstituteController extends Controller
{
    /**
     * Get institute status by ID
     * Public endpoint to check if an institute is approved, pending, or rejected
     */
    public function getStatus(int $id): JsonResponse
    {
        $institute = Institute::find($id);

        if (!$institute) {
            return response()->json([
                'status' => 'not_found'
            ], 404);
        }

        return response()->json([
            'status' => $institute->status
        ]);
    }
}