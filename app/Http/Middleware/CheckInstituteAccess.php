<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstituteAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $instituteId = $request->route('institute_id') 
            ?? $request->input('institute_id')
            ?? $request->institute_id;

        if ($instituteId && !$user->canAccessInstitute($instituteId)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You do not have access to this institute.',
            ], 403);
        }

        return $next($request);
    }
}
