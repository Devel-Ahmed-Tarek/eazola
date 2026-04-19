
<header class="header-style-01 headerBg1">
    <!-- header-top -->
    @include('landlord.frontend.partials.topbar')
    <!-- Header Bottom -->
    <nav class="navbar navbar-area  navbar-expand-lg plr">
        <div class="container-fluid container-two nav-container">
            <div class="responsive-mobile-menu">
                <div class="logo-wrapper">
                    <a href="{{url('/')}}" class="logo">
                        @if(!empty(get_static_option('site_logo')))
                            {!! render_image_markup_by_attachment_id(get_static_option('site_logo'),'logo') !!}
                        @else
                            <h2 class="site-title">{{get_static_option('site_'.get_user_lang().'_title')}}</h2>
                        @endif
                    </a>
                </div>
                <!-- Click Menu Mobile right menu -->
                <a href="#0" class="click_show_icon"><i class="las la-ellipsis-v"></i> </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#bizcoxx_main_menu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="NavWrapper">
                <!-- Main Menu -->
                <div class="collapse navbar-collapse" id="bizcoxx_main_menu">
                    <ul class="navbar-nav">
                        {!! render_frontend_menu($primary_menu) !!}
                    </ul>
                </div>
            </div>
            <!-- Menu Right -->
            <div class="nav-right-content">
                <div class="btn-wrapper">
                    @guest('web')
                        <a href="{{route('landlord.user.login')}}" class="cmn-btn" target="_blank">{{__("Login")}}</a>
                    @endguest
                </div>
            </div>

        </div>
    </nav>
</header>
