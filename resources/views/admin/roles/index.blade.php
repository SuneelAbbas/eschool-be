@extends('admin.layouts.app')

@section('title', 'Roles')
@section('page_title', 'Roles')
@section('sidebar.active', 'roles')

@section('content')
    <div class="rounded-xl border border-stone-200 bg-white">
        <div class="px-5 py-4 border-b border-stone-100">
            <span class="text-xs text-stone-500">{{ $roles->total() }} roles</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-stone-400 border-b border-stone-100">
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Users</th>
                        <th class="px-5 py-3">Permissions</th>
                        <th class="px-5 py-3">System</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-sm">
                    @forelse ($roles as $r)
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-stone-900">{{ $r->name }}</p>
                            <p class="text-xs text-stone-500">{{ $r->description ?? ucfirst($r->slug) }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ $r->users_count }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-medium text-violet-700">{{ $r->permissions_count }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if ($r->is_system)
                            <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">System</span>
                            @else
                            <span class="text-xs text-stone-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('admin.roles.show', $r) }}" class="rounded-lg border border-stone-200 p-1.5 text-stone-400 hover:bg-stone-100 inline-block" title="View">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-sm text-stone-500">No roles found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($roles->hasPages())
        <div class="px-5 py-3 border-t border-stone-100">
            {{ $roles->links() }}
        </div>
        @endif
    </div>
@endsection
