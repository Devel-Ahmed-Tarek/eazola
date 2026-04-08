@php
    $aiAssistUrl = route('tenant.admin.knowledgebase.ai.assist');
    $knowledgebaseId = $knowledgebase_id ?? null;
@endphp

<style>
    .kb-ai-card{border-radius:16px;border:1px solid rgba(15,23,42,.08);background:linear-gradient(135deg,rgba(248,250,252,.95) 0%,#fff 50%,rgba(240,253,244,.35) 100%);box-shadow:0 10px 40px rgba(15,23,42,.06)}
    #kbAiModal .modal-content{border:none;border-radius:18px;overflow:hidden;box-shadow:0 24px 60px rgba(15,23,42,.15)}
    #kbAiModal .modal-header{border-bottom:none;padding:1.2rem 1.4rem;background:linear-gradient(125deg,#0f766e 0%,#059669 45%,#16a34a 100%);color:#fff}
    #kbAiModal .modal-header .btn-close{filter:invert(1)}
    #kbAiModal .modal-body{background:#fafafa}
    #kbAiModal .blog-ai-inner-panel{background:#fff;border-radius:12px;padding:1rem;border:1px solid rgba(15,23,42,.06)}
</style>

<div class="kb-ai-card mb-4 p-3 p-md-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success" style="width:40px;height:40px;"><i class="mdi mdi-robot-outline mdi-24px"></i></span>
            <div><div class="fw-bold text-dark">{{ __('AI knowledgebase assistant') }}</div><small class="text-muted">{{ __('Draft or refine using your site reference and OpenAI.') }}</small></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-success kb-ai-open-modal" data-kb-ai-mode="generate"><i class="mdi mdi-auto-fix me-1"></i>{{ __('Generate draft') }}</button>
            <button type="button" class="btn btn-outline-dark kb-ai-open-modal" data-kb-ai-mode="refine"><i class="mdi mdi-pencil-outline me-1"></i>{{ __('Improve / edit with AI') }}</button>
        </div>
    </div>
</div>

<div class="modal fade" id="kbAiModal" tabindex="-1" aria-hidden="true" data-ai-url="{{ $aiAssistUrl }}" data-ai-lang="{{ $lang_slug }}" data-kb-id="{{ $knowledgebaseId }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="kbAiModalTitle">{{ __('AI knowledgebase assistant') }}</h5>
                    <small class="opacity-90">{{ __('Review output before publishing.') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="bg-light rounded-3 p-3 border mb-3">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="kb_ai_all_langs" checked>
                        <label class="form-check-label fw-semibold" for="kb_ai_all_langs">{{ __('All site languages at once') }}</label>
                    </div>
                    <small class="text-muted d-block mt-1">{{ __('For improve mode, save the article first.') }}</small>
                </div>
                <div id="kb-ai-panel-generate" class="blog-ai-inner-panel mb-3">
                    <label class="form-label fw-semibold">{{ __('Topic or brief') }}</label>
                    <textarea class="form-control" id="kb_ai_topic" rows="5"></textarea>
                </div>
                <div id="kb-ai-panel-refine" class="blog-ai-inner-panel mb-3 d-none">
                    <label class="form-label fw-semibold">{{ __('How should the content change?') }}</label>
                    <textarea class="form-control" id="kb_ai_instruction" rows="5"></textarea>
                </div>
                <div id="kb-ai-error" class="alert alert-danger mt-2 d-none" role="alert"></div>
                <div id="kb-ai-loading" class="d-none text-center py-3"><span class="spinner-border text-success"></span><div class="mt-2 small text-muted">{{ __('Working…') }}</div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-success" id="kb_ai_run_btn">{{ __('Apply to editor') }}</button>
            </div>
        </div>
    </div>
</div>
