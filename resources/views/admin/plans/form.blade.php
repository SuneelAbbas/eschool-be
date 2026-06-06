@extends('admin.layouts.app')

@section('title', isset($plan) ? 'Edit Plan' : 'New Plan')
@section('page_title', isset($plan) ? 'Edit Plan' : 'New Plan')
@section('sidebar.active', 'plans')

@section('content')
    <div class="max-w-lg">
        <div class="rounded-xl border border-stone-200 bg-white p-6">
            <form method="POST" action="{{ isset($plan) ? route('admin.plans.update', $plan) : route('admin.plans.store') }}">
                @csrf
                @if (isset($plan)) @method('PUT') @endif

                <div class="flex flex-col gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-1.5">Name</label>
                        <input type="text" name="name" value="{{ old('name', $plan->name ?? '') }}" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-900 outline-none transition-colors focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20" required>
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-1.5">Description</label>
                        <textarea name="description" rows="3" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-900 outline-none transition-colors focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">{{ old('description', $plan->description ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-1.5">Price</label>
                            <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price ?? '') }}" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-900 outline-none transition-colors focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20" required>
                            @error('price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-1.5">Duration (days)</label>
                            <input type="number" name="duration_days" value="{{ old('duration_days', $plan->duration_days ?? '') }}" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-900 outline-none transition-colors focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20" required>
                            @error('duration_days') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-1.5">Features (one per line)</label>
                        <textarea name="features_list" rows="5" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-900 outline-none transition-colors focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20" placeholder="Up to 500 students&#10;Exam management&#10;Fee management">{{ old('features_list', isset($plan) ? implode("\n", $plan->features ?? []) : '') }}</textarea>
                        <p class="text-xs text-stone-400 mt-1">Enter each feature on a new line.</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">
                            {{ isset($plan) ? 'Update Plan' : 'Create Plan' }}
                        </button>
                        <a href="{{ route('admin.plans') }}" class="text-sm font-semibold text-stone-500 hover:text-stone-700">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
