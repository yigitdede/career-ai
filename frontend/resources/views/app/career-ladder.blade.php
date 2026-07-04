@extends('app.layouts.app')

@section('title', __('panel.career_ladder.title'))

@section('content')
<div class="mx-auto max-w-5xl">
    <header class="mb-8">
        <h1 class="mb-1 text-2xl font-bold">{{ __('panel.career_ladder.title') }}</h1>
        <p class="text-slate-600 dark:text-slate-400">{{ __('panel.career_ladder.subtitle') }}</p>
        @if (! empty($fromApi))
            <p class="mt-3 inline-flex rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-300">
                {{ __('panel.career_ladder.from_cv_analysis') }}
            </p>
        @endif
    </header>

    @include('app.partials.career-ladder', ['hideSectionHeader' => true])
</div>
@endsection
