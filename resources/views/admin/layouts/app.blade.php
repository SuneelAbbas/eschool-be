<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'E-School ERP')) - Admin</title>
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='black'/><text x='16' y='22' text-anchor='middle' font-family='Arial' font-weight='900' font-size='18' fill='%23eab308'>E</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-stone-50">
    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside class="hidden lg:flex lg:flex-col w-64 bg-stone-900 border-r border-stone-800">
            <div class="flex items-center gap-3 px-6 h-16 border-b border-stone-800">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-sm font-black text-white">
                    E
                </div>
                <div>
                    <p class="text-sm font-bold text-white">E-School ERP</p>
                    <p class="text-[10px] font-medium text-stone-500">Super Admin</p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors @if(View::hasSection('sidebar.active') && View::getSection('sidebar.active') === 'dashboard') bg-emerald-600 text-white @else text-stone-400 hover:text-white hover:bg-stone-800 @endif">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.institutes') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors @if(View::hasSection('sidebar.active') && View::getSection('sidebar.active') === 'institutes') bg-emerald-600 text-white @else text-stone-400 hover:text-white hover:bg-stone-800 @endif">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Institutes
                </a>
                <a href="{{ route('admin.users') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors @if(View::hasSection('sidebar.active') && View::getSection('sidebar.active') === 'users') bg-emerald-600 text-white @else text-stone-400 hover:text-white hover:bg-stone-800 @endif">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    Users
                </a>
                <a href="{{ route('admin.plans') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors @if(View::hasSection('sidebar.active') && View::getSection('sidebar.active') === 'plans') bg-emerald-600 text-white @else text-stone-400 hover:text-white hover:bg-stone-800 @endif">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Plans
                </a>
                <a href="{{ route('admin.messages') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors @if(View::hasSection('sidebar.active') && View::getSection('sidebar.active') === 'messages') bg-emerald-600 text-white @else text-stone-400 hover:text-white hover:bg-stone-800 @endif">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Messages
                </a>
                <a href="{{ route('admin.roles') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors @if(View::hasSection('sidebar.active') && View::getSection('sidebar.active') === 'roles') bg-emerald-600 text-white @else text-stone-400 hover:text-white hover:bg-stone-800 @endif">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Roles
                </a>
                <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors @if(View::hasSection('sidebar.active') && View::getSection('sidebar.active') === 'settings') bg-emerald-600 text-white @else text-stone-400 hover:text-white hover:bg-stone-800 @endif">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>
            </nav>

            <div class="p-4 border-t border-stone-800 space-y-1">
                <a href="/api-info" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-stone-500 transition-colors hover:text-stone-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    API Info
                </a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-stone-500 transition-colors hover:text-red-400 hover:bg-stone-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Top Navbar --}}
            <header class="h-16 bg-white border-b border-stone-200 flex items-center justify-between px-6 shrink-0">
                <div class="flex items-center gap-3">
                    <button class="lg:hidden p-2 rounded-lg text-stone-500 hover:bg-stone-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-base font-bold text-stone-900">@yield('page_title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative hidden sm:block">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" placeholder="Search..." class="w-56 rounded-lg border border-stone-200 bg-stone-50 py-2 pl-9 pr-3 text-xs text-stone-900 placeholder-stone-400 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <button class="relative p-2 rounded-lg text-stone-500 hover:bg-stone-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-red-500 border-2 border-white"></span>
                    </button>

                    <div class="flex items-center gap-3 pl-4 border-l border-stone-200">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">
                            SA
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-sm font-medium text-stone-900 leading-tight">Super Admin</p>
                            <p class="text-[11px] text-stone-500">admin@eschool.pk</p>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
