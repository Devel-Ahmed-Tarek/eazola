@extends('tenant.admin.admin-master')
@section('title')
    {{__('All Menu Orders')}}
@endsection

@section('style')
    <x-media-upload.css/>
    <x-datatable.css/>
@endsection

@section('content')
    @php
        $lang_slug = request()->get('lang') ?? \App\Facades\GlobalLanguage::default_slug();
    @endphp
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <x-admin.header-wrapper>
                    <x-slot name="left">
                        <h4 class="card-title mb-5">{{__('All Menu Orders')}}</h4>
                    </x-slot>
                </x-admin.header-wrapper>
                <x-error-msg/>
                <x-flash-msg/>
                <x-datatable.table>
                    <x-slot name="th">
                        <th>{{__('ID')}}</th>
                        <th>{{__('Billing Details')}}</th>
                        <th>{{__('Shipping Details')}}</th>
                        <th>{{__('Order Date')}}</th>
                        <th>{{__('Sub Total')}}</th>
                        <th>{{__('Tax(inc)')}}</th>
                        <th>{{__('Payed Amount')}}</th>
                        <th>{{__('Payment Gateway')}}</th>
                        <th>{{__('Payment Status')}}</th>
                        <th>{{__('Status')}}</th>
                        <th>{{__('Action')}}</th>
                    </x-slot>
                    <x-slot name="tr">
                        @foreach($menu_order_infos as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>
                                    @if($item->menu_billing)
                                        <p><b>{{__('Name')}}:</b> {{ $item->menu_billing?->name }}</p>
                                        <p><b>{{__('Email')}}:</b> {{ $item->menu_billing?->email }}</p>
                                        <p><b>{{__('Phone')}}:</b> {{ $item->menu_billing?->phone }}</p>
                                        <p><b>{{__('City')}}
                                                :</b> {{ $item->menu_billing?->getTranslation('city',$lang_slug)}}</p>
                                        <p><b>{{__('Address')}}:</b> {{ @$item->menu_billing?->address}}</p>
                                        <p><b>{{__('Address two')}}:</b> {{ @$item->menu_billing?->address_two}}</p>
                                    @else
                                        <span> {{__('No Data Available')}}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->menu_shipping)
                                        <p><b>{{__('Name')}}:</b> {{ $item->menu_shipping?->name }}</p>
                                        <p><b>{{__('Email')}}:</b> {{ $item->menu_shipping?->email }}</p>
                                        <p><b>{{__('Phone')}}:</b> {{ $item->menu_shipping?->phone }}</p>
                                        <p><b>{{__('City')}}:</b> {{ $item->menu_shipping?->getTranslation('city',$lang_slug)}}</p>
                                        <p><b>{{__('Address')}}:</b> {{ @$item->menu_shipping->address}}</p>
                                    @else
                                        <span>{{__('No Data Available')}}</span>
                                    @endif
                                </td>
                                <td>
                                    <p> {{ $item->order_date }}</p>
                                </td>
                                <td>
                                    <p>{{ amount_with_currency_symbol($item->subtotal) }}</p>
                                </td>
                                <td>
                                    <p> {{ amount_with_currency_symbol($item->tax_amount) }}</p>
                                </td>
                                <td>
                                    <p> {{ amount_with_currency_symbol($item->total_amount) }}</p>
                                </td>
                                <td>
                                    <p>{{ $item->payment_gateway }}</p>
                                </td>
                                <td>
                                    <span
                                        class="badge {{ @$item->payment_log->payment_status == 'complete' ? 'badge-success' : 'badge-warning' }}">
                                        {{ $item->payment_log?->payment_status }}
                                    </span>
                                </td>

                                <td>
                                    @if($item->status == 0)
                                        <span class="badge badge-warning">
                                            {{__('pending')}}
                                        </span>
                                    @elseif($item->status == 1)
                                        <span class="badge badge-success">
                                            {{__('completed')}}
                                        </span>
                                    @elseif($item->status == 2)
                                        <span class="badge badge-info">
                                           {{__(' in-progress')}}
                                        </span>
                                    @elseif($item->status == 3)
                                        <span class="badge badge-danger">
                                            {{__('cancled')}}
                                        </span>
                                    @elseif($item->status == 4)
                                        <span class="badge badge-danger">
                                            {{__('cancel requested')}}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($item->status != 1 || $item->status == 3)
                                        <a href="#"
                                           data-bs-toggle="modal"
                                           data-item-id="{{ $item->id }}"
                                           data-payment-status="{{ $item->payment_status }}"
                                           data-bs-target="#UpdatePaymentStatus"
                                           class="btn update-status btn-sm btn-info">{{__('Update Status')}}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </x-slot>
                </x-datatable.table>

            </div>
        </div>
    </div>

    <div class="modal fade" id="UpdatePaymentStatus" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Update Status')}}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tenant.admin.menu.order.status-update') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="order_id" id="order_id" value="" required>
                            <label>Select Payment Status</label>
                            <select class="form-control form-control-sm" id="status" name="status">
                                <option data-payment-status="0" value="0">{{__('Pending')}}</option>
                                <option data-payment-status="1" value="1">{{__('Complete')}} </option>
                                <option data-payment-status="2" value="2">{{__('In-Progress')}}</option>
                                <option data-payment-status="3" value="3">{{__('Failed')}}</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">{{__('Close')}}</button>
                            <button type="submit" class="btn btn-info btn-sm">{{__('Save changes')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <x-datatable.js/>
    <x-table.btn.swal.js/>
    <script>
        (function($) {
            "use strict";

            $(document).ready(function ($)
            {
                // booking status update
                $(document).on("click", ".update-status", function () {
                    let data = $(this);
                    let id = data.data("item-id")
                    let status = data.data("status")

                    $("#order_id").val(id);

                    $("#status option").each(function ()
                    {
                        if (status == $(this).val()) {
                            $(this).attr("selected", true);
                        }
                    });
                });
            });
            // end

        })(jQuery);
    </script>
@endsection
