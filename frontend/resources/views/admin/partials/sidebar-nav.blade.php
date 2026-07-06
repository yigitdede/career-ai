@foreach ($adminNav as $item)
    <a href="{{ route($item['route']) }}"
        class="panel-nav-link {{ request()->routeIs($item['route']) ? 'panel-nav-link-active' : '' }}">
        <span class="w-5 shrink-0 text-center">{{ $item['icon'] }}</span>
        <span class="truncate">{{ $item['label'] }}</span>
    </a>
@endforeach
