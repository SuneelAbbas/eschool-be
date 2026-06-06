<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class AdminPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('institutes')->latest()->paginate(15);
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features_list' => 'nullable|string',
        ]);

        $data['features'] = $data['features_list']
            ? array_filter(array_map('trim', explode("\n", $data['features_list'])))
            : [];
        unset($data['features_list']);

        Plan::create($data);

        return redirect()->route('admin.plans')->with('success', 'Plan created.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features_list' => 'nullable|string',
        ]);

        $data['features'] = $data['features_list']
            ? array_filter(array_map('trim', explode("\n", $data['features_list'])))
            : [];
        unset($data['features_list']);

        $plan->update($data);

        return redirect()->route('admin.plans')->with('success', 'Plan updated.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->institutes()->count() > 0) {
            return back()->with('error', 'Cannot delete plan with active institutes.');
        }

        $plan->delete();
        return redirect()->route('admin.plans')->with('success', 'Plan deleted.');
    }
}
