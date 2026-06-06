<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class AdminMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('subject', 'like', "%{$s}%");
            });
        }

        $messages = $query->paginate(15)->withQueryString();
        return view('admin.messages.index', compact('messages'));
    }

    public function show(ContactMessage $contactMessage)
    {
        return view('admin.messages.show', compact('contactMessage'));
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();
        return redirect()->route('admin.messages')->with('success', 'Message deleted.');
    }
}
