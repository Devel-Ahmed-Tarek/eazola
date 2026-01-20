@extends(route_prefix().'admin.admin-master')
@section('title') {{__('Pickup Address')}} @endsection
@section('style')
    <x-media-upload.css/>
@endsection
@section('content')
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-5">{{__('Pickup Address')}}</h4>
                <x-error-msg/>
                <x-flash-msg/>
                <form class="forms-sample" method="post" action="{{route('tenant.admin.menu.pickup.address')}}">
                    @csrf
                    <div class="form-group">
                        <label for="timezone">{{__('Title')}}</label>
                        <input class="form-control" type="text" name="menu_pickup_title" value="{{get_static_option('menu_pickup_title')}}">
                        <small class="form-text text-muted"></small>
                    </div>
                    <div class="form-group">
                        <label for="timezone">{{__('Select States')}}</label>
                        <select class="form-control" name="menu_pickup_state" id="state">
                            @foreach($states as $item)
                                <option value="{{$item->name}}" {{$item->name == get_static_option('menu_pickup_state') ? 'selected' : ''}}>{{$item->name}}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted"></small>
                    </div>
                    <div class="form-group">
                        <label for="timezone">{{__('City/twon')}}</label>
                     <input class="form-control" type="text" name="menu_pickup_city" value="{{get_static_option('menu_pickup_city')}}">
                        <small class="form-text text-muted"></small>
                    </div>
                    <div class="form-group">
                        <label for="timezone">{{__('Address')}}</label>
                     <input class="form-control" type="text" name="menu_pickup_address" value="{{get_static_option('menu_pickup_address')}}">
                        <small class="form-text text-muted"></small>
                    </div>
                    <button type="submit" class="btn btn-gradient-primary me-2">{{__('Save Changes')}}</button>
                </form>
            </div>
        </div>
    </div>
    <x-media-upload.markup/>
@endsection
@section('scripts')
    <x-media-upload.js/>
@endsection
