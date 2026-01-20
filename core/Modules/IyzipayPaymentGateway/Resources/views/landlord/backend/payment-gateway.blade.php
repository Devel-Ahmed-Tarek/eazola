@extends(route_prefix().'admin.admin-master')
@section('title')
    {{__('Payment Settings')}}
@endsection
@section('style')
<x-summernote.css/>
    <x-media-upload.css/>
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-12 mt-5">
                <x-flash-msg/>
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">{{__("Payment Gateway Settings") .' - '. str_replace('_',' ', ucfirst($payment_gateway_info['name']))}}</h4>
                        <x-error-msg/>
                        <form action="{{route(route_prefix().'admin.payment.settings.iyzipay.settings-update')}}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="accordion-wrapper">
                                        <div id="accordion-payment">
                                                <div class="card">
                                                    <div id="iyzipay_settings">
                                                        <h3 class="mt-2 mb-0">
                                                            {{ str_replace('_',' ', ucfirst($payment_gateway_info['name'])) }}
                                                        </h3>
                                                    </div>

                                                    <div id="settings_content_iyzipay">
                                                        <div class="card-body">

                                                            @if(!empty($payment_gateway_info['description']))
                                                                <div class="payment-notice alert alert-warning">
                                                                    <p>{{$payment_gateway_info['description']}}</p>
                                                                </div>
                                                            @endif

                                                            <div class="form-group">
                                                                <label for="instamojo_gateway"><strong>{{__('Enable/Disable '. ucfirst($payment_gateway_info['name']))}}</strong></label>
                                                                <input type="hidden" name="{{$payment_gateway_info['name']}}_payment_gateway_status">
                                                                <label class="switch">
                                                                    <input type="checkbox" name="{{$payment_gateway_info['name']}}_payment_gateway_status"  @if($payment_gateway_info['status']) checked @endif >
                                                                    <span class="slider onff"></span>
                                                                </label>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="instamojo_test_mode"><strong>{{sprintf(__('Enable Test Mode %s'),ucfirst($payment_gateway_info['test_mode']))}}</strong></label>
                                                                <input type="hidden" name="{{$payment_gateway_info['name']}}_payment_gateway_test_mode">
                                                                <label class="switch">
                                                                    <input type="checkbox" name="{{$payment_gateway_info['name']}}_payment_gateway_test_mode" @if($payment_gateway_info['test_mode']) checked @endif>
                                                                    <span class="slider onff"></span>
                                                                </label>
                                                            </div>

{{--                                                            <x-landlord-others.edit-media-upload-image label="{{ sprintf(__('%s Logo'),__(ucfirst($gateway->name)))}}" name="{{$gateway->name.'_logo'}}" :value="$gateway->image" size="100*100"/>--}}

                                                            @php
                                                                $credentials = !empty($payment_gateway_info['credentials']) ? json_decode($payment_gateway_info['credentials']) : [];
                                                            @endphp

                                                            @foreach($credentials as $cre_name =>  $cre_value)
                                                                <div class="form-group">
                                                                    <label >{{ str_replace('_', ' ' , ucfirst($cre_name)) }}</label>
                                                                    <input type="text" name="{{$payment_gateway_info['name'].'_payment_gateway_'.$cre_name}}" value="{{$cre_value}}" class="form-control">
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

   <x-media-upload.markup/>

@endsection
@section('scripts')
  <x-summernote.js/>
  <x-media-upload.js/>
    <script>
        (function($){
            "use strict";
            $(document).ready(function ($) {
                $('.summernote').summernote({
                    height: 200,   //set editable area's height
                    codemirror: { // codemirror options
                        theme: 'monokai'
                    },
                    callbacks: {
                        onChange: function(contents, $editable) {
                            $(this).prev('input').val(contents);
                        }
                    }
                });
                if($('.summernote').length > 0){
                    $('.summernote').each(function(index,value){
                        $(this).summernote('code', $(this).data('content'));
                    });
                }
            });
        })(jQuery);


    </script>
@endsection
