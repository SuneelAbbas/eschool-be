<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankAccountRequest;
use App\Http\Resources\BankAccountResource;
use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = BankAccount::where('institute_id', $user->institute_id);

        if (!$request->boolean('include_inactive')) {
            $query->where('is_active', true);
        }

        $bankAccounts = $query->orderBy('is_default', 'desc')
            ->orderBy('bank_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => BankAccountResource::collection($bankAccounts),
        ]);
    }

    public function store(BankAccountRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['institute_id'] = $user->institute_id;

        if (!empty($data['is_default'])) {
            BankAccount::where('institute_id', $user->institute_id)
                ->update(['is_default' => false]);
        }

        $bankAccount = BankAccount::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Bank account created successfully',
            'data' => new BankAccountResource($bankAccount),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $bankAccount = BankAccount::where('id', $id)
            ->where('institute_id', $user->institute_id)
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Bank account not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new BankAccountResource($bankAccount),
        ]);
    }

    public function update(BankAccountRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $bankAccount = BankAccount::where('id', $id)
            ->where('institute_id', $user->institute_id)
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Bank account not found',
            ], 404);
        }

        $data = $request->validated();

        if (!empty($data['is_default'])) {
            BankAccount::where('institute_id', $user->institute_id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $bankAccount->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Bank account updated successfully',
            'data' => new BankAccountResource($bankAccount),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $bankAccount = BankAccount::where('id', $id)
            ->where('institute_id', $user->institute_id)
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Bank account not found',
            ], 404);
        }

        $bankAccount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bank account deleted successfully',
        ]);
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $bankAccount = BankAccount::where('id', $id)
            ->where('institute_id', $user->institute_id)
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Bank account not found',
            ], 404);
        }

        BankAccount::where('institute_id', $user->institute_id)
            ->update(['is_default' => false]);

        $bankAccount->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default bank account updated',
            'data' => new BankAccountResource($bankAccount),
        ]);
    }
}
