<style>
    /* All Icons in Header/Navbar - Black Color */
    .navbar i,
    .navbar .mdi,
    .navbar-toggler i.mdi,
    .navbar-toggler .mdi-menu,
    .navbar-nav .nav-link i,
    .navbar-nav .nav-link .mdi,
    .navbar-nav .nav-item .nav-link i,
    .navbar-nav .count-indicator i,
    .navbar-nav .count-indicator .mdi-email-outline,
    .navbar-nav .count-indicator .mdi-bell,
    .navbar-nav .count-indicator .mdi-bell-outline,
    .navbar-nav .nav-logout .mdi,
    .navbar-nav .nav-logout .mdi-upload,
    .navbar-dropdown .dropdown-item i,
    .navbar-dropdown .dropdown-item .mdi {
        color: #000000 !important;
    }

    /* Navbar Toggler Icon */
    .navbar-toggler span.mdi-menu {
        color: #000000 !important;
    }

    /* Email Icon */
    .navbar-nav .count-indicator#messageDropdown .mdi-email-outline {
        color: #000000 !important;
    }

    /* Bell/Notification Icon */
    .navbar-nav .count-indicator .mdi-bell,
    .navbar-nav .count-indicator .mdi-bell-outline {
        color: #000000 !important;
    }

    /* Upload/Visit Website Icon */
    .navbar-nav .nav-logout .mdi-upload {
        color: #000000 !important;
    }

    /* Dropdown Menu Icons */
    .navbar-dropdown .dropdown-item .mdi-cached,
    .navbar-dropdown .dropdown-item .mdi-account-settings,
    .navbar-dropdown .dropdown-item .mdi-key,
    .navbar-dropdown .dropdown-item .mdi-logout {
        color: #000000 !important;
    }

    /* All Buttons in Header/Navbar - Black Text Always */
    .navbar .btn,
    .navbar .btn-info,
    .navbar .btn-outline-danger,
    .navbar .btn-icon-text,
    .navbar-nav .nav-logout .btn,
    .navbar-nav .nav-logout .btn-outline-danger,
    .navbar-nav .nav-logout .btn-icon-text,
    .navbar .btn-outline-danger.btn-icon-text,
    .navbar .btn .btn-icon-prepend,
    .navbar .btn span,
    .navbar .btn i {
        color: #000000 !important;
    }

    /* Button text before hover */
    .navbar .btn:not(:hover),
    .navbar .btn-info:not(:hover),
    .navbar .btn-outline-danger:not(:hover),
    .navbar-nav .nav-logout .btn:not(:hover) {
        color: #000000 !important;
    }

    /* Button text on hover - keep black */
    .navbar .btn:hover,
    .navbar .btn-info:hover,
    .navbar .btn-outline-danger:hover,
    .navbar-nav .nav-logout .btn:hover,
    .navbar .btn:hover span,
    .navbar .btn:hover i {
        color: #000000 !important;
    }
</style>

