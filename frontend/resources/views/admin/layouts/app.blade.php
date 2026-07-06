<!DOCTYPE html>
<html lang="tr" class="dark h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — CareerTalent AI</title>
    <script>
        (function () {
            var t = localStorage.getItem('panel-theme');
            if (t === 'light') document.documentElement.classList.remove('dark');
            else document.documentElement.classList.add('dark');
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100"
    x-data="{
        theme: localStorage.getItem('panel-theme') || 'dark',
        init() { this.applyTheme(); },
        applyTheme() {
            document.documentElement.classList.toggle('dark', this.theme === 'dark');
            localStorage.setItem('panel-theme', this.theme);
        },
        toggleTheme() {
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
            this.applyTheme();
        }
    }">
    <div class="panel-shell">
        <aside class="panel-sidebar" aria-label="CareerTalent Admin">
            <div class="flex h-full min-h-0 flex-col p-6">
                <a href="{{ route('admin.dashboard') }}" class="mb-6 block shrink-0 text-lg font-bold text-emerald-600 dark:text-emerald-400">CareerTalent Admin</a>
                <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain text-sm">
                    @include('admin.partials.sidebar-nav')
                </nav>
                <div class="mt-auto space-y-2 border-t border-slate-200 pt-4 dark:border-slate-800">
                    <a href="{{ route('panel.dashboard') }}" class="panel-nav-link">↗ <span>Öğrenci paneli</span></a>
                    <a href="{{ route('home') }}" class="panel-nav-link">⌂ <span>Tanıtım sitesi</span></a>
                </div>
            </div>
        </aside>

        <div class="panel-main">
            <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-white/80 px-6 py-3 backdrop-blur dark:border-slate-800 dark:bg-slate-950/80 md:px-10">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Sprint 2 demo admin</p>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">/admin → /panel bağlantılı yönetim yüzeyi</p>
                </div>
                <button type="button" @click="toggleTheme()" class="panel-btn-secondary">
                    <span x-text="theme === 'dark' ? 'Light' : 'Dark'"></span>
                </button>
            </header>
            <main class="flex-1 p-6 md:p-10">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
