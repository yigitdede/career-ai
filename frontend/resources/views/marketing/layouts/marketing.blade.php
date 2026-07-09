<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('marketing.brand')) — {{ __('marketing.meta_suffix') }}</title>
    <meta name="description" content="@yield('description', __('marketing.meta_description'))">
    <meta name="theme-color" content="#080b18">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wdth,wght@12..96,75..100,500..800&family=IBM+Plex+Mono:wght@500;600&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="marketing-shell antialiased">
    <a class="marketing-skip-link" href="#main-content">{{ __('marketing.skip_to_content') }}</a>
    @include('marketing.partials.header')

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="marketing-footer">
        <div class="marketing-container marketing-footer__inner">
            <div class="marketing-footer__brand">
                <a href="{{ route('home') }}" class="brand-lockup" aria-label="{{ __('marketing.brand') }}">
                    <span class="brand-mark brand-mark--small" aria-hidden="true">
                        <span></span><span></span><span></span>
                    </span>
                    <span>{{ __('marketing.brand') }}</span>
                </a>
                <p>{{ __('marketing.footer_tagline') }}</p>
            </div>
            <nav class="marketing-footer__links" aria-label="{{ __('marketing.footer_nav') }}">
                <a href="{{ route('features') }}">{{ __('marketing.nav.features') }}</a>
                <a href="{{ route('how-it-works') }}">{{ __('marketing.nav.how_it_works') }}</a>
                <a href="{{ route('careers') }}">{{ __('marketing.nav.careers') }}</a>
                <a href="{{ route('contact') }}">{{ __('marketing.nav.contact') }}</a>
            </nav>
            <p class="marketing-footer__meta">{{ __('marketing.footer') }}</p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
