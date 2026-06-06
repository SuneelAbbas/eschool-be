@extends('admin.layouts.app')

@section('title', 'Users')
@section('page_title', 'Users')
@section('sidebar.active', 'users')

@section('content')
    <div class="rounded-xl border border-stone-200 bg-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-5 py-4 border-b border-stone-100">
            <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." class="w-48 rounded-lg border border-stone-200 bg-stone-50 py-2 pl-9 pr-3 text-xs text-stone-900 placeholder-stone-400 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <select name="user_type" class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-xs text-stone-600 outline-none focus:border-emerald-500">
                    <option value="">All Types</option>
                    @foreach ($userTypes as $type)
                    <option value="{{ $type }}" @selected(request('user_type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-xs text-stone-600 outline-none focus:border-emerald-500">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                </select>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Filter</button>
                @if (request()->anyFilled(['search', 'user_type', 'status']))
                <a href="{{ route('admin.users') }}" class="rounded-lg border border-stone-200 px-4 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-50">Clear</a>
                @endif
            </form>
            <span class="text-xs text-stone-500">{{ $users->total() }} users</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-stone-400 border-b border-stone-100">
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Institute</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Joined</th>
                        <th class="px-5 py-3">Last Login</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-sm">
                    @forelse ($users as $u)
                    @php
                        $typeColor = match($u->user_type) {
                            'super_admin' => 'violet',
                            'admin' => 'emerald',
                            'teacher' => 'blue',
                            'student' => 'amber',
                            'parent' => 'pink',
                            'accountant' => 'cyan',
                            'librarian' => 'stone',
                            default => 'stone',
                        };
                        $statusColor = $u->status === 'active' ? 'emerald' : ($u->status === 'suspended' ? 'red' : 'amber');
                        $initials = strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1));
                    @endphp
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-{{ $typeColor }}-100 text-xs font-bold text-{{ $typeColor }}-700">{{ $initials }}</div>
                                <div>
                                    <p class="font-semibold text-stone-900">{{ $u->first_name }} {{ $u->last_name }}</p>
                                    <p class="text-xs text-stone-500">{{ $u->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-full bg-{{ $typeColor }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $typeColor }}-700">{{ ucfirst(str_replace('_', ' ', $u->user_type)) }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-stone-700">{{ $u->institute?->name ?? '-' }}</td>
                        <td class="px-5 py-3.5">
                            <span class="flex items-center gap-1.5 text-xs font-semibold text-{{ $statusColor }}-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-{{ $statusColor }}-500"></span>
                                {{ ucfirst($u->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-stone-500">{{ $u->created_at->format('M j, Y') }}</td>
                        <td class="px-5 py-3.5 text-stone-500">{{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-stone-500">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
        <div class="px-5 py-3 border-t border-stone-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>
@endsection
