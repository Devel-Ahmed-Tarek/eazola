@if(!request()->is('/') && !Request::is('menu/details/*'))
    <style>
        #restaurant ul li {
            color: var(--black);
        }

        #restaurant-user-account ul li {
            color: black !important;
        }

        .white__nav .user_account .listItem {
            color: var(--black);
        }

        .tenant_languages_selector {
            color: white !important;
        }

        .header__restaurant .nice-select.niceSelectt.tenant_languages_selector {
            background: none;
            border: 0;
            padding: 0;
            padding-right: 25px;
            color: black !important;
        }
    </style>
@else
    <style>
        .header__restaurant .nice-select.niceSelectt.tenant_languages_selector {
            background: none;
            border: 0;
            padding: 0;
            padding-right: 25px;
            color: white;
        }
    </style>
@endif


<header class="header__restaurant">
    <nav class="navbar white__nav navbar__area navbar__border navbar__padding navbar-expand-lg">
        <div class="container nav__container container-one">
            <div class="responsive-mobile-menu">
                <a href="javascript:void(0)" class="click-nav-right-icon">
                    <i class="las la-ellipsis-v"></i>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#restaurant">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="restaurant">
                <ul class="navbar-nav">
                    {!! render_frontend_menu($primary_menu) !!}
                </ul>
            </div>
            <div class="navbar-right-content show-nav-content">
                <div class="navbar-right-content show-nav-content">
                    <div class="navbar-right-content show-nav-content">
                        <div class="single-right-content">

                            <div class="header-info-right" id="restaurant-user-account">

                                <ul class="user_account">
                                    @if (auth()->check())
                                        @php
                                            $route = auth()->guest() == 'admin' ? route('tenant.admin.dashboard') : route('tenant.user.home');
                                        @endphp
                                        <li class="listItem"><a href="{{ $route }}">{{__('Dashboard') }}</a>
                                            <span>/</span>
                                            <a href="{{ route('tenant.user.logout') }}">{{__('Logout') }}</a>
                                        </li>
                                    @else

                                        <li class="listItem">
                                            @if(!empty(get_static_option('tenant_login_show_hide')))
                                                <a href="{{ route('tenant.user.login') }}">{{ __('Login') }}</a>
                                                <span>/</span>
                                            @endif
                                            @if(!empty(get_static_option('tenant_register_show_hide')))
                                                <a href="{{ route('tenant.user.register') }}">{{ __('Register') }}</a>
                                            @endif
                                        </li>
                                    @endif
                                </ul>

                                <div
                                    class="language_dropdown @if(get_user_lang_direction() == 'rtl') ml-1 @else mr-1 @endif d-none"
                                    id="languages_selector">
                                    @if (auth()->check())
                                        @php
                                            $route = auth()->guest() == 'admin' ? route('tenant.admin.dashboard') : route('tenant.user.home');
                                        @endphp
                                        <div class="selected-language">{{ __('Account') }}<i
                                                class="fas fa-caret-down"></i></div>
                                        <ul>
                                            <li class="listItem"><a href="{{ $route }}">{{ __('Dashboard') }}</a>
                                            <li class="listItem"><a
                                                    href="{{ route('tenant.user.logout') }}">{{ __('Logout') }}</a></li>
                                        </ul>
                                    @else
                                        <div class="selected-language">{{ __('Login') }}<i
                                                class="fas fa-caret-down"></i></div>
                                        <ul>
                                            <li class="listItem"><a class="listItem"
                                                                    href="{{ route('tenant.user.login') }}">{{ __('Login') }}</a>
                                            <li class="listItem"><a class="listItem"
                                                                    href="{{ route('tenant.user.register') }}">{{ __('Register') }}</a>
                                        </ul>
                                    @endif
                                </div>
                                @if(get_static_option('landlord_frontend_language_show_hide'))
                                    <!-- Select  -->
                                    <div class="select-language">
                                        <select class="niceSelectt tenant_languages_selector">
                                            @foreach(\App\Facades\GlobalLanguage::all_languages(\App\Enums\StatusEnums::PUBLISH) as $lang)
                                                @php
                                                    $exploded = explode('(',$lang->name);
                                                @endphp
                                                <option class="lang_item"
                                                        @selected(session()->get('lang') == $lang->slug) value="{{$lang->slug}}">{{current($exploded)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>

