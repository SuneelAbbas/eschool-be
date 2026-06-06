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
            <p class="text-3xl font-black text-stone-900">12</p>
            <div class="flex items-center gap-3 mt-2">
                <span class="flex items-center gap-1 text-xs text-emerald-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    8 active
                </span>
                <span class="flex items-center gap-1 text-xs text-amber-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    3 pending
                </span>
                <span class="flex items-center gap-1 text-xs text-red-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    1 rejected
                </span>
            </div>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Users</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-stone-900">1,248</p>
            <p class="text-xs text-stone-500 mt-2">+48 this month</p>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Students</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100 text-violet-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-stone-900">8,572</p>
            <p class="text-xs text-stone-500 mt-2">Across all institutes</p>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Teachers</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-stone-900">426</p>
            <p class="text-xs text-stone-500 mt-2">+12 this month</p>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Revenue</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-stone-900">$45.2k</p>
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
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-xs font-bold text-emerald-700">AB</div>
                                    <div>
                                        <p class="font-semibold text-stone-900">Al-Bustan School</p>
                                        <p class="text-xs text-stone-500">Lahore</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-stone-700">Ali Raza</td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Gold</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-stone-500">Jan 15, 2026</td>
                            <td class="px-5 py-3.5">
                                <button class="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-xs font-bold text-amber-700">CI</div>
                                    <div>
                                        <p class="font-semibold text-stone-900">City Grammar School</p>
                                        <p class="text-xs text-stone-500">Karachi</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-stone-700">Sara Khan</td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-700">Basic</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="flex items-center gap-1.5 text-xs font-semibold text-amber-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Pending
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-stone-500">May 20, 2026</td>
                            <td class="px-5 py-3.5">
                                <button class="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100 text-xs font-bold text-red-700">IS</div>
                                    <div>
                                        <p class="font-semibold text-stone-900">Iqbal Science Academy</p>
                                        <p class="text-xs text-stone-500">Islamabad</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-stone-700">Usman Ali</td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-700">Basic</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="flex items-center gap-1.5 text-xs font-semibold text-red-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    Rejected
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-stone-500">Apr 8, 2026</td>
                            <td class="px-5 py-3.5">
                                <button class="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-xs font-bold text-emerald-700">LH</div>
                                    <div>
                                        <p class="font-semibold text-stone-900">The Horizon School</p>
                                        <p class="text-xs text-stone-500">Rawalpindi</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-stone-700">Fatima Noor</td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Gold</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-stone-500">Mar 2, 2026</td>
                            <td class="px-5 py-3.5">
                                <button class="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex items-center justify-between px-5 py-3 border-t border-stone-100">
                <p class="text-xs text-stone-500">Showing 4 of 12 institutes</p>
                <button class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">View All →</button>
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
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <div>
                            <p class="text-sm font-semibold text-stone-900">City Grammar School</p>
                            <p class="text-xs text-stone-500">Karachi • May 20, 2026</p>
                        </div>
                        <div class="flex gap-1">
                            <button class="rounded-lg bg-emerald-600 p-1.5 text-white hover:bg-emerald-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            <button class="rounded-lg bg-red-500 p-1.5 text-white hover:bg-red-600">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <div>
                            <p class="text-sm font-semibold text-stone-900">Green Valley School</p>
                            <p class="text-xs text-stone-500">Faisalabad • Today</p>
                        </div>
                        <div class="flex gap-1">
                            <button class="rounded-lg bg-emerald-600 p-1.5 text-white hover:bg-emerald-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            <button class="rounded-lg bg-red-500 p-1.5 text-white hover:bg-red-600">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Messages --}}
            <div class="rounded-xl border border-stone-200 bg-white">
                <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                    <h2 class="text-sm font-bold text-stone-900">Recent Messages</h2>
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">3 new</span>
                </div>
                <div class="divide-y divide-stone-100">
                    <div class="px-5 py-3.5">
                        <div class="flex items-start justify-between mb-1">
                            <p class="text-sm font-semibold text-stone-900">Ahmed Hassan</p>
                            <span class="text-[10px] text-stone-400">2h ago</span>
                        </div>
                        <p class="text-xs text-stone-500 truncate">Interested in the Gold plan for our school...</p>
                    </div>
                    <div class="px-5 py-3.5">
                        <div class="flex items-start justify-between mb-1">
                            <p class="text-sm font-semibold text-stone-900">Zainab Ali</p>
                            <span class="text-[10px] text-stone-400">5h ago</span>
                        </div>
                        <p class="text-xs text-stone-500 truncate">Need demo for our management team...</p>
                    </div>
                    <div class="px-5 py-3.5">
                        <div class="flex items-start justify-between mb-1">
                            <p class="text-sm font-semibold text-stone-900">Omar Farooq</p>
                            <span class="text-[10px] text-stone-400">1d ago</span>
                        </div>
                        <p class="text-xs text-stone-500 truncate">Can we get a custom plan for 500+ students?...</p>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-stone-100">
                    <button class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">View All Messages →</button>
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
                        <span class="text-xs font-semibold text-stone-900">142</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-stone-600">Queued Jobs</span>
                        <span class="text-xs font-semibold text-stone-900">3</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-stone-600">Failed Jobs (24h)</span>
                        <span class="text-xs font-semibold text-red-600">1</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-stone-600">PHP Version</span>
                        <span class="text-xs font-mono font-semibold text-stone-900">8.2</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
