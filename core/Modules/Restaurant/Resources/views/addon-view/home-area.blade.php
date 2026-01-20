@php
    $post_img = null;
    $lang_slug = get_user_lang();
@endphp

<main>
    <section
        class="main_area mainPage__area main__bg" {!! render_background_image_markup_by_attachment_id($data['background_image']) !!}
    ">
    <div class="main__leaf">

        <img src="{!! render_image_url_by_attachment_id($data['left_image']) !!}" alt="menu1"
             style="width: 101px; height: 115px;">
        <img src="{!! render_image_url_by_attachment_id($data['middle_image']) !!}" alt="menu1"
             style="width: 101px; height: 115px;">
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="main__wrap">
                    <div class="main__wrap__contents">
                            <span class="main__wrap__subtitle"><span
                                    class="main__wrap__subtitle__shapes"
                                    style="background-image: url('{{route('bg_image', "gradient_title2.png")}}');">{{$data['top_title_one']}}</span> <span
                                    class="main__wrap__subtitle__delicious">{{$data['top_title_two']}}</span></span>
                        <h1 class="main__wrap__title"
                            style="background-image: url('{{route('bg_image', "gradient_title.png")}}');">{{$data['middle_title']}}</h1>
                        <span class="main__wrap__bestDeal">{{$data['last_title']}}</span>
                        <div class="btn-wrapper clickSidebarBtn mt-4">
                            <a href="javascript:void(0)" class="cmn_btn btn_bg_1 radius-30">{{$data['button_text']}}</a>
                        </div>
                    </div>
                    <div class="main__wrap__thumb">
                        {!! render_image_markup_by_attachment_id($data['right_image']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>

    <!-- Restaurant Menu sidebar start  -->
    <div class="restaurantMenu__sidebar restaurantMenu__index">
        <div class="restaurantMenu__sidebar__header">
            <div class="restaurantMenu__sidebar__header__item">
                <div class="restaurantMenu__sidebar__header__logo">
                    <a href="{{url('/')}}" class="logo">
                        {!! render_image_markup_by_attachment_id(get_static_option('site_logo'),'logo') !!}
                    </a>
                </div>
            </div>
            <div class="restaurantMenu__sidebar__header__item">
                <div class="restaurantMenu__sidebar__header__bars">
                    <div class="restaurantMenu__sidebar__header__icon restaurantMenu__bars">
                        <i class="las la-bars"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="restaurantMenu__sidebar__body">
            <div class="restaurantMenu__sidebar__body__menu">
                <div class="restaurantMenu__sidebar__body__menu__list" id="restaurantMenu__wrapper">
               <span class="restaurantMenu__sidebar__body__menu__list__arrow categorySub-arrow leftArrow"
                     id="prevBtn">
                   <i class="las la-arrow-left"></i>
               </span>
                    <ul class="tabs" id="restaurantMenu__tab">
                        @foreach($data['menu_categories'] ?? [] as $item)
                            <li data-tab="{{$item->id}}" class="@if($loop->first) active @endif">
                                <div class="restaurantMenu__sidebar__body__menu__list__contents">
                                    <img src="{!! render_image_url_by_attachment_id($item->image_id) !!}" alt="menu1"
                                         style="width: 60px; height: 60px;">
                                    <span
                                        class="restaurantMenu__sidebar__body__menu__list__contents__title">{{$item->name}}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <span class="restaurantMenu__sidebar__body__menu__list__arrow categorySub-arrow rightArrow"
                          id="nextBtn">
                   <i class="las la-arrow-right"></i>
               </span>
                </div>
                <div class="restaurantMenu__sidebar__body__menu__contents" id="restaurantMenu__tabContent">

                    @foreach($data['menu_categories'] ?? [] as $item)
                        <div class="tab_content_item @if($loop->first) active @endif" id="{{$item->id}}">
                            @foreach($item->food_menus ?? [] as $menu)
                                <div
                                    class="restaurantMenu__sidebar__body__menu__contents__item detailsListItem @if($loop->first) active  @endif "
                                    data-details="{{$menu->slug}}">
                                    <a href="  {{route('tenant.frontend.menu.details',$menu->slug)}}">
                                        <div
                                            class="restaurantMenu__sidebar__body__menu__contents__item__flex subCategory_menu_list"
                                            onclick="subMenuClick({{$menu->id}})">
                                            <input class="menu_content_id" type="hidden" name="menu_content_id"
                                                   value="{{$menu->id}}">
                                            <div class="restaurantMenu__sidebar__body__menu__contents__item__thumb">
                                                <img src="{!! render_image_url_by_attachment_id($menu->image_id) !!}"
                                                     alt="menu1" style="width: 80px; height: 80px;">
                                            </div>
                                            <div class="restaurantMenu__sidebar__body__menu__contents__item__contents">
                                                <h6 class="restaurantMenu__sidebar__body__menu__contents__item__title">
                                                    {{$menu->name}}</h6>
                                                <p class="restaurantMenu__sidebar__body__menu__contents__item__para mt-2">
                                                    {{ Str::limit(@$menu->getTranslation('title',$lang_slug), 42) }}</p>
                                                <p class="restaurantMenu__sidebar__body__menu__contents__item__code mt-2">
                                                    {{$menu->sku}}</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</main>
