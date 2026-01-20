<div class="checkOut__wrapper__header">
    <h4 class="checkOut__wrapper__header__title">{{__('Checkout')}}</h4>
    <div class="checkOut__wrapper__header__close checkOutBarClose"><i class="las la-times"></i></div>
</div>
<div class="checkOut__wrapper__body">
    <form action="{{route('tenant.frontend.restaurant.payment.store')}}" method="post">
        @csrf
        <div class="checkOut__wrapper__body__inner">
            @foreach($cart_content as $item)
                <div class="checkOut__wrapper__body__item checkOut__product">
                    <div class="checkOut__wrapper__product">
                        <div class="checkOut__wrapper__product__details">
                            <div class="checkOut__wrapper__product__flex">
                                <div class="checkOut__wrapper__product__thumb">
                                    <img src="{!! render_image_url_by_attachment_id($item->options->image) !!}"
                                         alt="food1" style="width: 80px; height: 80px;"></div>
                                <div class="checkOut__wrapper__product__cart">
                                    <h5 class="checkOut__wrapper__product__cart__title">{{$item->name}}</h5>
                                    <div class="checkOut__wrapper__product__cart__flex">
                                        <div class="product__details__contents__cart__wrapper">
                                            <div class="product__details__contents__cart__wrapper__icon substract"
                                                 id="{{$item->rowId}}" onclick="quantityDecrement(this.id)">
                                                <i class="las la-minus"></i>
                                            </div>
                                            <input type="number"  id="{{$item->rowId}}" value="{{$item->qty}}"
                                                   class="product__details__contents__cart__wrapper__input quantity-input checkout-quantity-input checkout_quantity{{$item->rowId}}">
                                            <div class="product__details__contents__cart__wrapper__icon plus"
                                                 id="{{$item->rowId}}" onclick="quantityIncrement(this.id)"><i
                                                    class="las la-plus"></i></div>
                                        </div>

                                        @foreach($item->options->attribute_values as $value)
                                            <div class="checkOut__wrapper__product__cart__size checkOutSize__wrap">
                                                <span
                                                    class="checkOut__wrapper__product__cart__size__title checkOutSize__click">{{$value}}</span>
                                            </div>
                                        @endforeach

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="checkOut__wrapper__product__close checkOut__product__close" id="{{$item->rowId}}"
                             onclick="cartRemove(this.id)"><i class="las la-times"></i></div>
                    </div>
                </div>
            @endforeach

            <div class="mt-3 checkout-price-section">
                <div class="checkOut__wrapper__body__item">
                    <div class="checkOut__wrapper__estimate">
                        <h6 class="checkOut__wrapper__estimate__title">{{__('Subtotal')}}</h6>
                        <span
                            class="checkOut__wrapper__estimate__price">{{ amount_with_currency_symbol($subtotal)}} </span>
                        <input type="hidden" name="subtotal" value="{{$subtotal}}">
                        <input type="hidden" name="total_tax" value="{{$total_tax}}">
                        <input type="hidden" name="total" value="{{ $subtotal + $total_tax }}">
                    </div>
                </div>
                <div class="checkOut__wrapper__body__item">
                    <div class="checkOut__wrapper__estimate">
                        <h6 class="checkOut__wrapper__estimate__title">{{__('Tax')}}</h6>
                        <span
                            class="checkOut__wrapper__estimate__price">{{ amount_with_currency_symbol($total_tax)}}</span>
                    </div>
                </div>
                <div class="checkOut__wrapper__body__item">
                    <div class="checkOut__wrapper__estimate">
                        <h6 class="checkOut__wrapper__estimate__title">{{__('Total')}}</h6>
                        <span class="checkOut__wrapper__estimate__price">{{ amount_with_currency_symbol($total)}}</span>
                    </div>
                </div>
            </div>
            <div class="checkOut__wrapper__body__item mt-2">
                <div class="checkOut__wrapper__delivery">
                    <h2 class="checkOut__wrapper__delivery__title">DeliveryOption:</h2>
                    <div class="form-check">
                        <input type="radio" id="superFastDelivery" name="delivery_option" value="pick up"
                               class="form-check-input" checked>
                        <label for="superFastDelivery" class="form-check-label">
                            <h6 class="checkOut__wrapper__delivery__title"> {{__('Pick Up')}}</h6>
                        </label>
                    </div>

                    <div class="form-check">
                        <input type="radio" id="fastDelivery" name="delivery_option" value="delivery"
                               class="form-check-input delivary_option">
                        <label for="fastDelivery" class="form-check-label">
                            <h6 class="checkOut__wrapper__delivery__title">{{__('Delivery')}}</h6>
                        </label>
                    </div>
                </div>
            </div>
            <div class="checkOut__wrapper__body__item mt-2 ship_to_a_different_location" style="display: none">
                <div class="checkOut__wrapper__delivery">
                    <h2 class="checkOut__wrapper__delivery__title">{{__('Ship to a different location?:')}} </h2>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ship_to_a_different_location"
                               id="ship_to_a_different_location">
                    </div>
                </div>
            </div>
            @if(get_static_option('menu_pickup_address'))
                <div class="checkOut__wrapper__body__item pickup_address_option">
                    <div class="checkOut__wrapper__address">
                        <h6 class="checkOut__wrapper__address__title">{{__('Pickup Address:')}}</h6>
                        <p class="checkOut__wrapper__address__para mt-1">{{get_static_option('menu_pickup_title')}}, {{get_static_option('menu_pickup_address')}}
                            {{get_static_option('menu_pickup_city')}},  {{get_static_option('menu_pickup_state')}}
                        </p>
                    </div>
                </div>
            @endif
            <div class="checkOut__wrapper__body__item shipping_address_form" style="display: none">
                <div class="checkOut__wrapper__billing">
                    <h4 class="checkOut__wrapper__billing__title"> {{__('Shipping Details')}}</h4>
                    <div class="checkOut__wrapper__billing__flex">
                        <div class="checkOut__wrapper__billing__item">
                            <input class="checkOut__wrapper__billing__item__input" name="shipping_full_name" type="text"
                                   class="form-control" placeholder=" {{__('Full Name')}}">
                        </div>
                        <div class="checkOut__wrapper__billing__item">
                            <input class="checkOut__wrapper__billing__item__input" name="shipping_email" type="text"
                                   class="form-control" placeholder=" {{__('Email Address')}}">
                        </div>
                        <div class="checkOut__wrapper__billing__item">
                            <input class="checkOut__wrapper__billing__item__input" name="shipping_phone" type="tel"
                                   class="form-control" placeholder=" {{__('Phone')}}">
                        </div>
                        <div class="checkOut__wrapper__billing__item">
                            <select class="checkOut__wrapper__billing__item__input" name="shipping_state">
                                @foreach($states as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="checkOut__wrapper__billing__item">
                            <input class="checkOut__wrapper__billing__item__input" name="shipping_city" type="text"
                                   class="form-control" placeholder="City/Town">
                        </div>
                        <div class="checkOut__wrapper__billing__item">
                            <input class="checkOut__wrapper__billing__item__input" name="shipping_address" type="text"
                                   class="form-control" placeholder="Address">
                        </div>
                    </div>
                </div>
            </div>
            <div class="barberShop__bookingInfo__billing__inner">
                <div class="payment_container padding-top-20">
                    {!! render_payment_gateway_for_form() !!}
                </div>
            </div>
            <div class="checkOut__wrapper__body__item">
                <div class="checkOut__wrapper__billing">
                    <h4 class="checkOut__wrapper__billing__title"> {{__('Billing Details')}}</h4>
                    <div class="checkOut__wrapper__billing__flex">
                        <div class="checkOut__wrapper__billing__item">
                            <input class="checkOut__wrapper__billing__item__input" name="full_name" type="text"
                                   class="form-control" placeholder="  {{__('Full Name')}}">
                        </div>
                        <div class="checkOut__wrapper__billing__item">
                            <input class="checkOut__wrapper__billing__item__input" name="email" type="text"
                                   class="form-control" placeholder="  {{__('Email Address')}}">
                        </div>
                        <div class="checkOut__wrapper__billing__item">
                            <input class="checkOut__wrapper__billing__item__input" name="phone" type="tel"
                                   class="form-control" placeholder="  {{__('Phone')}}">
                        </div>
                        <div class="checkOut__wrapper__billing__item">
                            <select class="checkOut__wrapper__billing__item__input" name="state">
                                @foreach($states as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="checkOut__wrapper__billing__item">
                            <input class="checkOut__wrapper__billing__item__input" name="city" type="text"
                                   class="form-control" placeholder=" {{__('City/Town')}}">
                        </div>
                        <div class="checkOut__wrapper__billing__item">
                            <input class="checkOut__wrapper__billing__item__input" name="address" type="text"
                                   class="form-control" placeholder=" {{__('Address1')}}">
                        </div>
                        <div class="checkOut__wrapper__billing__item w-100">
                            <input class="checkOut__wrapper__billing__item__input" name="address_two" type="text"
                                   class="form-control" placeholder=" {{__('Address2')}}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="checkOut__wrapper__btn mt-4" data-bs-toggle="modal" data-bs-target="#orderCompletedModal">
            <button type="submit" class="cmn_btn btn_bg_1 w-100"> {{__('Order Place')}}</button>
        </div>
    </form>
</div>

<script>
    (function($) {
        "use strict";

        $(document).ready(function ()
        {
            $('input[name="ship_to_a_different_location"]').change(function () {
                var ship_to_a_different_location = $('#ship_to_a_different_location').is(':checked');
                if (ship_to_a_different_location) {
                    $('.shipping_address_form').show();
                    $('.pickup_address_option').hide();
                } else {
                    $('.shipping_address_form').hide();
                    $('.pickup_address_option').show();
                }
            });

            // ship to different location show hide

            $('input[name="delivery_option"]').change(function () {
                if ($(this).val() === 'delivery') {
                    // Show the div if the delivery option is selected
                    $('.ship_to_a_different_location').show();
                } else {
                    // Hide the div if the delivery option is not selected
                    $('.ship_to_a_different_location').hide();
                }
            });
        });
    })(jQuery);

</script>

