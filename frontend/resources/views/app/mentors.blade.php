@extends('app.layouts.app')

@section('title', __('panel.mentors.title'))

@section('content')
<div class="mx-auto max-w-6xl">
    <header class="mb-8">
        <h1 class="mb-1 text-2xl font-bold">{{ __('panel.mentors.title') }}</h1>
        <p class="text-slate-600 dark:text-slate-400">{{ __('panel.mentors.subtitle') }}</p>
    </header>

    @if ($mentors['packages'] === [] && $mentors['experts'] === [])
        <section data-mentors-empty class="panel-card p-10 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                <i data-lucide="users" class="h-5 w-5 text-slate-500" aria-hidden="true"></i>
            </div>
            <h2 class="font-semibold text-slate-950 dark:text-white">{{ __('panel.mentors.empty_title') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('panel.mentors.empty_description') }}</p>
        </section>
    @else
        <section class="mb-8 grid gap-4 md:grid-cols-3">
            @foreach ($mentors['packages'] as $package)
                <article class="panel-card p-5">
                    <h2 class="font-semibold">{{ $package['name'] }}</h2>
                    <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $package['price'] }}</p>
                    <p class="panel-muted text-sm">{{ $package['delivery'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            @foreach ($mentors['experts'] as $expert)
                <article class="panel-card p-5">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-semibold">{{ $expert['name'] }}</h2>
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $expert['title'] }} · {{ $expert['company'] }}</p>
                        </div>
                        <span class="rounded-full bg-amber-500/15 px-2 py-1 text-xs text-amber-700 dark:text-amber-300">★ {{ $expert['rating'] }}</span>
                    </div>
                    <p class="text-sm"><strong>{{ __('panel.mentors.focus') }}:</strong> {{ $expert['focus'] }}</p>
                    <p class="panel-muted mt-2 text-sm">{{ __('panel.mentors.next_slot') }}: {{ $expert['slots'] }}</p>
                </article>
            @endforeach
        </section>
    @endif
</div>
@endsection
