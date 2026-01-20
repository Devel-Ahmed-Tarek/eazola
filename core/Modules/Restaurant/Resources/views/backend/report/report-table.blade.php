@php
    $colors = ["warning","danger","info","success","dark","secondary"];
    $x = 1;

        $lang_slug = request()->get('lang') ?? \App\Facades\GlobalLanguage::default_slug();

@endphp
<div class="data-tables datatable-primary content-table-wrapper dataTable">
    <table id="all_user_table" class="text-center table">
        <thead class="text-capitalize">
        <th>{{__('ID')}}</th>
        <th>{{__('Billing Name')}}</th>
        <th>{{__('Billing Email')}}</th>
        <th>{{__('Billing Phone')}}</th>
        <th>{{__('Billing Address')}}</th>
        <th>{{__('Billing Address-2')}}</th>
        <th>{{__('Shipping Name')}}</th>
        <th>{{__('Shipping Email')}}</th>
        <th>{{__('Shipping Phone')}}</th>
        <th>{{__('Shipping Address')}}</th>
        <th>{{__('Order Date')}}</th>
        <th>{{__('Total Amount')}}</th>
        <th>{{__('Payment Gateway')}}</th>
        </thead>
        <tbody class="data-table-body">
        @foreach($orderInformations as $key => $orderInfo)
            @foreach($orderInfo as $key => $item)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $item->menu_billing?->name }} </td>
                    <td>{{ $item->menu_billing?->email }} </td>
                    <td>{{ $item->menu_billing?->phone }} </td>
                    <td>{{ @$item->menu_billing->address}}</td>
                    <td>{{ $item->menu_shipping?->name }} </td>
                    <td>{{ $item->menu_shipping?->email }} </td>
                    <td>{{ $item->menu_shipping?->phone }} </td>
                    <td>{{ @$item->menu_shipping->address}}</td>
                    <td> {{ $item->order_date }}</td>
                    <td>{{ $item->total_amount }}</td>
                    <td>{{ $item->payment_gateway }}</td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>
