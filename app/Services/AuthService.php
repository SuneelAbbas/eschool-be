<?php

namespace App\Services;

use App\Models\User;
use App\Models\Institute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;

class AuthService
{
    public function registerInstitute(array $data): array
    {
        $existingUser = User::where('email', $data['email'])->first();

        if ($existingUser) {
            $institute = Institute::where('admin_user_id', $existingUser->id)->first();
            
            return [
                'exists' => true,
                'user' => $existingUser,
                'institute' => $institute,
                'token' => null,
            ];
        }

        $user = null;
        $institute = null;

        DB::transaction(function () use ($data, &$user, &$institute) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'user_type' => 'admin',
            ]);

            $logoInitials = $this->generateLogoInitials($data['institute_name']);

            $institute = Institute::create([
                'name' => $data['institute_name'],
                'logo' => $logoInitials,
                'address' => $data['institute_address'] ?? null,
                'contact_email' => $data['email'],
                'contact_phone' => $data['institute_contact_phone'] ?? null,
                'type' => $data['institute_type'] ?? null,
                'city' => $data['institute_city'] ?? null,
                'no_of_students' => $data['institute_no_of_students'] ?? null,
                'description' => $data['institute_description'] ?? null,
                'status' => 'pending',
                'admin_user_id' => $user->id,
                'plan_id' => $data['plan_id'] ?? null,
            ]);

                $user->institute_id = $institute->id;
            $user->save();
        });

        $plainTextToken = $user->createToken('auth-token')->plainTextToken;
        $user->refresh();

        return [
            'exists' => false,
            'user' => $user,
            'institute' => $institute,
            'token' => $plainTextToken,
        ];
    }

    public function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        $institute = Institute::where('admin_user_id', $user->id)->first();

        if ($institute && $institute->status !== 'approved') {
            return [
                'user' => $user,
                'institute' => $institute,
                'token' => null,
                'pending_status' => $institute->status,
            ];
        }

        $plainTextToken = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'institute' => $institute,
            'token' => $plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function createToken(User $user, string $name = 'api-token'): string
    {
        return $user->createToken($name)->plainTextToken;
    }

    private function generateLogoInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        
        return strtoupper(substr($name, 0, 2));
    }
}
