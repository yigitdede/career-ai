@foreach ($adminNav as $item)
    <a href="{{ route($item['route']) }}"
        class="panel-nav-link {{ request()->routeIs($item['route']) ? 'panel-nav-link-active' : '' }}">
        @include('app.partials.sidebar-nav-icon', ['icon' => $item['icon']])
        <span class="truncate">{{ $item['label'] }}</span>
    </a>
@endforeach
