<div class="product__details {{$food_menu->slug}}  active">
    <div class="row align-items-center g-5">
        <div class="col-xl-6">
            <div class="product__details__thumb">
                <img src="{!! render_image_url_by_attachment_id($food_menu->image_id) !!}"
                     alt="food1" style="width: 550px; height: 550px;">
            </div>
        </div>
        <div class="col-xl-6">
            <div class="product__details__contents">
                <div class="product__details__contents__single">
                    <h4 class="product__details__contents__title mb-3">{{$food_menu->getTranslation('name',$lang_slug)}}</h4>
                    <div class="food_menu_price_sections" id="food_menu_price_sectionmmm">
                        <span
                            class="product__details__contents__price offer__price">{{ @amount_with_currency_symbol($food_menu->sale_price) }}</span>
                        <span
                            class="product__details__contents__price regular__price">{{ @amount_with_currency_symbol($food_menu->regular_price) }}</span>
                    </div>
                    <p class="product__details__contents__para  mt-3">{{$food_menu->getTranslation('description',$lang_slug)}}</p>

                    @php
                        $food_menu_attributes =  \Modules\Restaurant\Entities\FoodMenuAttribute::where('food_menu_id', $food_menu->id)
                         ->get()
                         ->groupBy('term');
                        $attribute_count = $food_menu_attributes->count();

                    @endphp
                    <input readonly class="form--input value-size" name="size" type="hidden" value="">
                    <input readonly class="form--input value-size" id="menu_id" name="menu_id" type="hidden"
                           value="{{$food_menu->id}}">
                    <input readonly class="form--input value-size attribute_count" name="attribute_count" type="hidden"
                           value="{{$attribute_count}}">
                    <div class="value-input-area">
                        <div class="value-input-area single-input-list mt-4 size_list">
                            @foreach($food_menu_attributes ?? [] as $key => $item)
                                <div class="value-input-item">
                                        <span class="input-title fw-500 color-heading">
                                           <span class="product__details__contents__size__title">{{$key}}:</span>
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
                                <input name="menu_quantity" type="number" value="1"
                                       class="product__details__contents__cart__wrapper__input quantity-input">
                                <div class="product__details__contents__cart__wrapper__icon plus menu-quantity-plus"><i
                                        class="las la-plus"></i></div>
                            </div>
                        </div>
                        <div class="product__details__contents__cart__item">
                            <div class="btn-wrapper checkOutBtn">
                                @if($food_menu->is_orderable == 1)
                                    <a href="javascript:void(0)"
                                       class="product__details__contents__cart__btn">{{__('Add to cart')}}</a>
                                @else
                                    <button class="btn btn-warning">{{__('Not Orderable')}}</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product__details__contents__single">
                    <div class="product__details__contents__category">
                        <span class="product__details__contents__category__title">{{__('Category:')}}</span>
                        <div class="product__details__contents__category__list">
                            <a href="javascript:void(0)"
                               class="product__details__contents__category__list__item">{{$food_menu->category?->getTranslation('name',$lang_slug)}}</a>
                        </div>
                    </div>
                    <div class="product__details__contents__category mt-3">
                        <span class="product__details__contents__category__title">{{__('Tags:')}}</span>
                        <div class="product__details__contents__category__list">
                            <a href="javascript:void(0)"
                               class="product__details__contents__category__list__item">{{$food_menu?->getTranslation('tag',$lang_slug)}}</a>
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
