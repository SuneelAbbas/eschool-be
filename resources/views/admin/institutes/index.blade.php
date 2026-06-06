@extends('admin.layouts.app')

@section('title', 'Institutes')
@section('page_title', 'Institutes')
@section('sidebar.active', 'institutes')

@section('content')
    <div class="rounded-xl border border-stone-200 bg-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-5 py-4 border-b border-stone-100">
            <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search institutes..." class="w-56 rounded-lg border border-stone-200 bg-stone-50 py-2 pl-9 pr-3 text-xs text-stone-900 placeholder-stone-400 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <select name="status" class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-xs text-stone-600 outline-none focus:border-emerald-500">
                    <option value="">All Status</option>
                    <option value="approved" @selected(request('status') === 'approved')>Active</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Filter</button>
                @if (request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.institutes') }}" class="rounded-lg border border-stone-200 px-4 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-50">Clear</a>
                @endif
            </form>
            <span class="text-xs text-stone-500">{{ $institutes->total() }} institutes</span>
        </div>

        @if (session('success'))
        <div class="mx-5 mt-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3">
            <p class="text-xs font-semibold text-emerald-700">{{ session('success') }}</p>
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-stone-400 border-b border-stone-100">
                        <th class="px-5 py-3">Institute</th>
                        <th class="px-5 py-3">Contact</th>
                        <th class="px-5 py-3">Plan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Registered</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-sm">
                    @forelse ($institutes as $i)
                    @php
                        $color = match($i->status) {
                            'approved' => 'emerald',
                            'pending' => 'amber',
                            'rejected' => 'red',
                            default => 'stone',
                        };
                        $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $i->name), 0, 2));
                    @endphp
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-{{ $color }}-100 text-xs font-bold text-{{ $color }}-700">{{ $initials }}</div>
                                <div>
                                    <p class="font-semibold text-stone-900">{{ $i->name }}</p>
                                    <p class="text-xs text-stone-500">{{ $i->city }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-stone-700">{{ $i->contact_email }}</p>
                            <p class="text-xs text-stone-500">{{ $i->contact_phone ?? '-' }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-full bg-{{ $color }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $color }}-700">{{ $i->plan?->name ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="flex items-center gap-1.5 text-xs font-semibold text-{{ $color }}-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-{{ $color }}-500"></span>
                                {{ ucfirst($i->status === 'approved' ? 'active' : $i->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-stone-500">{{ $i->created_at->format('M j, Y') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1">
                                @if ($i->isPending())
                                <form method="POST" action="{{ route('admin.institutes.approve', $i) }}" class="inline">
                                    @csrf
                                    <button class="rounded-lg bg-emerald-600 p-1.5 text-white hover:bg-emerald-700" title="Approve">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.institutes.reject', $i) }}" class="inline">
                                    @csrf
                                    <button class="rounded-lg bg-red-500 p-1.5 text-white hover:bg-red-600" title="Reject">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.institutes.pending', $i) }}" class="inline">
                                    @csrf
                                    <button class="rounded-lg border border-stone-200 p-1.5 text-stone-400 hover:bg-stone-100" title="Move to Pending">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-stone-500">No institutes found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($institutes->hasPages())
        <div class="px-5 py-3 border-t border-stone-100">
            {{ $institutes->links() }}
        </div>
        @endif
    </div>
@endsection
