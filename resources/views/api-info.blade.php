<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="{{ config('app.name', 'E-School ERP') }} - API">
    <meta property="og:description" content="E-School ERP API Backend — school management platform">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="{{ config('app.name', 'E-School ERP') }}">
    <title>{{ config('app.name', 'E-School ERP') }} - API</title>
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='black'/><text x='16' y='22' text-anchor='middle' font-family='Arial' font-weight='900' font-size='18' fill='%23eab308'>E</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-stone-50 flex items-center justify-center p-6">
    <div class="w-full max-w-lg">
        <div class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-base font-black text-white">
                    E
                </div>
                <div>
                    <h1 class="text-lg font-black text-stone-900">{{ config('app.name', 'E-School ERP') }}</h1>
                    <p class="text-xs text-stone-400">API Backend</p>
                </div>
            </div>

            <div class="flex flex-col gap-3 mb-6">
                <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3">
                    <span class="text-sm font-medium text-stone-600">Status</span>
                    <span class="flex items-center gap-1.5 text-sm font-semibold text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Online
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-stone-50 px-4 py-3">
                    <span class="text-sm font-medium text-stone-600">Environment</span>
                    <span class="text-sm font-semibold text-stone-700 capitalize">{{ app()->environment() }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-stone-50 px-4 py-3">
                    <span class="text-sm font-medium text-stone-600">Version</span>
                    <span class="font-mono text-sm font-semibold text-stone-700">Laravel {{ app()->version() }}</span>
                </div>
            </div>

            <div class="mb-6">
                <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-stone-400">Quick Links</h2>
                <div class="flex flex-col gap-2">
                    <a href="https://es.thedigiorb.com"
                       class="flex items-center gap-3 rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50 hover:border-emerald-300">
                        <span class="text-base">🌐</span>
                        Frontend Portal
                        <span class="ml-auto text-stone-300">→</span>
                    </a>
                    <a href="/api/v1/institutes"
                       class="flex items-center gap-3 rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50 hover:border-emerald-300">
                        <span class="text-base">📋</span>
                        API Status
                        <span class="ml-auto text-stone-300">→</span>
                    </a>
                    <button onclick="fetch('/api/v1/institutes/1/status').then(r => r.json()).then(d => alert(JSON.stringify(d, null, 2)))"
                            class="flex w-full items-center gap-3 rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50 hover:border-emerald-300 text-left">
                        <span class="text-base">🔍</span>
                        Test API Status Endpoint
                        <span class="ml-auto text-stone-300">→</span>
                    </a>
                </div>
            </div>

            <div class="rounded-xl bg-stone-900 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span class="text-xs font-medium text-stone-400">Quick Test</span>
                </div>
                <pre class="font-mono text-xs leading-relaxed text-stone-300">curl {{ request()->getSchemeAndHttpHost() }}/api/v1/institutes/1/status</pre>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-stone-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'E-School ERP') }}. All rights reserved.
            <br>
            <a href="mailto:support@eschool.pk" class="underline underline-offset-2 hover:text-stone-600">support@eschool.pk</a>
        </p>
    </div>
</body>
</html>
