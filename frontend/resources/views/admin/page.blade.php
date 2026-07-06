@extends('admin.layouts.app')

@section('title', $page['title'])

@section('content')
<div class="mx-auto max-w-7xl">
    <header class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="mb-1 text-2xl font-bold">{{ $page['title'] }}</h1>
            <p class="max-w-3xl text-slate-600 dark:text-slate-400">{{ $page['subtitle'] }}</p>
        </div>
        <a href="{{ route($page['panel_route']) }}" class="panel-btn-secondary">İlgili öğrenci paneli →</a>
    </header>

    <section class="mb-8 grid gap-4 md:grid-cols-3">
        @foreach ($page['actions'] as $action)
            <button type="button" class="panel-card p-5 text-left transition hover:border-emerald-500/50 hover:bg-emerald-500/5">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $action }}</p>
                <p class="panel-muted mt-1 text-xs">Demo aksiyon · backend kaydı Sprint 3</p>
            </button>
        @endforeach
    </section>

    <section class="panel-card overflow-hidden">
        <div class="border-b border-slate-200 p-5 dark:border-slate-800">
            <h2 class="text-lg font-semibold">Yönetim listesi</h2>
            <p class="panel-muted text-sm">Admin kararı, öğrencinin ilgili `/panel` deneyimine bağlanacak şekilde tasarlandı.</p>
        </div>
        <div class="divide-y divide-slate-200 dark:divide-slate-800">
            @foreach ($page['rows'] as $row)
                <article class="grid gap-4 p-5 md:grid-cols-[1fr_8rem_10rem_14rem] md:items-center">
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $row['name'] }}</p>
                        <p class="panel-muted mt-1 text-sm">{{ $row['meta'] }}</p>
                    </div>
                    <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $row['score'] }}</p>
                    <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $row['status'] }}</span>
                    <p class="text-sm text-slate-700 dark:text-slate-300">{{ $row['next'] }}</p>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection
