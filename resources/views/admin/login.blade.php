<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="{{ config('app.name', 'E-School ERP') }} - Admin">
    <meta property="og:description" content="E-School ERP Admin Panel">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="{{ config('app.name', 'E-School ERP') }}">
    <title>{{ config('app.name', 'E-School ERP') }} - Admin</title>
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='black'/><text x='16' y='22' text-anchor='middle' font-family='Arial' font-weight='900' font-size='18' fill='%23eab308'>E</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-stone-50 flex items-center justify-center p-6">
    <div class="w-full max-w-sm">
        <div class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col items-center mb-8">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-600 text-xl font-black text-white mb-4">
                    E
                </div>
                <h1 class="text-lg font-black text-stone-900">Admin Login</h1>
                <p class="text-xs text-stone-400 mt-1">Sign in to your account</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3">
                    <p class="text-xs font-semibold text-red-700">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf
                <div class="flex flex-col gap-5">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-stone-700 mb-1.5">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com"
                               class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 placeholder-stone-400 outline-none transition-colors focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 @error('email') border-red-300 @enderror">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-semibold text-stone-700 mb-1.5">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password"
                               class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 placeholder-stone-400 outline-none transition-colors focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <button type="submit"
                            class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                        Sign in
                    </button>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-stone-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'E-School ERP') }}. All rights reserved.
        </p>
    </div>
</body>
</html>
