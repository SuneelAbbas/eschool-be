<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;

class SectionController extends Controller
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

        return response()->json(['status' => 'success', 'data' => Section::where('user_id', $user->id)->get()]);
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
        $section = Section::create($data);

        return response()->json(['status' => 'success', 'data' => $section], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['status' => 'error','message' => 'Unauthorized'], 401);
        }

        $section = Section::where('id', $id)->where('user_id', $user->id)->first();
        if (!$section) {
            return response()->json(['status' => 'error','message' => 'Section not found'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $section]);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['status' => 'error','message' => 'Unauthorized'], 401);
        }

        $section = Section::where('id', $id)->where('user_id', $user->id)->first();
        if (!$section) {
            return response()->json(['status' => 'error','message' => 'Section not found'], 404);
        }

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'institute_id' => ['nullable', 'integer'],
        ]);

        $section->update($data);
        return response()->json(['status' => 'success', 'data' => $section]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['status' => 'error','message' => 'Unauthorized'], 401);
        }

        $section = Section::where('id', $id)->where('user_id', $user->id)->first();
        if (!$section) {
            return response()->json(['status' => 'error','message' => 'Section not found'], 404);
        }

        $section->delete();
        return response()->json(['status' => 'success', 'message' => 'Section deleted']);
    }
}
