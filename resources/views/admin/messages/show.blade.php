@extends('admin.layouts.app')

@section('title', 'Message')
@section('page_title', 'Message')
@section('sidebar.active', 'messages')

@section('content')
    <div class="max-w-2xl">
        <div class="rounded-xl border border-stone-200 bg-white">
            <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
                <a href="{{ route('admin.messages') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">&larr; Back to Messages</a>
                <form method="POST" action="{{ route('admin.messages.destroy', $contactMessage) }}" onsubmit="return confirm('Delete this message?')">
                    @csrf @method('DELETE')
                    <button class="text-xs font-semibold text-red-600 hover:text-red-700">Delete</button>
                </form>
            </div>

            <div class="p-5 space-y-5">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">{{ strtoupper(substr($contactMessage->name, 0, 1)) }}</div>
                    <div>
                        <h2 class="text-lg font-bold text-stone-900">{{ $contactMessage->name }}</h2>
                        <p class="text-sm text-stone-500">{{ $contactMessage->email }}</p>
                    </div>
                </div>

                <div class="rounded-xl bg-stone-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400 mb-1">Subject</p>
                    <p class="text-sm font-medium text-stone-900">{{ $contactMessage->subject }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400 mb-2">Message</p>
                    <p class="text-sm text-stone-700 leading-relaxed">{{ $contactMessage->message }}</p>
                </div>

                <div class="text-xs text-stone-400">
                    Received {{ $contactMessage->created_at->format('F j, Y g:i A') }}
                </div>
            </div>
        </div>
    </div>
@endsection
