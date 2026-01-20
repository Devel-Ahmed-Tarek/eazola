<style>
    .header-top .header-info-left .listing .listItem {
        color: black;
    }
</style>
<!-- Banner area Starts -->
<div class="banner-area banner-area-one">
    <div class="container-fluid p-0">
        <div class="row align-items-center flex-column-reverse flex-lg-row">
            <div class="col-lg-6">
                <div class="banner-single banner-single-one percent-padding">
                    <div class="banner-single-content">
                        <h2 class="banner-single-content-title fw-700"> {{$data['title']}} </h2>
                        <p class="banner-single-content-para mt-3"> {{$data['description']}} </p>
                        <div class="banner-location banner-location-one bg-white radius-5 mt-5">
                            <form action="{{route('tenant.frontend.search_room')}}" method="post">
                                @csrf
                            <div class="banner-location-flex">
                                <div class="banner-location-single">
                                    <div class="banner-location-single-flex">
                                        <div class="banner-location-single-icon">
                                            <i class="las la-calendar"></i>
                                        </div>
                                        <div class="banner-location-single-contents">
                                            <span class="banner-location-single-contents-subtitle">   {{__('Check In')}} </span>
                                            <div class="banner-location-single-contents-dropdown custom-select">
                                                <input name="check_in" class="select-style-two form--control test-picker select-style-two lg" type="text" value="{{today()}}" placeholder="Check in" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="banner-location-single">
                                    <div class="banner-location-single-flex">
                                        <div class="banner-location-single-icon">
                                            <i class="las la-calendar"></i>
                                        </div>
                                        <div class="banner-location-single-contents">
                                            <span class="banner-location-single-contents-subtitle">  {{__('Check Out')}} </span>
                                            <div class="banner-location-single-contents-dropdown">
                                                <input id="datePicker" name="check_out" value="{{today()}}" class="form--control test-picker select-style-two" type="text" placeholder="Check in" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="banner-location-single">
                                    <div class="banner-location-single-flex">
                                        <div class="banner-location-single-icon">
                                            <i class="las la-user-friends"></i>
                                        </div>
                                        <div class="banner-location-single-contents">
                                            <span class="banner-location-single-contents-subtitle">  {{__('Person')}} </span>
                                            <div class="banner-location-single-contents-dropdown custom-select">
                                                <input  type="number" name="person" id="children" class="form-control"
                                                       value="1" pattern="[0-9]" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="banner-location-single">
                                    <div class="banner-location-single-flex">
                                        <div class="banner-location-single-icon">
                                            <i class="las la-user-friends"></i>
                                        </div>
                                        <div class="banner-location-single-contents">
                                            <span class="banner-location-single-contents-subtitle">   {{__('Children')}} </span>
                                            <div class="banner-location-single-contents-dropdown custom-select">
                                                <input  type="number" name="children" id="children" class="form-control"
                                                 value="0" pattern="[0-9]">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="banner-location-single-search">
                                    <div class="search-suggestions-wrapper">
                                        <button type="submit" class="search-click-icon"><i class="las la-search"></i></button>
                                    </div>
                                    <div class="search-suggestion-overlay"></div>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 bg-image banner-right-bg radius-20"  {!! render_background_image_markup_by_attachment_id($data['background_image']) !!}"></div>
        </div>
    </div>
</div>
<!-- Banner area end -->
