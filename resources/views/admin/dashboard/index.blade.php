@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('sidebar.active', 'dashboard')

@section('content')
    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Institutes</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-stone-900">{{ $totalInstitutes }}</p>
            <div class="flex items-center gap-3 mt-2">
                @if ($activeInstitutes > 0)
                <span class="flex items-center gap-1 text-xs text-emerald-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ $activeInstitutes }} active
                </span>
                @endif
                @if ($pendingInstitutes > 0)
                <span class="flex items-center gap-1 text-xs text-amber-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    {{ $pendingInstitutes }} pending
                </span>
                @endif
                @if ($rejectedInstitutes > 0)
                <span class="flex items-center gap-1 text-xs text-red-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    {{ $rejectedInstitutes }} rejected
                </span>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Users</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-stone-900">{{ number_format($totalUsers) }}</p>
            <p class="text-xs text-stone-500 mt-2">+{{ $usersThisMonth }} this month</p>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Students</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100 text-violet-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-stone-900">{{ number_format($totalStudents) }}</p>
            <p class="text-xs text-stone-500 mt-2">Across all institutes</p>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Teachers</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-stone-900">{{ number_format($totalTeachers) }}</p>
            <p class="text-xs text-stone-500 mt-2">+{{ $teachersThisMonth }} this month</p>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Revenue</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-stone-900">${{ number_format($revenueThisYear) }}</p>
            <p class="text-xs text-stone-500 mt-2">Collected this year</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Institutes Table --}}
        <div class="lg:col-span-2 rounded-xl border border-stone-200 bg-white">
            <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <h2 class="text-sm font-bold text-stone-900">All Institutes</h2>
                <div class="flex items-center gap-2">
                    <select class="rounded-lg border border-stone-200 bg-white px-3 py-1.5 text-xs text-stone-600 outline-none focus:border-emerald-500">
                        <option>All Status</option>
                        <option>Active</option>
                        <option>Pending</option>
                        <option>Rejected</option>
                    </select>
                    <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">+ New</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-stone-400 border-b border-stone-100">
                            <th class="px-5 py-3">Institute</th>
                            <th class="px-5 py-3">Admin</th>
                            <th class="px-5 py-3">Plan</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Created</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-sm">
                        @forelse ($institutes as $i)
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-{{ $i->status_color }}-100 text-xs font-bold text-{{ $i->status_color }}-700">{{ $i->initials }}</div>
                                    <div>
                                        <p class="font-semibold text-stone-900">{{ $i->name }}</p>
                                        <p class="text-xs text-stone-500">{{ $i->city }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-stone-700">{{ $i->admin_name }}</td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full bg-{{ $i->status_color === 'emerald' ? 'emerald' : 'stone' }}-50 px-2.5 py-0.5 text-xs font-medium text-{{ $i->status_color === 'emerald' ? 'emerald' : 'stone' }}-700">{{ $i->plan_name }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="flex items-center gap-1.5 text-xs font-semibold text-{{ $i->status_color }}-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-{{ $i->status_color }}-500"></span>
                                    {{ ucfirst($i->status === 'approved' ? 'active' : $i->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-stone-500">{{ $i->created_at }}</td>
                            <td class="px-5 py-3.5">
                                <button class="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                </button>
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
            <div class="flex items-center justify-between px-5 py-3 border-t border-stone-100">
                <p class="text-xs text-stone-500">Showing {{ $institutes->count() }} of {{ $totalInstitutes }} institutes</p>
                <a href="{{ route('admin.institutes') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">View All →</a>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">
            {{-- Pending Approvals --}}
            <div class="rounded-xl border border-stone-200 bg-white">
                <div class="px-5 py-4 border-b border-stone-100">
                    <h2 class="text-sm font-bold text-stone-900">Pending Approvals</h2>
                </div>
                <div class="divide-y divide-stone-100">
                    @forelse ($pendingList as $p)
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <div>
                            <p class="text-sm font-semibold text-stone-900">{{ $p->name }}</p>
                            <p class="text-xs text-stone-500">{{ $p->city }} • {{ $p->created_at }}</p>
                        </div>
                        <div class="flex gap-1">
                            <form method="POST" action="{{ route('admin.institutes.approve', $p->id) }}" class="inline">
                                @csrf
                                <button class="rounded-lg bg-emerald-600 p-1.5 text-white hover:bg-emerald-700">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.institutes.reject', $p->id) }}" class="inline">
                                @csrf
                                <button class="rounded-lg bg-red-500 p-1.5 text-white hover:bg-red-600">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-8 text-center text-sm text-stone-500">No pending approvals.</div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Messages --}}
            <div class="rounded-xl border border-stone-200 bg-white">
                <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                    <h2 class="text-sm font-bold text-stone-900">Recent Messages</h2>
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">{{ $recentMessages->count() }} new</span>
                </div>
                <div class="divide-y divide-stone-100">
                    @forelse ($recentMessages as $m)
                    <div class="px-5 py-3.5">
                        <div class="flex items-start justify-between mb-1">
                            <p class="text-sm font-semibold text-stone-900">{{ $m->name }}</p>
                            <span class="text-[10px] text-stone-400">{{ $m->time }}</span>
                        </div>
                        <p class="text-xs text-stone-500 truncate">{{ $m->subject }}</p>
                    </div>
                    @empty
                    <div class="px-5 py-8 text-center text-sm text-stone-500">No messages yet.</div>
                    @endforelse
                </div>
                <div class="px-5 py-3 border-t border-stone-100">
                    <a href="{{ route('admin.messages') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">View All Messages →</a>
                </div>
            </div>

            {{-- System Health --}}
            <div class="rounded-xl border border-stone-200 bg-white">
                <div class="px-5 py-4 border-b border-stone-100">
                    <h2 class="text-sm font-bold text-stone-900">System Health</h2>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-stone-600">Active Sessions</span>
                        <span class="text-xs font-semibold text-stone-900">{{ $activeSessions }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-stone-600">Queued Jobs</span>
                        <span class="text-xs font-semibold text-stone-900">{{ $queuedJobs }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-stone-600">Failed Jobs (24h)</span>
                        <span class="text-xs font-semibold text-{{ $failedJobs24h > 0 ? 'red' : 'stone' }}-600">{{ $failedJobs24h }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-stone-600">PHP Version</span>
                        <span class="text-xs font-mono font-semibold text-stone-900">{{ $phpVersion }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
