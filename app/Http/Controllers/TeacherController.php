<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    private function getUserFromToken(Request $request): ?User
    {
        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }
        return User::where('api_token', $token)->first();
    }

    public function index(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json(['data' => Teacher::all()]);
    }

    public function store(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'join_date' => ['required', 'string', 'max:255'],
            'cnic_number' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:50'],
            'mobile_number' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'string', 'max:255'],
            'blood_group' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'academic_qualification' => ['nullable', 'string', 'max:255'],
            'school_id' => ['required', 'integer'],
        ]);

        $teacher = Teacher::create($data);
        return response()->json(['message' => 'Teacher created', 'data' => $teacher], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $teacher = Teacher::find($id);
        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        return response()->json(['data' => $teacher]);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $teacher = Teacher::find($id);
        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $data = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'join_date' => ['nullable', 'string', 'max:255'],
            'cnic_number' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:50'],
            'mobile_number' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'string', 'max:255'],
            'blood_group' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'academic_qualification' => ['nullable', 'string', 'max:255'],
            'school_id' => ['nullable', 'integer'],
        ]);

        $teacher->update($data);
        return response()->json(['message' => 'Teacher updated', 'data' => $teacher]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $teacher = Teacher::find($id);
        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $teacher->delete();
        return response()->json(['message' => 'Teacher deleted']);
    }
}
