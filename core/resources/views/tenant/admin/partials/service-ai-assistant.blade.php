@php
    $aiAssistUrl = route('tenant.admin.service.ai.assist');
@endphp

<div class="alert alert-light border mb-3 service-ai-toolbar">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="text-muted small fw-semibold">
            <i class="mdi mdi-lightbulb text-success"></i> {{ __('AI assistant') }}
        </span>
        <button type="button" class="btn btn-sm btn-outline-success service-ai-open-modal" data-service-ai-mode="generate">
            {{ __('Generate draft') }}
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary service-ai-open-modal" data-service-ai-mode="refine">
            {{ __('Improve / edit with AI') }}
        </button>
    </div>
    <small class="text-muted d-block mt-2 mb-0">{{ __('Uses your AI site reference and OPENAI_API_KEY. Review text before publishing.') }}</small>
</div>

<div class="modal fade" id="serviceAiModal" tabindex="-1" aria-hidden="true" data-ai-url="{{ $aiAssistUrl }}" data-ai-lang="{{ $lang_slug }}">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceAiModalTitle">{{ __('AI service assistant') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="service-ai-panel-generate">
                    <label class="form-label">{{ __('Topic or brief') }}</label>
                    <textarea class="form-control" id="service_ai_topic" rows="5"></textarea>
                </div>
                <div id="service-ai-panel-refine" class="d-none">
                    <label class="form-label">{{ __('How should the content change?') }}</label>
                    <textarea class="form-control" id="service_ai_instruction" rows="5"></textarea>
                    <small class="text-muted d-block mt-2">{{ __('Uses the current text in the editor below.') }}</small>
                </div>
                <div id="service-ai-error" class="alert alert-danger mt-3 d-none" role="alert"></div>
                <div id="service-ai-loading" class="d-none text-center py-3">
                    <span class="spinner-border spinner-border-sm text-success"></span>
                    <span class="ms-2">{{ __('Working…') }}</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-success" id="service_ai_run_btn">{{ __('Apply to editor') }}</button>
            </div>
        </div>
    </div>
</div>

