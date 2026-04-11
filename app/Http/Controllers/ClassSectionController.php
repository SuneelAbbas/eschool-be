<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\User;
use Illuminate\Http\Request;

class ClassSectionController extends Controller
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

        return response()->json(['status' => 'success','data' => ClassSection::all()]);
    }

    public function store(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['status' => 'error','message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'room_number' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'string', 'max:255'],
            'teacher_id' => ['nullable', 'integer'],
            'grade_id' => ['required', 'integer'],
            'institute_id' => ['required', 'integer'],
        ]);

        $section = ClassSection::create($data);
        return response()->json(['status' => 'success','message' => 'Class section created', 'data' => $section], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['status' => 'error','message' => 'Unauthorized'], 401);
        }

        $section = ClassSection::find($id);
        if (!$section) {
            return response()->json(['status' => 'error','message' => 'Class section not found'], 404);
        }

        return response()->json(['data' => $section]);
    }

    public function update(Request $request, $id)
    {
                    // return response()->json(['status' => 'error','message' => $id], 404);

        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['status' => 'error','message' => 'Unauthorized'], 401);
        }

        $section = ClassSection::findOrFail($id);
        if (!$section) {
            return response()->json(['status' => 'error','message' => 'Class section not found'], 404);
        }

        $section->name = $request->input('name');
        $section->room_number = $request->input('room_number');
        $section->capacity = $request->input('capacity');
        $section->teacher_id = $request->input('teacher_id');
        $section->grade_id = $request->input('grade_id');
        $section->update();

        // $data = $request->validate([
        //     'name' => ['nullable', 'string', 'max:255'],
        //     'room_number' => ['nullable', 'string', 'max:255'],
        //     'capacity' => ['nullable', 'string', 'max:255'],
        //     'teacher_id' => ['nullable', 'integer'],
        //     'grade_id' => ['nullable', 'integer'],
        // ]);

        // $section->update($data);
        return response()->json(['status' => 'success','message' => 'Class section updated', 'data' => $section]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['status' => 'error','message' => 'Unauthorized'], 401);
        }

        $section = ClassSection::find($id);
        if (!$section) {
            return response()->json(['status' => 'error','message' => 'Class section not found'], 404);
        }

        $section->delete();
        return response()->json(['status' => 'success','message' => 'Class section deleted']);
    }
}
