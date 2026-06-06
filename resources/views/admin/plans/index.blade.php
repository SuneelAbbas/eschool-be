@extends('admin.layouts.app')

@section('title', 'Plans')
@section('page_title', 'Plans')
@section('sidebar.active', 'plans')

@section('content')
    <div class="rounded-xl border border-stone-200 bg-white">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
            <span class="text-xs text-stone-500">{{ $plans->total() }} plans</span>
            <a href="{{ route('admin.plans.create') }}" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">+ New Plan</a>
        </div>

        @if (session('success'))
        <div class="mx-5 mt-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3">
            <p class="text-xs font-semibold text-emerald-700">{{ session('success') }}</p>
        </div>
        @endif

        @if (session('error'))
        <div class="mx-5 mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3">
            <p class="text-xs font-semibold text-red-700">{{ session('error') }}</p>
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-stone-400 border-b border-stone-100">
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Price</th>
                        <th class="px-5 py-3">Duration</th>
                        <th class="px-5 py-3">Institutes</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-sm">
                    @forelse ($plans as $p)
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-stone-900">{{ $p->name }}</p>
                            @if ($p->description)
                            <p class="text-xs text-stone-500 max-w-xs truncate">{{ $p->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-stone-900">{{ number_format($p->price, 2) }}</td>
                        <td class="px-5 py-3.5 text-stone-700">{{ $p->duration_days }} days</td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">{{ $p->institutes_count }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.plans.edit', $p) }}" class="rounded-lg border border-stone-200 p-1.5 text-stone-400 hover:bg-stone-100" title="Edit">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.plans.destroy', $p) }}" class="inline" onsubmit="return confirm('Delete this plan?')">
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
                        <td colspan="5" class="px-5 py-8 text-center text-sm text-stone-500">No plans found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($plans->hasPages())
        <div class="px-5 py-3 border-t border-stone-100">
            {{ $plans->links() }}
        </div>
        @endif
    </div>
@endsection
