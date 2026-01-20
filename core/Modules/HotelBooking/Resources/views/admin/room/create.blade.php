@extends(route_prefix().'admin.admin-master')

@section('title')
    {{__('Add New Room')}}
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
                        <h4 class="card-title mb-5">  {{__('New Room')}}</h4>
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
                        <x-link-with-popover url="{{route('tenant.admin.rooms.index')}}" extraclass="ml-3">
                            {{__('All Room')}}
                        </x-link-with-popover>
                    </x-slot>
                </x-admin.header-wrapper>
                <x-error-msg/>
                <x-flash-msg/>
                <form action="{{route('tenant.admin.rooms.store')}}" method="POST">
                    @csrf
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body p-5">
                                    <h5 class="mb-5">{{ __('Room Information') }}</h5>
                                    <div class="row">
                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Name') }}</label>
                                            <input type="text" value="{{ old("name") }}" name="name" id="name" class="form-control" placeholder="Enter room name">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Room Types') }}</label>
                                            <select name="room_type_id" id="room_type_id" class="form-control">
                                                <option value="">Select room type</option>
                                                @foreach($all_room_type as $room_type)
                                                    <option value="{{ $room_type->id }}" {{ $room_type->id == old("room_type_id") ? "selected" : "" }}>{{ $room_type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Base Cost') }}</label>
                                            <input type="number" name="base_cost" id="base_cost" class="form-control"  placeholder="Enter base cost"/>
                                            <input type="hidden" name="lang" id="lang"  value="{{$lang_slug}}" class="form-control" />
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="name">{{ __('Share value') }}</label>
                                            <input type="number" name="share_value" id="share_value" class="form-control" placeholder="Enter share value"/>
                                        </div>
                                        <x-fields.select name="status" title="{{__('Status')}}">
                                            <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                            <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                                        </x-fields.select>

                                        <div class="form-group">
                                            <label for="description">{{ __('Description') }}</label>
                                            <textarea type="text" name="description" class="form-control summernote" id="description" cols="10" rows="5">{!! old('description') !!}</textarea>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <x-landlord-others.edit-media-upload-gallery :label="'Image Gallery'" :name="'image'" :size="'first : 816*356 others 396*164'"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" id="bed_type_add" class="btn btn-primary" value="Room Add">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-media-upload.markup/>
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
