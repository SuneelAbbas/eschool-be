@extends('admin.layouts.app')

@section('title', $role->name)
@section('page_title', $role->name)
@section('sidebar.active', 'roles')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Role Info --}}
        <div class="rounded-xl border border-stone-200 bg-white">
            <div class="px-5 py-4 border-b border-stone-100">
                <h2 class="text-sm font-bold text-stone-900">Role Details</h2>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Name</p>
                    <p class="text-sm font-semibold text-stone-900 mt-1">{{ $role->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Slug</p>
                    <p class="text-sm font-mono text-stone-700 mt-1">{{ $role->slug }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Users</p>
                    <p class="text-sm font-semibold text-stone-900 mt-1">{{ $role->users->count() }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">System Role</p>
                    <p class="text-sm mt-1">{{ $role->is_system ? 'Yes' : 'No' }}</p>
                </div>
                @if ($role->description)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Description</p>
                    <p class="text-sm text-stone-700 mt-1">{{ $role->description }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Permissions --}}
        <div class="lg:col-span-2 rounded-xl border border-stone-200 bg-white">
            <div class="px-5 py-4 border-b border-stone-100">
                <h2 class="text-sm font-bold text-stone-900">Permissions ({{ $role->permissions->count() }})</h2>
            </div>
            <div class="p-5 space-y-5">
                @foreach ($permissionGroups as $group => $perms)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400 mb-2">{{ $group }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($perms as $perm)
                        @php
                            $has = $role->permissions->contains('id', $perm['id']);
                        @endphp
                        <span class="rounded-full px-3 py-1 text-xs font-medium {{ $has ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-400' }}">
                            {{ $perm['name'] }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
