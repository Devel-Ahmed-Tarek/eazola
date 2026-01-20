@php
    if(!isset($inventorydetails)){
        $inventorydetails = [];
    }
     if(!isset($allAttributes)){
        $allAttributes = [];
    }
     if(!isset($foodMenuAttributes)){
        $foodMenuAttributes = [];
    }


@endphp

<div class="variant_info_container">
    <h5 class="dashboard-common-title-two mb-3">{{ __('Custom Inventory variant') }}</h5>
    <p class="mb-4">
        {{ __('Inventory will be variant of this product.') }} <br>
        {{ __('All inventory stock count will be merged and replace to main stock of
        this product.') }}<br>
        {{ __('Stock count filed is required.') }}
    </p>

    <div class="inventory_items_container">
        @forelse($foodMenuAttributes as $key => $detail)
            <x-restaurant::backend.menu.variant-info.repeater
                :detail="$detail"
                :is-first='false'
                :all-attribute-values="$allAttributeValues"
                :all-available-attributes="$allAttributes" :key="$key" />
        @empty
            <x-restaurant::backend.menu.variant-info.repeater
                    :is-first="true"
                    :all-available-attributes="$allAttributes" />
        @endforelse
    </div>
</div>
