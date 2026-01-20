@extends('tenant.frontend.frontend-page-master')
@php
    $post_img = null;
    $user_lang = get_user_lang();
@endphp

@section('page-title')
    {{@$menu['title']}}
@endsection

@section('title')
    {{@$menu['title']}}
@endsection

@section('meta-data')
@endsection

@section('style')

@endsection

@section('content')
    @php
        $lang_slug = request()->get('lang') ?? \App\Facades\GlobalLanguage::default_slug();
    @endphp

    <style>
        .regular__price {
            text-decoration: line-through; /* Strike-through effect for regular price */
            color: #888; /* Color for the regular price */
        }

        .offer__price {
            font-weight: bold; /* Emphasize the offer price */
            color: #ff0000; /* Color for the offer price */
        }
    </style>

    <!-- Details page start -->
    <section class="main_area detailsPage__area main__bg"
             style="background: url('{{route('bg_image',$menu->bg_image ?? "main_bg.jpg")}}')">

        <div class="align-content-center">
            @if($errors->any())
                <div class="alert alert-danger main_error_message">
                    <ul class="list-none text-center">
                        @foreach($errors->all() as $error)
                            <li>{{$error}}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <x-flash-msg/>
        </div>

        <div class="responsive__bars responsiveSidebarBtn">
            <i class="las la-bars"></i>
        </div>


        <div class="food_menu_contact_section">
            <div class="product__details @if($menu) active @endif">
                <div class="row align-items-center g-5">
                    <div class="col-xl-6">
                        <div class="product__details__thumb">
                            {!!render_image_markup_by_attachment_id($menu->image_id) !!}
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="product__details__contents">
                            <div class="product__details__contents__single">
                                <h4 class="product__details__contents__title mb-3">{{$menu->getTranslation('name',$lang_slug)}}</h4>
                                <div class="food_menu_price_section" id="food_menu_price_sectiondd">
                                    <span
                                        class="product__details__contents__price offer__price">{{ @amount_with_currency_symbol($menu->sale_price) }}</span>
                                    <span
                                        class="product__details__contents__price regular__price">{{ @amount_with_currency_symbol($menu->regular_price) }}</span>
                                </div>
                                <p class="product__details__contents__para  mt-3">{{$menu->getTranslation('description',$lang_slug)}}</p>
                                @php
                                    $food_menu_attributes =  \Modules\Restaurant\Entities\FoodMenuAttribute::where('food_menu_id', $menu->id)
                                     ->get()
                                     ->groupBy('term');
                                    $attribute_count = $food_menu_attributes->count();

                                @endphp
                                <input readonly class="form--input value-size" name="size" type="hidden" value="">
                                <input class="form--input value-size" id="menu_id" name="menu_id" type="hidden"
                                       value="{{$menu->id}}">
                                <input class="form--input value-size attribute_count" name="attribute_count"
                                       type="hidden" value="{{$attribute_count}}">

                                {{--adnan work start --}}
                                <div class="value-input-area">
                                    <div class="value-input-area single-input-list mt-4 size_list">
                                        @foreach($food_menu_attributes ?? [] as $key => $item)
                                            <div class="value-input-item">
                                                    <span class="input-title fw-500 color-heading">
                                                       <span
                                                           class="product__details__contents__size__title">{{$key}}:</span>
                                                       <input type="hidden" id="selected_size">
                                                    </span>
                                                <ul class="size-lists select-list" data-type="Size">
                                                    @foreach($item as $value)
                                                        <li class="list text-white"
                                                            data-value="{{ $value->id }}"
                                                            data-display-value="{{ $value->value }}"
                                                        > {{$value->value }} </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="product__details__contents__cart mt-4">
                                    <div class="product__details__contents__cart__item">
                                        <div class="product__details__contents__cart__wrapper">
                                            <div
                                                class="product__details__contents__cart__wrapper__icon substract menu-quantity-substract">
                                                <i
                                                    class="las la-minus"></i>
                                            </div>
                                            <input name="menu_quantity" type="number" min="1" value="1"
                                                   class="product__details__contents__cart__wrapper__input quantity-input">
                                            <div
                                                class="product__details__contents__cart__wrapper__icon plus menu-quantity-plus">
                                                <i
                                                    class="las la-plus"></i></div>
                                        </div>
                                    </div>
                                    <div class="product__details__contents__cart__item">
                                        <div class="btn-wrapper checkOutBtn">
                                            @if($menu->is_orderable == 1)
                                                <a href="javascript:void(0)"
                                                   class="product__details__contents__cart__btn">{{__('Add to cart')}}</a>
                                            @else
                                                <button class="btn btn-warning"> {{__('Not Orderable')}}</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="product__details__contents__single">
                                <div class="product__details__contents__category">
                                    <span
                                        class="product__details__contents__category__title"> {{__('Category:')}}</span>
                                    <div class="product__details__contents__category__list">
                                        <a href="javascript:void(0)"
                                           class="product__details__contents__category__list__item">{{$menu->category?->getTranslation('name',$lang_slug)}}</a>
                                    </div>
                                </div>
                                <div class="product__details__contents__category mt-3">
                                    <span class="product__details__contents__category__title"> {{__('Tags:')}}</span>
                                    <div class="product__details__contents__category__list">
                                        <a href="javascript:void(0)"
                                           class="product__details__contents__category__list__item">{{$menu?->getTranslation('tag',$lang_slug)}}</a>
                                    </div>
                                </div>
                                <div class="product__details__contents__social mt-4">
                                    <span class="product__details__contents__social__title"> {{__('Share:')}}</span>
                                    <div class="product__details__contents__social__list">
                                        <a href="{{get_static_option('topbar_facebook_url')}}"
                                           class="product__details__contents__social__list__item"><i
                                                class="lab la-facebook-f"></i></a>
                                        <a href="{{get_static_option('topbar_twitter_url')}}"
                                           class="product__details__contents__social__list__item"><i
                                                class="lab la-twitter"></i></a>
                                        <a href="{{get_static_option('topbar_instagram_url')}}"
                                           class="product__details__contents__social__list__item"><i
                                                class="lab la-instagram"></i></a>
                                        <a href="{{get_static_option('topbar_linkedin_url')}}"
                                           class="product__details__contents__social__list__item"><i
                                                class="lab la-linkedin-in"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Restaurant Menu sidebar start  -->
    <div class="restaurantMenu__sidebar restaurantMenu__detailsPage">
        <div class="restaurantMenu__sidebar__header">
            <div class="restaurantMenu__sidebar__header__item">
                <div class="restaurantMenu__sidebar__header__logo">
                    <a href="{{url('/')}}" class="logo">
                        {!! render_image_markup_by_attachment_id(get_static_option('site_logo'),'logo') !!}
                    </a>
                </div>
            </div>
            <div class="restaurantMenu__sidebar__header__item d-lg-none">
                <div class="restaurantMenu__sidebar__header__bars">
                    <div class="restaurantMenu__sidebar__header__icon responsiveSidebarBtn">
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
                    <ul class="tabs menu_categories_list" id="restaurantMenu__tab">
                        @foreach($menu_categories ?? [] as $item)
                            <li data-tab="{{$item->id}}" class="@if($loop->first) active @endif">
                                <div class="restaurantMenu__sidebar__body__menu__list__contents">
                                    <img src="{!! render_image_url_by_attachment_id($item->image_id) !!}" alt="menu1"
                                         style="width: 60px; height: 60px;" onclick="MenuCategoryClick({{$item->id}})">
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
                    @foreach($menu_categories ?? [] as $item)
                        <div class="tab_content_item @if($loop->first) active @endif" id="{{$item->id}}">
                            @foreach($item->food_menus ?? [] as $menu)
                                <div
                                    class="restaurantMenu__sidebar__body__menu__contents__item detailsListItem @if($loop->first) active  @endif "
                                    data-details="{{$menu->slug}}">
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
                                                {{ Str::limit(@$menu->getTranslation('title',$lang_slug), 42) }}
                                            </p>
                                            <p class="restaurantMenu__sidebar__body__menu__contents__item__code mt-2">
                                                {{$menu->sku}}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if($item->food_menus->isEmpty())
                                    <div class="alert alert-danger text-center">{{__('No content available')}}</div>
                            @endif
                        </div>

                    @endforeach

                </div>
            </div>
        </div>
    </div>

    <!-- Checkout start -->
    <div class="checkOut__wrapper__overlay">

    </div>
    <div class="checkOut__wrapper">

    </div>

    <!-- Checkout end -->

