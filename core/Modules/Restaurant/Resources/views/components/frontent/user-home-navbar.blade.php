<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('tenant.user.dashboard.user.order')) active @endif" aria-current="page" href="{{route('tenant.user.dashboard.user.order')}}">{{__('Menu
            Orders')}}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('tenant.user.dashboard.pending.order')) active @endif" href="{{route('tenant.user.dashboard.pending.order')}}">{{__('Pending
            Orders')}}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('tenant.user.dashboard.approved.order')) active @endif" href="{{route('tenant.user.dashboard.approved.order')}}">{{__('Approved
            Orders')}}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(request()->routeIs('tenant.user.dashboard.canceled.order')) active @endif" href="{{route('tenant.user.dashboard.canceled.order')}}">{{__('Canceled
            Orders')}}</a>
    </li>
</ul>
