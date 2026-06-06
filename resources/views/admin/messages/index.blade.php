@extends('admin.layouts.app')

@section('title', 'Messages')
@section('page_title', 'Messages')
@section('sidebar.active', 'messages')

@section('content')
    <div class="rounded-xl border border-stone-200 bg-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-5 py-4 border-b border-stone-100">
            <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search messages..." class="w-56 rounded-lg border border-stone-200 bg-stone-50 py-2 pl-9 pr-3 text-xs text-stone-900 placeholder-stone-400 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Search</button>
                @if (request()->filled('search'))
                <a href="{{ route('admin.messages') }}" class="rounded-lg border border-stone-200 px-4 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-50">Clear</a>
                @endif
            </form>
            <span class="text-xs text-stone-500">{{ $messages->total() }} messages</span>
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
                        <th class="px-5 py-3">From</th>
                        <th class="px-5 py-3">Subject</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-sm">
                    @forelse ($messages as $m)
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">{{ strtoupper(substr($m->name, 0, 1)) }}</div>
                                <div>
                                    <p class="font-semibold text-stone-900">{{ $m->name }}</p>
                                    <p class="text-xs text-stone-500">{{ $m->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-stone-700 max-w-xs truncate">{{ $m->subject }}</td>
                        <td class="px-5 py-3.5 text-stone-500">{{ $m->created_at->format('M j, Y g:i A') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.messages.show', $m) }}" class="rounded-lg border border-stone-200 p-1.5 text-stone-400 hover:bg-stone-100" title="View">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.messages.destroy', $m) }}" class="inline" onsubmit="return confirm('Delete this message?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-stone-200 p-1.5 text-stone-400 hover:bg-red-50 hover:text-red-500" title="Delete">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-sm text-stone-500">No messages found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($messages->hasPages())
        <div class="px-5 py-3 border-t border-stone-100">
            {{ $messages->links() }}
        </div>
        @endif
    </div>
@endsection