<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="{{route('landlord.admin.home')}}">
            @php
                $logo_type = !empty(get_static_option('dark_mode_for_admin_panel')) ? 'site_white_logo' : 'site_logo';
            @endphp
            {!! render_image_markup_by_attachment_id(get_static_option($logo_type)) !!}

        </a>
        <a class="navbar-brand brand-logo-mini" href="{{route('landlord.admin.home')}}">
            {!! render_image_markup_by_attachment_id(get_static_option('site_favicon')) !!}
        </a>


    </div>
    <div class="navbar-menu-wrapper d-flex align-items-stretch">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
        </button>

        <ul class="navbar-nav navbar-nav-right">

            {{-- Contact Message--}}
            <li class="nav-item dropdown">
                  <a href="{{route('landlord.admin.health')}}" class="btn btn-info">Health </a>
            </li>
            {{-- Contact Message--}}

            {{--Notification--}}
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="mdi mdi-bell"></i>
                    @if($new_notification)
                        <span class="count-symbol bg-warning"></span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list"
                     aria-labelledby="messageDropdown">

                    <h4 class="p-3 mb-0 message_notification_title"> {{ __('Notification') }}  ({{$new_notification}}) </h4>
                    <div class="dropdown-divider"></div>

                     @foreach($all_notifications as $notification)
                        <a class="dropdown-item preview-item" href="{{route(route_prefix().'admin.notification.view', $notification->id)}}">
                            <div class="preview-thumbnail">
                                <div class="preview-icon bg-success">
                                    <i class="mdi mdi-bell text-white"></i>
                                </div>
                            </div>
                            <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                <h6 class="preview-subject mb-1 font-weight-normal">{{__('Notification ' )}}
                                    <strong>{{$notification->title}}</strong></h6>
                                <p class="text-gray mb-0"> {{$notification->created_at->diffForHumans() . ' '}}
                                    @if($notification->status == 0)
                                        <small class="mt-1 text-danger">{{'('.__('New' .')')}}</small>
                                    @endif</p>
                            </div>
                            <div class="dropdown-divider"></div>
                          @endforeach

                            <h6 class="p-3 mb-0 text-center">
                                @if(count($all_notifications) > 0)
                                    <a href="{{route(route_prefix().'admin.notification.all')}}">{{__('See All')}}</a>
                                    @else
                                    <span >{{__('Empty')}}</span>
                                @endif

                            </h6>
                        </a>
                </div>
            </li>
            {{--Notification--}}


            {{-- Contact Message--}}
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="mdi mdi-email-outline"></i>
                    @if($new_message)
                        <span class="count-symbol bg-warning"></span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list"
                     aria-labelledby="messageDropdown">

                    <h4 class="p-3 mb-0 message_notification_title">({{$new_message}}) {{__('New Messages') }}  </h4>
                    <div class="dropdown-divider"></div>

                    @foreach($all_messages as $message)

                        <a class="dropdown-item preview-item" href="{{route(route_prefix().'admin.contact.message.view', $message->id)}}">
                            <div class="preview-thumbnail">
                                <div class="preview-icon bg-success">
                                    <i class="las la-envelope text-white"></i>
                                </div>
                            </div>
                            @php
                                $fields = json_decode($message->fields,true);
                            @endphp
                            <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                <h6 class="preview-subject mb-1 font-weight-normal">{{__('You have message from' )}}
                                    <strong>{{optional($message->form)->title}}</strong></h6>
                                <p class="text-gray mb-0"> {{$message->created_at->diffForHumans() . ' '}}  @if($message->status === 1)<small class="mt-1 text-danger">{{'('.__('New' .')')}}</small>@endif</p>
                            </div>
                            <div class="dropdown-divider"></div>
                            @endforeach

                            <h6 class="p-3 mb-0 text-center">

                                @if(count($all_messages) > 0)
                                    <a href="{{route(route_prefix().'admin.contact.message.all')}}">{{__('See All')}}</a>
                                @else
                                    <span >{{__('Empty')}}</span>
                                @endif
                            </h6>
                        </a>
                </div>

            </li>
            {{-- Contact Message--}}

            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown"
                   aria-expanded="false">
                    <div class="nav-profile-img">
                        {!! render_image_markup_by_attachment_id(auth('admin')->user()->image,'','full',true) !!}
                        <span class="availability-status online"></span>
                    </div>
                    <div class="nav-profile-text">
                        <p class="mb-1 text-black">{{auth('admin')->user()->name}}</p>
                    </div>
                </a>
                <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                    <a class="dropdown-item" href="{{route('landlord.admin.tenant.activity.log')}}">
                        <i class="mdi mdi-cached me-2 text-success"></i> {{__('Activity Log')}}
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{route('landlord.admin.edit.profile')}}">
                        <i class="mdi mdi-account-settings me-2 text-success"></i> {{__('Edit Profile')}}
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{route('landlord.admin.change.password')}}">
                        <i class="mdi mdi-key me-2 text-success"></i> {{__('Change Password')}}
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#"
                       onclick="event.preventDefault();
                           document.getElementById('tenanat_logout_submit_btn').dispatchEvent(new MouseEvent('click'));">
                        <i class="mdi mdi-logout me-2 text-primary"></i>
                        {{__('Signout')}}
                        <form id="logout-form" action="{{ route('landlord.admin.logout') }}" method="POST"
                              class="d-none">
                            @csrf
                            <button class="d-none" type="submit" id="tenanat_logout_submit_btn"></button>
                        </form>
                    </a>
                </div>
            </li>




            <!--<li class="nav-item nav-logout d-none d-lg-block">-->
            <!--    <a class="nav-link" href="#">-->
            <!--        <i class="mdi mdi-theme-light-dark"></i>-->
            <!--    </a>-->
            <!--</li>-->
            <li class="nav-item nav-logout d-none d-lg-block">
                <a class="btn btn-outline-danger btn-icon-text" href="{{route('landlord.homepage')}}" target="_blank">
                    <i class="mdi mdi-upload btn-icon-prepend"></i> {{__('Visit Site')}}
                </a>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>
