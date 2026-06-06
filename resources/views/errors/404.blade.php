<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-stone-50 flex items-center justify-center p-6">
    <div class="w-full max-w-sm text-center">
        <div class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col items-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-stone-100 text-3xl font-black text-stone-300 mb-6">
                    404
                </div>
                <h1 class="text-lg font-black text-stone-900 mb-2">Page not found</h1>
                <p class="text-xs text-stone-500 mb-8">The page you're looking for doesn't exist or has been moved.</p>
                <a href="/" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition-colors hover:bg-emerald-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Go home
                </a>
            </div>
        </div>
        <p class="mt-6 text-xs text-stone-400">&copy; {{ date('Y') }} {{ config('app.name', 'E-School ERP') }}. All rights reserved.</p>
    </div>
</body>
</html>
