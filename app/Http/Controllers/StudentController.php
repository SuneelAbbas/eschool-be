<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
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

        return response()->json(['data' => Student::all()]);
    }

    public function store(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'registration_date' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'roll_no' => ['nullable', 'string', 'max:255'],
            'grade_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'gender' => ['nullable', 'string', 'max:50'],
            'mobile_number' => ['nullable', 'string', 'max:50'],
            'parents_name' => ['nullable', 'string', 'max:255'],
            'parents_mobile_number' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'string', 'max:255'],
            'blood_group' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'upload' => ['nullable', 'string', 'max:255'],
            'institute_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
        ]);

        $student = Student::create($data);
        return response()->json(['message' => 'Student created', 'data' => $student], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $student = Student::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json(['data' => $student]);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $student = Student::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $data = $request->validate([
            'firstName' => ['nullable', 'string', 'max:255'],
            'lastName' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'registration_date' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'roll_no' => ['nullable', 'string', 'max:255'],
            'grade_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'gender' => ['nullable', 'string', 'max:50'],
            'mobile_number' => ['nullable', 'string', 'max:50'],
            'parents_name' => ['nullable', 'string', 'max:255'],
            'parents_mobile_number' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'string', 'max:255'],
            'blood_group' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'upload' => ['nullable', 'string', 'max:255'],
            'school_id' => ['nullable', 'integer'],
        ]);

        $student->update($data);
        return response()->json(['message' => 'Student updated', 'data' => $student]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $student = Student::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->delete();
        return response()->json(['message' => 'Student deleted']);
    }
}
