@extends('admin.layouts.app')

@section('title', 'Settings')
@section('page_title', 'Settings')
@section('sidebar.active', 'settings')

@section('content')
    <div class="max-w-lg">
        <div class="rounded-xl border border-stone-200 bg-white p-6">
            <div class="flex flex-col gap-6">
                <div>
                    <h2 class="text-sm font-bold text-stone-900 mb-1">Application</h2>
                    <div class="rounded-xl bg-stone-50 px-4 py-3 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-stone-600">Name</span>
                            <span class="text-xs font-semibold text-stone-900">{{ config('app.name') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-stone-600">Environment</span>
                            <span class="text-xs font-semibold text-stone-900 capitalize">{{ app()->environment() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-stone-600">URL</span>
                            <span class="text-xs font-mono font-semibold text-stone-900">{{ config('app.url') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-stone-600">Laravel</span>
                            <span class="text-xs font-mono font-semibold text-stone-900">{{ app()->version() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-stone-600">PHP</span>
                            <span class="text-xs font-mono font-semibold text-stone-900">{{ phpversion() }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-sm font-bold text-stone-900 mb-1">Database</h2>
                    <div class="rounded-xl bg-stone-50 px-4 py-3 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-stone-600">Connection</span>
                            <span class="text-xs font-semibold text-stone-900">{{ config('database.default') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-stone-600">Name</span>
                            <span class="text-xs font-semibold text-stone-900">{{ config("database.connections." . config('database.default') . ".database") }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-sm font-bold text-stone-900 mb-1">Session</h2>
                    <div class="rounded-xl bg-stone-50 px-4 py-3 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-stone-600">Driver</span>
                            <span class="text-xs font-semibold text-stone-900">{{ config('session.driver') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-stone-600">Lifetime</span>
                            <span class="text-xs font-semibold text-stone-900">{{ config('session.lifetime') }} min</span>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-stone-400">
                    System settings are managed via the <code class="font-mono text-stone-600">.env</code> file.
                </p>
            </div>
        </div>
    </div>
@endsection
