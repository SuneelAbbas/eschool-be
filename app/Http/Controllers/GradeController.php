<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\Request;

class GradeController extends Controller
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
            return response()->json(['status' => 'error','message' => 'Unauthorized'], 401);
        }

        $instituteId = $request->query('institute_id');
        if (!$instituteId) {
            return response()->json(['status' => 'error','message' => 'institute_id query parameter is required'], 400);
        }

        $grades = Grade::where('institute_id', $instituteId)->where('user_id', $user->id)->get();
        return response()->json(['status' => 'success','data' => $grades]);
    }

    public function store(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['status' => 'error','message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'institute_id' => ['required', 'integer'],
        ]);

        $data['user_id'] = $user->id;
        $grade = Grade::create($data);

        return response()->json(['status' => 'success','message' => 'Grade created', 'data' => $grade], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['status' => 'error','message' => 'Unauthorized'], 401);
        }

        $grade = Grade::where('id', $id)->where('user_id', $user->id)->first();
        if (!$grade) {
            return response()->json(['status' => 'error','message' => 'Grade not found'], 404);
        }

        return response()->json(['status' => 'success','data' => $grade]);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['status' => 'error','message' => 'Unauthorized'], 401);
        }

        $grade = Grade::where('id', $id)->where('user_id', $user->id)->first();
        if (!$grade) {
            return response()->json(['status' => 'error','message' => 'Grade not found'], 404);
        }

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $grade->update($data);
        return response()->json(['status' => 'success','message' => 'Grade updated', 'data' => $grade]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['status' => 'error','message' => 'Unauthorized'], 401);
        }

        $grade = Grade::where('id', $id)->where('user_id', $user->id)->first();
        if (!$grade) {
            return response()->json(['status' => 'error','message' => 'Grade not found'], 404);
        }

        $grade->delete();
        return response()->json(['status' => 'success','message' => 'Grade deleted']);
    }
}

