<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use Illuminate\Http\Request;

class AdminInstituteController extends Controller
{
    public function index(Request $request)
    {
        $query = Institute::with('adminUser', 'plan');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%")
                  ->orWhere('contact_email', 'like', "%{$s}%");
            });
        }

        $institutes = $query->latest()->paginate(15)->withQueryString();

        return view('admin.institutes.index', compact('institutes'));
    }

    public function approve(Institute $institute)
    {
        $institute->update(['status' => 'approved']);
        return back()->with('success', "{$institute->name} approved successfully.");
    }

    public function reject(Institute $institute)
    {
        $institute->update(['status' => 'rejected']);
        return back()->with('success', "{$institute->name} rejected.");
    }

    public function pending(Institute $institute)
    {
        $institute->update(['status' => 'pending']);
        return back()->with('success', "{$institute->name} moved back to pending.");
    }
}
