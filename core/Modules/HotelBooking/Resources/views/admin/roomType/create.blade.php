@extends(route_prefix().'admin.admin-master')

@section('title')
    {{__('Add New Room Type')}}
@endsection

@section('style')
    <x-media-upload.css />
    <x-niceselect.css />
    <x-summernote.css />
    <style>
        .nav-pills .nav-link {
            margin: 8px 0px !important;
        }
        .col-lg-4.right-side-card {
            background: aliceblue;
        }
    </style>
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
                        <h4 class="card-title mb-5">  {{__('New Room Type')}}</h4>
                    </x-slot>
                    <x-slot name="right" class="d-flex">
                        <form action="" method="get">
                            <x-fields.select name="lang" title="{{__('Language')}}">
                                @foreach(\App\Facades\GlobalLanguage::all_languages() as $lang)
                                    <option value="{{$lang->slug}}" @if($lang->slug === $lang_slug) selected @endif>{{$lang->name}}</option>
                                @endforeach
                            </x-fields.select>
                        </form>
                        <p></p>
                        <x-link-with-popover url="{{route('tenant.admin.room-types.index')}}" extraclass="ml-3">
                            {{__('All RoomType')}}
                        </x-link-with-popover>
                    </x-slot>
                </x-admin.header-wrapper>

                <x-error-msg/>
                <x-flash-msg/>

                <form action="{{route('tenant.admin.room-types.store')}}" method="POST">
                    @csrf
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body p-5">
                                    <h5 class="mb-5">{{ __('Room Type Information') }}</h5>
                                    <div class="row">
                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Name') }}</label>
                                            <input type="hidden" value="{{ $lang_slug }}" name="lang" id="name" class="form-control">
                                            <input type="text" value="{{ old("name") }}" name="name" id="name" class="form-control" placeholder="Enter room-type name">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Amenities') }}</label>
                                            <select name="amenities[]" multiple id="amenity" class="form-control nice-select wide">
                                                <option value="">Select Amenity</option>
                                                @foreach($all_amenities as $amenity)
                                                    <option value="{{ $amenity->id }}" {{ in_array($amenity->id,old("amenities") ?? []) ? "selected" : ""}}>{{ $amenity->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Max Guest') }}</label>
                                            <input type="number" value="{{ old("max_guest") }}" name="max_guest" id="max_guest" class="form-control" placeholder="Enter max guest">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Max Adult') }}</label>
                                            <input type="number" value="{{ old("max_adult") }}" name="max_adult" id="max_adult" class="form-control" placeholder="Enter max adult">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Max Child') }}</label>
                                            <input type="number" value="{{ old("max_child") }}" name="max_child" id="max_child" class="form-control" placeholder="Enter max child">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('No Of Bedroom') }}</label>
                                            <input type="number" value="{{ old("no_of_bedroom") }}" name="no_of_bedroom" id="no_of_bedroom" class="form-control" placeholder="Enter no of bedroom">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('No Of Living Room') }}</label>
                                            <input type="number" value="{{ old("no_of_living_room") }}" name="no_of_living_room" id="no_of_living_room" class="form-control" placeholder="Enter no of living room">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('No Of Bathroom') }}</label>
                                            <input type="number" value="{{ old("no_of_bathroom") }}" name="no_of_bathroom" id="no_of_bathroom" class="form-control" placeholder="Enter no of bathroom">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Base Charge') }}</label>
                                            <input type="number" value="{{ old("base_charge") }}" name="base_charge" id="base_charge" class="form-control" placeholder="Enter base charge">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Breakfast Charge') }}</label>
                                            <input type="number" value="{{ old("breakfast_charge") }}" name="breakfast_charge" id="breakfast_charge" class="form-control" placeholder="Enter breakfast charge">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Lunch price') }}</label>
                                            <input type="number" value="{{ old("lunch_price") }}" name="lunch_price" id="lunch_price" class="form-control" placeholder="Enter lunch price">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Dinner price') }}</label>
                                            <input type="number" value="{{ old("dinner_price") }}" name="dinner_price" id="dinner_price" class="form-control" placeholder="Enter dinner price">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Bed Type') }}</label>
                                            <select name="bed_type" id="dinner_price" class="form-control">
                                                <option value="">Select Bed Type</option>
                                                @foreach($all_bed_type as $bed_type)
                                                    <option value="{{ $bed_type->id }}" {{ $bed_type->id == old("bed_type") ? "selected" : ""}}>{{ $bed_type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="name">{{ __('Description') }}</label>
                                            <textarea name="description" id="description" class="form-control summernote" placeholder="Enter description" cols="30">{!! old("description") !!}</textarea>
                                        </div>
                                        @if(auth('admin')->user()->hasRole('Super Admin'))
                                            @php
                                                $all_hotels = \Modules\HotelBooking\Entities\Hotel::select("id","name")->get();
                                            @endphp
                                            <div class="form-group col-md-3">
                                                <label for="name">{{ __('Select Hotel ') }}</label>
                                                <select name="hotel_id" id="hotel_id" class="form-control">
                                                    <option value="">Select Hotel</option>
                                                    @foreach($all_hotels as $hotel)
                                                        <option value="{{ $hotel->id }}" {{ $hotel->id, old("hotel_id") ? "selected" : ""}}>{{ $hotel->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" id="bed_type_add" class="btn btn-primary" value="Hotel Room Type Add">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <x-media-upload.js />
    <x-niceselect.js />
    <x-summernote.js />

    <script>
        (function ($) {
            "use strict";

            $(document).ready(function ($) {

                $(document).on('change','select[name="lang"]',function (e){
                    $(this).closest('form').trigger('submit');
                    $('input[name="lang"]').val($(this).val());
                });

            });
        })(jQuery)
    </script>
@endsection
