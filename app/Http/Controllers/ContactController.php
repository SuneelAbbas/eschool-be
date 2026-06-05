<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function send(ContactRequest $request): JsonResponse
    {
        ContactMessage::create($request->validated());

        return response()->json([
            'message' => 'Your message has been received. We\'ll get back to you within 2-4 business hours.',
        ]);
    }
}
