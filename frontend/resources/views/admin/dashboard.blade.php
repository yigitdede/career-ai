@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="mx-auto max-w-7xl">
    <header class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="mb-1 text-2xl font-bold">Admin Dashboard</h1>
            <p class="text-slate-600 dark:text-slate-400">Cohort, öğrenci, readiness, iş radarı ve gelir odaklı modüllerin demo yönetim paneli.</p>
        </div>
        <a href="{{ route('panel.dashboard') }}" class="panel-btn-secondary">Öğrenci panelini aç</a>
    </header>

    <section class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <article class="panel-card p-5">
                <p class="panel-muted text-sm">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $stat['value'] }}</p>
                <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">{{ $stat['trend'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="mb-8 grid gap-6 lg:grid-cols-[1fr_24rem]">
        <div class="panel-card p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold">Cohort sağlığı</h2>
                <a href="{{ route('admin.cohorts') }}" class="text-xs font-medium text-emerald-600 hover:underline dark:text-emerald-400">Detay →</a>
            </div>
            <div class="space-y-3">
                @foreach ($cohorts as $cohort)
                    <article class="panel-entry !space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $cohort['name'] }}</p>
                                <p class="panel-muted text-xs">{{ $cohort['students'] }} öğrenci · Odak: {{ $cohort['focus'] }}</p>
                            </div>
                            <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-sm font-semibold text-emerald-700 dark:text-emerald-300">%{{ $cohort['readiness'] }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-700"><div class="h-full rounded-full bg-emerald-500" style="width: {{ $cohort['readiness'] }}%"></div></div>
                    </article>
                @endforeach
            </div>
        </div>

        <aside class="panel-card p-5">
            <h2 class="mb-3 text-lg font-semibold">Admin → Panel bağlantıları</h2>
            <p class="panel-muted mb-4 text-sm">Her admin modülü öğrencinin gördüğü `/panel` sayfasına bağlandı. Demo kararları öğrenci deneyimindeki ilgili ekrana iner.</p>
            <div class="space-y-2">
                <a href="{{ route('admin.skill-passport') }}" class="panel-outline-btn block text-center">Kanıt onay kuyruğu</a>
                <a href="{{ route('admin.job-radar') }}" class="panel-outline-btn block text-center">İlan sinyali yönetimi</a>
                <a href="{{ route('admin.mentors') }}" class="panel-outline-btn block text-center">Mentor gelir ekranı</a>
            </div>
        </aside>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($modules as $module)
            <article class="panel-card flex flex-col p-5">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">{{ $module['title'] }}</h2>
                        <p class="panel-muted mt-1 text-sm">{{ $module['desc'] }}</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs dark:bg-slate-800">{{ $module['metric'] }}</span>
                </div>
                <div class="mt-auto flex flex-wrap gap-2 pt-3">
                    <a href="{{ route($module['admin_route']) }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Yönet</a>
                    <a href="{{ route($module['panel_route']) }}" class="panel-btn-secondary">Panel karşılığı</a>
                </div>
            </article>
        @endforeach
    </section>
</div>
@endsection
