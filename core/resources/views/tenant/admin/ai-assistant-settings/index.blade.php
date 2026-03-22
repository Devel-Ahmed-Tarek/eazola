@extends(route_prefix().'admin.admin-master')

@section('title') {{ __('AI site reference') }} @endsection

@section('style')
    <style>
        .ai-ref-card {
            border-radius: 14px;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
        }
        .ai-ref-hint {
            font-size: 13px;
            color: #6c757d;
            line-height: 1.6;
        }
        .ai-ref-textarea {
            min-height: 280px;
            font-size: 14px;
            line-height: 1.55;
        }
    </style>
@endsection

@section('content')
    <div class="col-lg-12">
        <div class="row">
            <div class="col-12 mt-3">
                <x-error-msg/>
                <x-flash-msg/>
            </div>
            <div class="col-lg-10 mx-auto">
                <div class="card ai-ref-card">
                    <div class="card-body">
                        <h4 class="header-title mb-2">
                            <i class="mdi mdi-robot-outline me-1 text-success"></i>
                            {{ __('AI site reference') }}
                        </h4>
                        <p class="ai-ref-hint mb-4">
                            {{ __('Describe your site once here: niche, audience, brand voice, main products or services, policies, and preferred language. This text is sent to the AI as permanent context whenever you use site-aware generation (e.g. articles, social copy).') }}
                        </p>

                        <form action="{{ route('tenant.admin.ai.site.reference.update') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="ai_site_reference" class="form-label fw-semibold">{{ __('Site profile for AI') }}</label>
                                <textarea
                                    class="form-control ai-ref-textarea"
                                    id="ai_site_reference"
                                    name="ai_site_reference"
                                    placeholder="{{ __('Example: We are an online organic food store in Riyadh. Tone: friendly and professional. We ship nationwide. We do not mention competitors. Content language: Arabic (MSA).') }}"
                                >{{ $reference }}</textarea>
                                <small class="form-text text-muted">{{ __('Maximum :max characters.', ['max' => 50000]) }}</small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save-outline me-1"></i>
                                {{ __('Save') }}
                            </button>
                            <a href="{{ route('tenant.admin.general.hub') }}" class="btn btn-light border ms-2">
                                {{ __('Back to General Settings') }}
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
