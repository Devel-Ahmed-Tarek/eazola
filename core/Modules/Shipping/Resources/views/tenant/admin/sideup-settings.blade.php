@extends(route_prefix().'admin.admin-master')

@section('title')
    {{ __('SideUp Shipping Settings') }}
@endsection

@section('content')
    @php
        $meta = $account->meta ?? [];
        $authType = old('auth_type', $meta['auth_type'] ?? 'api_key');
    @endphp
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
                        <p class="text-muted small mb-4">
                            {{ __('You can connect either with API Key + Base URL, or with Email + Password (Legacy). You can also load credentials from a legacy JSON file containing: email, password, and optionally base_url.') }}
                        </p>

                        <form action="{{ route('tenant.admin.shipping.sideup.settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group mb-3">
                                <label for="base_url">{{ __('Base URL') }}</label>
                                <input type="url"
                                       class="form-control"
                                       id="base_url"
                                       name="base_url"
                                       value="{{ old('base_url', $account->base_url ?? '') }}"
                                       placeholder="https://api.sideup.io">
                            </div>

                            <div class="form-group mb-3">
                                <label class="d-block">{{ __('Connection method') }}</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="auth_type" id="auth_api_key" value="api_key" {{ $authType === 'api_key' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auth_api_key">{{ __('API Key') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="auth_type" id="auth_email_password" value="email_password" {{ $authType === 'email_password' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auth_email_password">{{ __('Email + Password (Legacy)') }}</label>
                                </div>
                            </div>

                            <div id="api_key_fields" class="{{ $authType === 'api_key' ? '' : 'd-none' }}">
                                <div class="form-group mb-3">
                                    <label for="api_key">{{ __('API Key') }}</label>
                                    <input type="text"
                                           class="form-control"
                                           id="api_key"
                                           name="api_key"
                                           value="{{ old('api_key', $account->api_key ?? '') }}"
                                           placeholder="{{ __('Enter your SideUp API Key') }}">
                                </div>
                            </div>

                            <div id="email_password_fields" class="{{ $authType === 'email_password' ? '' : 'd-none' }}">
                                <div class="form-group mb-3">
                                    <label for="email">{{ __('Email') }}</label>
                                    <input type="email"
                                           class="form-control"
                                           id="email"
                                           name="email"
                                           value="{{ old('email', $meta['email'] ?? '') }}"
                                           placeholder="{{ __('SideUp account email') }}">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="password">{{ __('Password') }}</label>
                                    <input type="password"
                                           class="form-control"
                                           id="password"
                                           name="password"
                                           placeholder="{{ __('Leave blank to keep current') }}">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="legacy_json">{{ __('Or load from legacy JSON file') }}</label>
                                    <input type="file"
                                           class="form-control"
                                           id="legacy_json"
                                           name="legacy_json"
                                           accept=".json">
                                    <small class="form-text text-muted">{{ __('JSON keys: email, password, and optionally base_url') }}</small>
                                </div>
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
    <script>
        document.querySelectorAll('input[name="auth_type"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var isApiKey = this.value === 'api_key';
                document.getElementById('api_key_fields').classList.toggle('d-none', !isApiKey);
                document.getElementById('email_password_fields').classList.toggle('d-none', isApiKey);
            });
        });
    </script>
@endsection

