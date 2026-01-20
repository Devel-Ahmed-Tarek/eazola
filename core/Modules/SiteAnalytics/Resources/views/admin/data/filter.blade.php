<div class="dropdown">
    <a class="btn btn-info dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
        {{ $periods[$period] }}
    </a>

    @php
        if (isset($type))
        {
            if ($type == 'dashboard')
            {
                $route = route(route_prefix().'admin.dashboard.analytics');
            }
            elseif ($type == 'analytics')
            {
                $route = route(route_prefix().'admin.analytics');
            }
        }
    @endphp
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
        @foreach ($periods as $key => $value)
            <li>
                <a class="dropdown-item" href="{{ $route }}?period={{ $key }}">{{ $value }}</a>
            </li>
        @endforeach
    </ul>
</div>
