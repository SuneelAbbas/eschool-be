<?php

namespace App\Http\Controllers;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'institute.name' => ['required', 'string', 'max:255'],
            'institute.logo' => ['nullable', 'string', 'max:255'],
            'institute.address' => ['nullable', 'string', 'max:255'],
            'institute.contact_email' => ['nullable', 'email', 'max:255'],
            'institute.contact_phone' => ['nullable', 'string', 'max:50'],
            'institute.type' => ['nullable', 'string', 'max:255'],
            'institute.city' => ['nullable', 'string', 'max:255'],
            'institute.no_of_students' => ['nullable', 'integer'],
            'institute.description' => ['nullable', 'string', 'max:2000'],
            'institute.status' => ['nullable', 'string', 'max:50'],
            'institute.plan_id' => ['nullable', 'integer'],
        ]);

        $user = null;
        $institute = null;

        DB::transaction(function () use ($data, &$user, &$institute) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'api_token' => bin2hex(random_bytes(20)),
            ]);

            $instituteData = $data['institute'];
            $instituteData['user_id'] = $user->id;
            $institute = Institute::create($instituteData);
        });

        return response()->json([
            'message' => 'User and institute registered successfully',
            'data' => [
                'user' => $user,
                'institute' => $institute,
                'token' => $user->api_token,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user->api_token = bin2hex(random_bytes(40));
        $user->save();

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $user->api_token,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json(['data' => $user]);
    }

    public function logout(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->api_token = null;
        $user->save();

        return response()->json(['message' => 'Logged out successfully']);
    }

    private function getUserFromToken(Request $request): ?User
    {
        $token = null;
        $bearer = $request->bearerToken();
        if ($bearer) {
            $token = $bearer;
        }

        if (!$token) {
            return null;
        }

        return User::where('api_token', $token)->first();
    }
}