@endsection

@section('scripts')

    @yield("custom-ajax-scripts")

    <script>

        (function($) {
            "use strict";

            //when clicked on subMenu related content will be visible
            window.subMenuClick = function(id)
            {
                let menuContentId = id;

                let data = new FormData();
                data.append("_token", "{{ csrf_token() }}");
                data.append("menu_id", menuContentId);

                send_ajax_request("post", data, '{{ route('tenant.frontend.menu.content') }}', function () {

                }, function (data) {
                    $('.food_menu_contact_section').html(data.html);
                }, function () {

                });
            }
            // end

            //when any attribute selected menu price will be changed
            $(document).ready(function ()
            {
                $(document).on('click', '.size_list li', function () {
                    // Remove the active class from all list items
                    $(this).closest('.select-list').find('.active').removeClass('active');
                    $(this).addClass('active');
                    menuPriceCalculate();
                });
            });
            // end

            // menu content quantity increment
            $(document).ready(function ()
            {
                $(document).on('click', '.menu-quantity-plus', function () {
                    menuPriceCalculate();
                });
            });
            // end

            // menu content quantity decrement
            $(document).ready(function ()
            {
                $(document).on('click', '.menu-quantity-substract', function () {
                    menuPriceCalculate();
                });
            });
            // end

            // menu content update when keyup on input field
            $(document).ready(function ()
            {
                $(document).on('keyup', '.quantity-input', function () {
                    menuPriceCalculate();
                });
            });
            // end

            // menu content display after menu category click
            window.MenuCategoryClick = function(menu_cat_id)
            {
                let data = new FormData();
                data.append("_token", "{{ csrf_token() }}");
                data.append("menu_cat_id", menu_cat_id);
                send_ajax_request("post", data, '{{ route('tenant.frontend.menu.content') }}', function () {

                }, function (data) {
                    $('.food_menu_contact_section').html(data.html);
                }, function () {

                });
            }
            // end

            // menu priceCalculate base function
            function menuPriceCalculate()
            {
                var selected_attributes = $('.size-lists li.active').map(function () {
                    return $(this).data('value');
                }).get();

                var menu_quantity = $('.quantity-input').val();
                var menu_id = $('#menu_id').val();

                let data = new FormData();
                data.append("_token", "{{ csrf_token() }}");
                data.append("menu_id", menu_id);
                data.append("menu_quantity", menu_quantity);
                data.append("attribute_ids", selected_attributes);

                send_ajax_request("post", data, '{{ route('tenant.frontend.menu.price-calculate') }}', function () {

                }, function (data) {

                    $('.food_menu_price_section').html(data.html);
                    $('.food_menu_price_sections').html(data.html);
                    $('.food_menu_price_sectionmmm').html(data.html);
                    $('#food_menu_price_section').html(data.html);
                    $('.sal_price_with_extra').val(data.sal_price_with_extra);
                }, function () {

                });
            }
            // end

            // add to cart
            $(document).ready(function ()
            {
                $(document).on('click', '.product__details__contents__cart__btn', function () {
                    var menu_quantity = $("input[name=menu_quantity]").val();
                    var attribute_count_value = $('.attribute_count').val();
                    var sal_price_with_extra = $('.sal_price_with_extra').val();

                    var selected_attributes = $('.size-lists li.active').map(function () {
                        return $(this).data('value');
                    }).get();
                    var menu_id = $('#menu_id').val();
                    let redirect_url =  `{{Request::url()}}`;
                    var domain = redirect_url.split('/')[2];
                    var restUrl = redirect_url.substring(redirect_url.indexOf(domain) + domain.length);

                    let data = new FormData();
                    data.append("_token", "{{ csrf_token() }}");
                    data.append("menu_id", menu_id);
                    data.append("sal_price_with_extra", sal_price_with_extra);
                    data.append("attribute_count_value", attribute_count_value);
                    data.append("selected_attributes", selected_attributes);
                    data.append("menu_quantity", menu_quantity);
                    data.append("redirect_url", restUrl);

                    send_ajax_request("post", data, '{{ route('tenant.frontend.menu.add_to_cart') }}', function () {

                    }, function (data) {
                        if (data.type != "error" && !data.redirect_url) {
                            $('.checkOut__wrapper').html(data.html);
                            CustomSweetAlertTwo.success(data.msg);
                        }
                        else if(data.redirect_url)
                        {
                            window.location.href = "{{ route("tenant.user.login") }}?return="+restUrl;
                        }
                        else {
                            CustomSweetAlertTwo.error(data.msg);
                            $('.checkOut__wrapper, .checkOut__wrapper__overlay').removeClass('active');
                        }

                    }, function () {

                    });
                });
            });
            // end

            //checkout cart quantity Increment
            window.quantityIncrement = function(rowId)
            {
                let data = new FormData();
                data.append("_token", "{{ csrf_token() }}");
                data.append("rowId", rowId);
                send_ajax_request("post", data, '{{ route('tenant.frontend.menu.cart.quantity.increment') }}', function () {
                }, function (data)
                {
                    $('.checkout-price-section').html(data.html);
                }, function ()
                {

                });
            }
            // end

            //checkout cart quantity Decrement
            window.quantityDecrement = function(rowId)
            {
                let quantity = $('.checkout_quantity' + rowId).val();

                if (quantity > 1) {
                    let data = new FormData();
                    data.append("_token", "{{ csrf_token() }}");
                    data.append("rowId", rowId);

                    send_ajax_request("post", data, '{{ route('tenant.frontend.menu.cart.quantity.decrement') }}', function () {

                    }, function (data) {
                        $('.checkout-price-section').html(data.html);
                    }, function () {

                    });
                } else {
                    alert('quantity can not be less then 1')
                }

            }
            // end

            //menu content update when keyup on input field
            $(document).ready(function()
            {
                $(document).on('keyup', '.checkout-quantity-input', function()
                {
                    let rowId = $(this).attr('id');
                    let quantity = $('.checkout_quantity' + rowId).val();

                    if (quantity >= 1) {
                        let data = new FormData();
                        data.append("_token", "{{ csrf_token() }}");
                        data.append("rowId", rowId);
                        data.append("quantity", quantity);

                        send_ajax_request("post", data, '{{ route('tenant.frontend.menu.checkout.price-calculate') }}', function () {

                        }, function (data) {
                            $('.checkout-price-section').html(data.html);
                        }, function () {

                        });
                    } else {
                        alert('quantity can not be less then 1')
                    }

                });
            });
            // end

            // remove cart item
            window.cartRemove = function(id)
            {
                let data = new FormData();
                data.append("_token", "{{ csrf_token() }}");
                data.append("cart_id", id);

                send_ajax_request("post", data, '{{ route('tenant.frontend.menu.cart.remove') }}', function () {

                }, function (data) {
                    $('.checkout-price-section').html(data.html);
                    CustomSweetAlertTwo.success('{{ __("Item removed successfully") }}');

                }, function () {

                });
            }
            // end

            // base ajax request structure
            function send_ajax_request(request_type, request_data, url, before_send, success_response, errors)
            {
                $.ajax({
                    url: url,
                    type: request_type,
                    headers: {
                        'X-CSRF-TOKEN': "4Gq0plxXAnBxCa2N0SZCEux0cREU7h4NHObiPH10",
                    },
                    beforeSend: (typeof before_send !== "undefined" && typeof before_send === "function") ? before_send : () => {
                        return "";
                    },
                    processData: false,
                    contentType: false,
                    data: request_data,
                    success: (typeof success_response !== "undefined" && typeof success_response === "function") ? success_response : () => {
                        return "";
                    },
                    error: (typeof errors !== "undefined" && typeof errors === "function") ? errors : () => {
                        return "";
                    }
                });
            }
            // end

            // submit checkout form
            $(document).on('submit', '#checkoutForm', function (e)
            {
                e.preventDefault();
                var form = $(this);
                var formID = form.attr('id');
                var formSelector = document.getElementById(formID);
                var formData = new FormData(formSelector);

                $.ajax({
                    url: "",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{csrf_token()}}",
                    },
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (data) {
                        if (data.warning_msg) {
                            CustomSweetAlertTwo.warning(data.warning_msg)
                        } else {
                            window.location.href = data.redirect_url;
                        }
                    }
                })
            });
            // end


            $(document).ready(function ()
            {
                // calling payment method list
                $(document).on('click', '.payment-gateway-list > li', function (e) {
                    e.preventDefault();
                    let gateway = $(this).data('gateway');
                    if (gateway === 'kinetic') {
                        $('.kinetic_payment_field').removeClass('d-none');
                    } else {
                        $('.kinetic_payment_field').addClass('d-none');

                    }
                    $(this).addClass('selected').siblings().removeClass('selected');
                    $('.payment-gateway-list').find(('input')).val($(this).data('gateway'));
                    $('.payment_gateway_passing_clicking_name').val(gateway);
                });
            });

        })(jQuery);

    </script>

@endsection
