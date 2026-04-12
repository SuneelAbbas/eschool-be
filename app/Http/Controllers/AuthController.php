<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterInstituteRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\InstituteResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\auth;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(RegisterInstituteRequest $request): JsonResponse
    {
        $result = $this->authService->registerInstitute($request->validated());

        if ($result['exists']) {
            $institute = $result['institute'];
            
            if (!$institute) {
                return response()->json([
                    'success' => false,
                    'message' => 'An account with this email already exists.',
                    'data' => [
                        'user' => new UserResource($result['user']),
                        'institute' => null,
                    ],
                ], 409);
            }

            $statusMessage = match ($institute->status) {
                'pending' => 'An account with this email already exists. Your registration is pending approval.',
                'rejected' => 'Your registration was rejected. You may register again with updated information.',
                'suspended' => 'Your account has been suspended. Please contact support.',
                default => 'An account with this email already exists.',
            };

            return response()->json([
                'success' => false,
                'message' => $statusMessage,
                'data' => [
                    'user' => new UserResource($result['user']),
                    'institute' => new InstituteResource($institute),
                ],
                'institute_status' => $institute->status,
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Your institute is pending approval.',
            'data' => [
                'user' => new UserResource($result['user']),
                'institute' => new InstituteResource($result['institute']),
                'token' => $result['token'],
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->authService->login($request->email, $request->password);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (isset($result['pending_status'])) {
            $statusMessage = match ($result['pending_status']) {
                'pending' => 'Your institute is pending approval. You will have full access once approved.',
                'rejected' => 'Your registration was rejected. You may register again with updated information.',
                'suspended' => 'Your account has been suspended. Please contact support.',
                default => 'Your account is not active.',
            };

            return response()->json([
                'success' => false,
                'message' => $statusMessage,
                'data' => [
                    'user' => new UserResource($result['user']),
                    'institute' => $result['institute'] ? new InstituteResource($result['institute']) : null,
                    'token' => $result['token'],
                ],
                'institute_status' => $result['pending_status'],
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => new UserResource($result['user']),
                'institute' => $result['institute'] ? new InstituteResource($result['institute']) : null,
                'token' => $result['token'],
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
