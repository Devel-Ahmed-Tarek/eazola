@extends(route_prefix().'admin.admin-master')

@section('title')
    {{ __('SideUp Shipping Settings') }}
@endsection

@section('content')
    <div class="col-lg-12 col-ml-12">
        <div class="row">
            <div class="col-12 mt-3">
                <x-error-msg/>
                <x-flash-msg/>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-4">{{ __('SideUp Shipping Integration') }}</h4>

                        <form action="{{ route('tenant.admin.shipping.sideup.settings.update') }}" method="POST">
                            @csrf

                            <div class="form-group mb-3">
                                <label for="api_key">{{ __('API Key') }}</label>
                                <input type="text"
                                       class="form-control"
                                       id="api_key"
                                       name="api_key"
                                       value="{{ old('api_key', $account->api_key ?? '') }}"
                                       placeholder="{{ __('Enter your SideUp API Key') }}">
                            </div>

                            <div class="form-group mb-3">
                                <label for="base_url">{{ __('Base URL') }}</label>
                                <input type="url"
                                       class="form-control"
                                       id="base_url"
                                       name="base_url"
                                       value="{{ old('base_url', $account->base_url ?? '') }}"
                                       placeholder="https://api.sideup.io">
                            </div>

                            <div class="form-group mb-4">
                                <label class="d-block">{{ __('Enable SideUp Integration') }}</label>
                                <label class="switch">
                                    <input type="checkbox" name="enabled"
                                           @if(old('enabled', $account->enabled ?? false)) checked @endif>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary me-2">{{ __('Save Settings') }}</button>
                        </form>

                        <hr class="my-4">

                        <form action="{{ route('tenant.admin.shipping.sideup.settings.test') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-info">
                                {{ __('Test Connection') }}
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

