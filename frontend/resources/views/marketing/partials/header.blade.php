@php
    $locale = app()->getLocale();
    $infoActive = request()->routeIs('faq', 'blog', 'about', 'gallery', 'pricing', 'contact');
@endphp

<header class="marketing-header" data-marketing-header>
    <div class="marketing-container marketing-header__inner">
        <a href="{{ route('home') }}" class="brand-lockup" aria-label="{{ __('marketing.brand') }}">
            <span class="brand-mark" aria-hidden="true">
                <span></span><span></span><span></span>
            </span>
            <span>{{ __('marketing.brand') }}</span>
        </a>

        <nav class="marketing-nav" aria-label="{{ __('marketing.primary_nav') }}">
            <a href="{{ route('home') }}" @class(['is-active' => request()->routeIs('home')])>
                {{ __('marketing.nav.home') }}
            </a>
            <a href="{{ route('features') }}" @class(['is-active' => request()->routeIs('features')])>
                {{ __('marketing.nav.features') }}
            </a>
            <a href="{{ route('careers') }}" @class(['is-active' => request()->routeIs('careers')])>
                {{ __('marketing.nav.careers') }}
            </a>
            <a href="{{ route('how-it-works') }}" @class(['is-active' => request()->routeIs('how-it-works')])>
                {{ __('marketing.nav.how_it_works') }}
            </a>
            <a href="{{ route('bootcamp') }}" @class(['is-active' => request()->routeIs('bootcamp')])>
                {{ __('marketing.nav.bootcamp') }}
            </a>

            <details class="marketing-menu marketing-menu--info">
                <summary @class(['is-active' => $infoActive])>
                    {{ __('marketing.nav.info') }}
                    <i data-lucide="chevron-down" aria-hidden="true"></i>
                </summary>
                <div class="marketing-menu__panel">
                    <a href="{{ route('pricing') }}">{{ __('marketing.nav.pricing') }}</a>
                    <a href="{{ route('gallery') }}">{{ __('marketing.nav.gallery') }}</a>
                    <a href="{{ route('faq') }}">{{ __('marketing.nav.faq') }}</a>
                    <a href="{{ route('blog') }}">{{ __('marketing.nav.blog') }}</a>
                    <a href="{{ route('about') }}">{{ __('marketing.nav.about') }}</a>
                    <a href="{{ route('contact') }}">{{ __('marketing.nav.contact') }}</a>
                </div>
            </details>
        </nav>

        <div class="marketing-header__actions">
            <details class="marketing-menu marketing-menu--locale">
                <summary aria-label="{{ __('marketing.nav.language') }}">
                    <span>{{ strtoupper($locale) }}</span>
                    <i data-lucide="chevron-down" aria-hidden="true"></i>
                </summary>
                <div class="marketing-menu__panel marketing-menu__panel--right">
                    <a href="{{ route('marketing.locale', 'tr') }}" @class(['is-current' => $locale === 'tr'])>
                        <span>Türkçe</span><strong>TR</strong>
                    </a>
                    <a href="{{ route('marketing.locale', 'en') }}" @class(['is-current' => $locale === 'en'])>
                        <span>English</span><strong>EN</strong>
                    </a>
                </div>
            </details>

            <a href="{{ route('login') }}" class="marketing-login">{{ __('marketing.nav.login') }}</a>
            <a href="{{ route('register') }}" class="marketing-button marketing-button--compact">
                {{ __('marketing.nav.register') }}
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </a>

            <details class="marketing-mobile-nav" data-mobile-nav>
                <summary aria-label="{{ __('marketing.mobile_menu') }}">
                    <i class="marketing-mobile-nav__open" data-lucide="menu" aria-hidden="true"></i>
                    <i class="marketing-mobile-nav__close" data-lucide="x" aria-hidden="true"></i>
                </summary>
                <div class="marketing-mobile-nav__panel">
                    <a href="{{ route('home') }}">{{ __('marketing.nav.home') }}</a>
                    <a href="{{ route('features') }}">{{ __('marketing.nav.features') }}</a>
                    <a href="{{ route('careers') }}">{{ __('marketing.nav.careers') }}</a>
                    <a href="{{ route('how-it-works') }}">{{ __('marketing.nav.how_it_works') }}</a>
                    <a href="{{ route('bootcamp') }}">{{ __('marketing.nav.bootcamp') }}</a>
                    <a href="{{ route('pricing') }}">{{ __('marketing.nav.pricing') }}</a>
                    <a href="{{ route('gallery') }}">{{ __('marketing.nav.gallery') }}</a>
                    <a href="{{ route('about') }}">{{ __('marketing.nav.about') }}</a>
                    <a href="{{ route('contact') }}">{{ __('marketing.nav.contact') }}</a>
                    <a href="{{ route('login') }}">{{ __('marketing.nav.login') }}</a>
                </div>
            </details>
        </div>
    </div>
</header>
