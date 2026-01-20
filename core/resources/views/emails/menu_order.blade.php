<!doctype html>
<html dir="{{ \App\Facades\GlobalLanguage::user_lang_dir() }}" lang="{{ \App\Facades\GlobalLanguage::user_lang_slug() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <title>{{__('Payment Success For')}} {{get_static_option('site_title')}}</title>
    <style>
        *{
            font-family: 'Montserrat', sans-serif;
        }
        body {
            background-color: #fdfdfd;
        }
        .mail-container {
            max-width: 650px;
            margin: 50px auto;
            text-align: center;
        }

        .mail-container .logo-wrapper {
            padding: 20px 0 20px;
            border-bottom: 5px solid {{get_static_option('site_color')}};
        }
        table {
            margin: 0 auto;
        }
        table {

            border-collapse: collapse;
            width: 100%;
        }

        table td, table th {
            border: 1px solid rgba(0,0,0,.05);
            padding: 10px 20px;
            background-color: #fafafa;
            text-align: left;
            font-size: 14px;
            text-transform: capitalize;
        }

        table tr:nth-child(even){background-color: #f2f2f2;}

        table tr:hover {background-color: #ddd;}

        table th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: {{get_static_option('site_color')}};
            color: #000;
        }
        footer {
            margin: 20px 0;
            font-size: 14px;
        }
        .main-content-wrap {
            background-color: #fff;
            box-shadow: 0 0 15px 0 rgba(0,0,0,.05);
            padding: 30px;
        }

        .main-content-wrap p {
            margin: 0;
            text-align: left;
            font-size: 14px;
            line-height: 26px;
        }

        .main-content-wrap p:first-child {
            margin-bottom: 10px;
        }

        .main-content-wrap .price-wrap {
            font-size: 60px;
            line-height: 70px;
            font-weight: 600;
            margin: 40px 0;
        }
        table {
            margin-bottom: 30px;
        }
        .logo-wrapper img{
            max-width: 200px;
        }

        .renew_heading{
            font-size: 20px;
        }
    </style>
</head>
<body>
<div class="mail-container">
    <div class="logo-wrapper">
        <a href="{{url('/')}}">
            {!! render_image_markup_by_attachment_id(get_static_option('site_logo')) !!}
        </a>
    </div>
    <div class="main-content-wrap">
        <p>{{__('Hello')}} {{$data->name}}</p>
        <p>{{__('Your payment was successful. Order ID')}} #{{$data->id}},
            {{__('Paid Via')}} {{ucfirst(str_replace('_',' ',$data->payment_gateway))}}</p>
        <div class="price-wrap">{{amount_with_currency_symbol($data->total_amount)}}</div>
    </div>

    <div class="product-table-wrap">
        <h3 class="mb-3">{{ __('Ordered Menus') }}</h3>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Quantity') }}</th>
                <th>{{ __('Attributes') }}</th>
                <th>{{ __('Tax') }}</th>
                <th>{{ __('Price(additional inc)') }}</th>
                <th>{{ __('Subtotal') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($data->menu_details ?? [] as $key => $cart_item)
                <tr>
                    <td>
                        {{ $cart_item->name?? __('untitled') }}
                    </td>
                    <td>{{ $cart_item->quantity ?? 0 }}</td>
                        <td>
                            @foreach($cart_item->order_detail_attributes ?? [] as $item)
                                <p>{{ $item->term }}: {{ $item->value}}</p>
                                <p>{{__('extra')}}: {{ $item->additional_price}}</p>
                            @endforeach
                        </td>

                    <td>{{ $cart_item->menu_tax ?? 0 }} %</td>
                    @php
                        $price = $cart_item->price ?? 0;
                    @endphp
                    <td>{{  amount_with_currency_symbol($price) }}</td>
                    <td>
                        {{ amount_with_currency_symbol($cart_item->quantity * $price)  }}
                    </td>
                </tr>
            @endforeach

            <tr>
                <td class="text-end" colspan="4">{{__('Total Tax Amount:')}}</td>
                <td>{{amount_with_currency_symbol($data->tax_amount ?? null)}}</td>
            </tr>
            <tr>
                <td class="text-end" colspan="4">{{__('Total Amount:')}}</td>
                <td>{{amount_with_currency_symbol($data->total_amount ?? null)}} <small class="small">{{__('Incl Total Tax')}}</small></td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="table-wrap">
        <h3 class="mb-3">{{ __('Billing Details') }}</h3>
        <table class="table table-bordered">
            <tbody>
            <tr>
                <th>{{ __('Name') }}</th>
                <td> {{ optional($data->menu_billing)->name }}</td>
            </tr>
            <tr>
                <th>{{ __('Email') }}</th>
                <td>{{ optional($data->menu_billing)->email}}</td>
            </tr>
            <tr>
                <th>{{ __('Adddress-1') }}</th>
                <td>{{  optional($data->menu_billing)->address }}</td>
            </tr>
            <tr>
                <th>{{ __('Adddress-2') }}</th>
                <td>{{  optional($data->menu_billing)->address_two }}</td>

            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-wrap mt-3">
        <h3 class="mb-3">{{ __('Shipping Details') }}</h3>
        <table class="table table-bordered">
            <tbody>
            <tr>
                <th>{{ __('Name') }}</th>
                <td> {{ optional($data->menu_shipping)->name }}</td>
            </tr>
            <tr>
                <th>{{ __('Email') }}</th>
                <td>{{ optional($data->menu_shipping)->email}}</td>
            </tr>
            <tr>
                <th>{{ __('Adddress') }}</th>
                <td>{{  optional($data->menu_shipping)->address }}</td>
            </tr>
            </tbody>
        </table>
    </div>
    <footer>
        {!! get_footer_copyright_text(\App\Facades\GlobalLanguage::default_slug()) !!}
    </footer>
</div>
</body>
</html>


