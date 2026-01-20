<div class="breadcrumb-area breadcrumb-padding breadcrumb-border
    @if((in_array(request()->route()->getName(),['tenant.frontend.homepage','tenant.dynamic.page']) && !empty($page_post) && $page_post->breadcrumb == 0 ))
        d-none
    @endif
">
    <div class="container custom-container-one">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-contents">
                    <ul class="breadcrumb-contents-list">
                        <li class="breadcrumb-contents-list-item"><a class="breadcrumb-contents-list-item-link" href="{{route('tenant.frontend.homepage')}}">{{__('Home')}}</a></li>
                        @if(Route::currentRouteName() === 'tenant.dynamic.page')
                            <li class="breadcrumb-contents-list-item"><a class="breadcrumb-contents-list-item-link" href="#">{!! $page_post->title ?? '' !!}</a></li>
                        @else
                            <li class="breadcrumb-contents-list-item">@yield('page-title')</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

