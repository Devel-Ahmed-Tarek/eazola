<div class="sidebars-wrappers newspaper_sidebar">
    <div class="sidebars-close" style="float: right"> <i class="las la-times"></i> </div>
{{--    <div class="sidebar-inner">--}}
{{--        <div class="sidebar-logo">--}}
{{--            <a href="{{url('/')}}">--}}
{{--            {!! render_image_markup_by_attachment_id(get_static_option('site_logo'),'logo') !!}--}}
{{--        </div>--}}
        <div class="restaurantMenu__sidebar restaurantMenu__detailsPage">
            <div class="restaurantMenu__sidebar__header">
                <div class="restaurantMenu__sidebar__header__item">
                    <div class="restaurantMenu__sidebar__header__logo">
                        <a href="{{url('/')}}"><img src="/core/Modules/Restaurant/assets/img/logo.png" alt="logo"> </a>
                    </div>
                </div>
                <div class="restaurantMenu__sidebar__header__item d-lg-none">
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
                        @php
                            $menu_categoriess = \Modules\Restaurant\Entities\MenuCategory::get();
                        @endphp
                        <ul class="tabs menu_categories_list" id="restaurantMenu__tab">
                            @foreach($menu_categoriess ?? [] as $item)
                                <li data-tab="{{$item->id}}" class="@if($loop->first) active @endif">
                                    <div class="restaurantMenu__sidebar__body__menu__list__contents">
                                        <img src="{!! render_image_url_by_attachment_id($item->image_id) !!}" alt="menu1" style="width: 60px; height: 60px;">
                                        <span class="restaurantMenu__sidebar__body__menu__list__contents__title">{{$item->name}}</span>
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
                        <div class="tab_content_item" id="burger">
                            <div class="restaurantMenu__sidebar__body__menu__contents__item">
                                <div class="restaurantMenu__sidebar__body__menu__contents__item__flex">
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__thumb">
                                        <img src="/core/Modules/Restaurant/assets/img/food/food1.jpg" alt="food1">
                                    </div>
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__contents">
                                        <h6 class="restaurantMenu__sidebar__body__menu__contents__item__title">
                                            Chicken Burger</h6>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__para mt-2">
                                            Fast food Submarine sandwich,</p>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__code mt-2">
                                            IQD6,500</p>
                                    </div>
                                </div>
                            </div>
                            <div class="restaurantMenu__sidebar__body__menu__contents__item">
                                <div class="restaurantMenu__sidebar__body__menu__contents__item__flex">
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__thumb">
                                        <img src="/core/Modules/Restaurant/assets/img/food/food2.jpg" alt="food2">
                                    </div>
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__contents">
                                        <h6 class="restaurantMenu__sidebar__body__menu__contents__item__title">
                                            Beef Burger</h6>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__para mt-2">
                                            Fast food Submarine sandwich,</p>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__code mt-2">
                                            IQD6,600</p>
                                    </div>
                                </div>
                            </div>
                            <div class="restaurantMenu__sidebar__body__menu__contents__item">
                                <div class="restaurantMenu__sidebar__body__menu__contents__item__flex">
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__thumb">
                                        <img src="/core/Modules/Restaurant/assets/img/food/food3.jpg" alt="food3">
                                    </div>
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__contents">
                                        <h6 class="restaurantMenu__sidebar__body__menu__contents__item__title">
                                            Italian Burger</h6>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__para mt-2">
                                            Fast food Submarine sandwich,</p>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__code mt-2">
                                            IQD6,350</p>
                                    </div>
                                </div>
                            </div>
                            <div class="restaurantMenu__sidebar__body__menu__contents__item">
                                <div class="restaurantMenu__sidebar__body__menu__contents__item__flex">
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__thumb">
                                        <img src="/core/Modules/Restaurant/assets/img/food/food4.jpg" alt="food4">
                                    </div>
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__contents">
                                        <h6 class="restaurantMenu__sidebar__body__menu__contents__item__title">
                                            California Burger</h6>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__para mt-2">
                                            Fast food Submarine sandwich,</p>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__code mt-2">
                                            IQD6,550</p>
                                    </div>
                                </div>
                            </div>
                            <div class="restaurantMenu__sidebar__body__menu__contents__item">
                                <div class="restaurantMenu__sidebar__body__menu__contents__item__flex">
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__thumb">
                                        <img src="/core/Modules/Restaurant/assets/img/food/food5.jpg" alt="food5">
                                    </div>
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__contents">
                                        <h6 class="restaurantMenu__sidebar__body__menu__contents__item__title">
                                            Bazel Burger</h6>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__para mt-2">
                                            Fast food Submarine sandwich,</p>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__code mt-2">
                                            IQD6,800</p>
                                    </div>
                                </div>
                            </div>
                            <div class="restaurantMenu__sidebar__body__menu__contents__item">
                                <div class="restaurantMenu__sidebar__body__menu__contents__item__flex">
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__thumb">
                                        <img src="/core/Modules/Restaurant/assets/img/food/food6.jpg" alt="food6">
                                    </div>
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__contents">
                                        <h6 class="restaurantMenu__sidebar__body__menu__contents__item__title">
                                            Sicilian Burger</h6>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__para mt-2">
                                            Fast food Submarine sandwich,</p>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__code mt-2">
                                            IQD6,800</p>
                                    </div>
                                </div>
                            </div>
                            <div class="restaurantMenu__sidebar__body__menu__contents__item">
                                <div class="restaurantMenu__sidebar__body__menu__contents__item__flex">
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__thumb">
                                        <img src="/core/Modules/Restaurant/assets/img/food/food7.jpg" alt="food7">
                                    </div>
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item__contents">
                                        <h6 class="restaurantMenu__sidebar__body__menu__contents__item__title">
                                            Greek Burger</h6>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__para mt-2">
                                            Fast food Submarine sandwich,</p>
                                        <p class="restaurantMenu__sidebar__body__menu__contents__item__code mt-2">
                                            IQD6,800</p>
                                    </div>
                                </div>
                            </div>
                        </div>
{{--                        adnan tab button first--}}

                        @foreach($menu_categories ?? [] as $item)
                            <div class="tab_content_item @if($loop->first) active @endif" id="{{$item->id}}">
                                @foreach($food_menus->where('menu_category_id',$item->id) ?? [] as $menu)
                                    <div class="restaurantMenu__sidebar__body__menu__contents__item detailsListItem @if($loop->first) active  @endif "
                                         data-details="{{$menu->slug}}">
                                        <div class="restaurantMenu__sidebar__body__menu__contents__item__flex subCategory_menu_list" onclick="subMenuClick({{$menu->id}})">
                                            <input class="menu_content_id" type="hidden" name="menu_content_id" value="{{$menu->id}}">
                                            <div class="restaurantMenu__sidebar__body__menu__contents__item__thumb">
{{--                                                {!!render_image_markup_by_attachment_id($menu->image_id) !!}--}}
                                                <img src="{!! render_image_url_by_attachment_id($menu->image_id) !!}" alt="menu1" style="width: 80px; height: 80px;">
                                            </div>
                                            <div class="restaurantMenu__sidebar__body__menu__contents__item__contents">
                                                <h6 class="restaurantMenu__sidebar__body__menu__contents__item__title">
                                                    {{$menu->name}}</h6>
                                                <p class="restaurantMenu__sidebar__body__menu__contents__item__para mt-2">
                                                    {{$menu->getTranslation('title',$lang_slug)}}</p>
                                                <p class="restaurantMenu__sidebar__body__menu__contents__item__code mt-2">
                                                    {{$menu->sku}}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
{{--            </div>--}}
        </div>
    </div>
</div>
