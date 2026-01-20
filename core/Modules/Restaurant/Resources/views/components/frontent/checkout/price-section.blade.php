<div class="checkOut__wrapper__body__item">
    <div class="checkOut__wrapper__estimate">
        <h6 class="checkOut__wrapper__estimate__title">{{__('Subtotal')}}</h6>
        <span class="checkOut__wrapper__estimate__price">{{ amount_with_currency_symbol($subtotal)}}</span>

        <input type="hidden" name="subtotal" value="{{$subtotal}}">
        <input type="hidden" name="total_tax" value="{{$total_tax}}">
        <input type="hidden" name="total" value="{{ $subtotal + $total_tax }}">
    </div>
</div>
<div class="checkOut__wrapper__body__item">
    <div class="checkOut__wrapper__estimate">
        <h6 class="checkOut__wrapper__estimate__title">{{__('Tax')}}</h6>
        <span class="checkOut__wrapper__estimate__price">{{ amount_with_currency_symbol($total_tax)}}</span>
    </div>
</div>
<div class="checkOut__wrapper__body__item">
    <div class="checkOut__wrapper__estimate">
        <h6 class="checkOut__wrapper__estimate__title">{{__('Total')}}</h6>
        <span class="checkOut__wrapper__estimate__price">{{ amount_with_currency_symbol($total)}}</span>
    </div>
</div>
