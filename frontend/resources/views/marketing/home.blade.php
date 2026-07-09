@extends('marketing.layouts.marketing')

@section('title', __('marketing.home.title'))
@section('description', __('marketing.home.meta_description'))

@section('content')
<section class="career-hero" data-career-trajectory>
    <div class="career-hero__glow career-hero__glow--green" aria-hidden="true"></div>
    <div class="career-hero__glow career-hero__glow--aqua" aria-hidden="true"></div>
    <div class="marketing-container career-hero__grid">
        <div class="career-hero__copy" data-reveal="hero">
            <p class="marketing-kicker">
                <span aria-hidden="true"></span>
                {{ __('marketing.home.eyebrow') }}
            </p>
            <h1>
                {{ __('marketing.home.headline') }}
                <span class="career-hero__accent">{{ __('marketing.home.headline_highlight') }}</span>
                @if(__('marketing.home.headline_end'))
                    {{ __('marketing.home.headline_end') }}
                @endif
            </h1>
            <p class="career-hero__subtitle">{{ __('marketing.home.subtitle') }}</p>

            <div class="career-hero__actions">
                <a href="{{ route('register') }}" class="marketing-button marketing-button--primary">
                    {{ __('marketing.home.cta_register') }}
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </a>
                <a href="{{ route('how-it-works') }}" class="marketing-button marketing-button--ghost">
                    <span class="marketing-button__play" aria-hidden="true">
                        <i data-lucide="play"></i>
                    </span>
                    {{ __('marketing.home.cta_how') }}
                </a>
            </div>

            <ul class="career-hero__signals" aria-label="{{ __('marketing.home.signal_label') }}">
                <li>{{ __('marketing.home.signal_cv') }}</li>
                <li>{{ __('marketing.home.signal_role') }}</li>
                <li>{{ __('marketing.home.signal_plan') }}</li>
            </ul>
        </div>

        <div class="career-hero__visual" data-trajectory-visual data-reveal="visual">
            @include('marketing.partials.panel-preview')
        </div>
    </div>

    <div class="marketing-container career-flow-strip" data-reveal>
        <p>{{ __('marketing.home.flow_title') }}</p>
        <ol>
            <li><span>01</span>{{ __('marketing.home.flow_cv') }}</li>
            <li><span>02</span>{{ __('marketing.home.flow_gap') }}</li>
            <li><span>03</span>{{ __('marketing.home.flow_plan') }}</li>
            <li><span>04</span>{{ __('marketing.home.flow_match') }}</li>
        </ol>
    </div>
</section>

<section class="career-journey" id="product-route">
    <div class="marketing-container career-journey__grid">
        <div class="career-journey__intro" data-reveal>
            <p class="marketing-kicker marketing-kicker--dark">
                <span aria-hidden="true"></span>
                {{ __('marketing.home.journey_eyebrow') }}
            </p>
            <h2>{{ __('marketing.home.journey_title') }}</h2>
            <p>{{ __('marketing.home.journey_intro') }}</p>
            <a href="{{ route('features') }}" class="marketing-text-link">
                {{ __('marketing.home.journey_link') }}
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
        </div>

        <div class="career-journey__steps">
            <article class="career-step" data-reveal>
                <div class="career-step__rail" aria-hidden="true"><span></span></div>
                <div class="career-step__content">
                    <div class="career-step__topline">
                        <span>{{ __('marketing.home.step_discover_label') }}</span>
                        <i data-lucide="file-text" aria-hidden="true"></i>
                    </div>
                    <h3>{{ __('marketing.home.feature_cv_title') }}</h3>
                    <p>{{ __('marketing.home.feature_cv_desc') }}</p>
                    <strong>{{ __('marketing.home.step_discover_output') }}</strong>
                </div>
            </article>

            <article class="career-step career-step--featured" data-reveal>
                <div class="career-step__rail" aria-hidden="true"><span></span></div>
                <div class="career-step__content">
                    <div class="career-step__topline">
                        <span>{{ __('marketing.home.step_build_label') }}</span>
                        <i data-lucide="trending-up" aria-hidden="true"></i>
                    </div>
                    <h3>{{ __('marketing.home.feature_roadmap_title') }}</h3>
                    <p>{{ __('marketing.home.feature_roadmap_desc') }}</p>
                    <strong>{{ __('marketing.home.step_build_output') }}</strong>
                </div>
            </article>

            <article class="career-step" data-reveal>
                <div class="career-step__rail" aria-hidden="true"><span></span></div>
                <div class="career-step__content">
                    <div class="career-step__topline">
                        <span>{{ __('marketing.home.step_match_label') }}</span>
                        <i data-lucide="briefcase-business" aria-hidden="true"></i>
                    </div>
                    <h3>{{ __('marketing.home.feature_match_title') }}</h3>
                    <p>{{ __('marketing.home.feature_match_desc') }}</p>
                    <strong>{{ __('marketing.home.step_match_output') }}</strong>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="career-cta">
    <div class="marketing-container">
        <div class="career-cta__panel" data-reveal>
            <div class="career-cta__orbit" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
            <div class="career-cta__copy">
                <p class="marketing-kicker"><span aria-hidden="true"></span>{{ __('marketing.home.cta_eyebrow') }}</p>
                <h2>{{ __('marketing.home.cta_title') }}</h2>
                <p>{{ __('marketing.home.cta_body') }}</p>
            </div>
            <div class="career-cta__action">
                <a href="{{ route('register') }}" class="marketing-button marketing-button--light">
                    {{ __('marketing.home.cta_button') }}
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </a>
                <span>{{ __('marketing.home.cta_note') }}</span>
            </div>
        </div>
    </div>
</section>
@endsection
