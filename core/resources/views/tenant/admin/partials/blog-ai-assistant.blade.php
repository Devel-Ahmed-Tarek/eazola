{{-- مساعد الذكاء الاصطناعي للمدونة — أزرار + نافذة حديثة --}}
@php
    $aiAssistUrl = route(route_prefix().'admin.blog.ai.assist');
    $aiApplyUrl = route(route_prefix().'admin.blog.ai.apply.translations');
    $blogId = $blog_id ?? null;
@endphp

<style>
    .blog-ai-card {
        border-radius: 16px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.95) 0%, rgba(255, 255, 255, 1) 50%, rgba(240, 253, 244, 0.35) 100%);
        box-shadow: 0 10px 40px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .blog-ai-card .blog-ai-actions .btn {
        border-radius: 10px;
        font-weight: 600;
        padding: 0.45rem 1rem;
    }
    #blogAiModal .modal-content {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.15);
    }
    #blogAiModal .modal-header.blog-ai-modal-head {
        border-bottom: none;
        padding: 1.35rem 1.5rem;
        background: linear-gradient(125deg, #0f766e 0%, #059669 45%, #16a34a 100%);
        color: #fff;
    }
    #blogAiModal .modal-header.blog-ai-modal-head .btn-close {
        filter: invert(1);
        opacity: 0.85;
    }
    #blogAiModal .modal-body {
        padding: 1.5rem;
        background: #fafafa;
    }
    #blogAiModal .blog-ai-inner-panel {
        background: #fff;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        border: 1px solid rgba(15, 23, 42, 0.06);
    }
    #blogAiModal .form-control, #blogAiModal .form-check-input {
        border-radius: 10px;
    }
    #blogAiModal .blog-ai-switch-wrap {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px;
        padding: 0.85rem 1rem;
        border: 1px solid rgba(15, 23, 42, 0.06);
    }
    #blogAiModal .modal-footer {
        border-top: 1px solid rgba(15, 23, 42, 0.06);
        padding: 1rem 1.5rem;
        background: #fff;
    }
    #blogAiModal #blog_ai_run_btn {
        border-radius: 10px;
        font-weight: 600;
        padding: 0.55rem 1.35rem;
        background: linear-gradient(125deg, #059669, #16a34a);
        border: none;
    }
    #blogAiModal #blog_ai_run_btn:hover {
        filter: brightness(1.05);
    }
</style>

<div class="blog-ai-card mb-4">
    <div class="p-3 p-md-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success" style="width:40px;height:40px;">
                    <i class="mdi mdi-robot-outline mdi-24px"></i>
                </span>
                <div>
                    <div class="fw-bold text-dark">{{ __('AI blog assistant') }}</div>
                    <small class="text-muted">{{ __('Draft or refine using your site reference and OpenAI.') }}</small>
                </div>
            </div>
            <div class="blog-ai-actions d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-success blog-ai-open-modal" data-blog-ai-mode="generate">
                    <i class="mdi mdi-auto-fix me-1"></i> {{ __('Generate draft') }}
                </button>
                <button type="button" class="btn btn-outline-dark blog-ai-open-modal" data-blog-ai-mode="refine">
                    <i class="mdi mdi-pencil-outline me-1"></i> {{ __('Improve / edit with AI') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="blogAiModal" tabindex="-1" aria-hidden="true"
     data-ai-url="{{ $aiAssistUrl }}"
     data-ai-apply-url="{{ $aiApplyUrl }}"
     data-ai-lang="{{ $lang_slug }}"
     data-blog-id="{{ $blogId }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header blog-ai-modal-head">
                <div>
                    <h5 class="modal-title mb-0" id="blogAiModalTitle">{{ __('AI blog assistant') }}</h5>
                    <small class="opacity-90">{{ __('Review output before publishing.') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="blog-ai-switch-wrap mb-3">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="blog_ai_all_langs" checked>
                        <label class="form-check-label fw-semibold" for="blog_ai_all_langs">{{ __('All site languages at once') }}</label>
                    </div>
                    <small class="text-muted d-block mt-1 ps-1">{{ __('Generates or updates every enabled language in one step. For “improve”, the post must already be saved.') }}</small>
                </div>

                <div id="blog-ai-panel-generate" class="blog-ai-inner-panel mb-3">
                    <label class="form-label fw-semibold">{{ __('Topic or brief') }}</label>
                    <textarea class="form-control" id="blog_ai_topic" rows="5" placeholder="{{ __('What should this article be about? Main points, audience, tone…') }}"></textarea>
                </div>
                <div id="blog-ai-panel-refine" class="blog-ai-inner-panel mb-3 d-none">
                    <label class="form-label fw-semibold">{{ __('How should the content change?') }}</label>
                    <textarea class="form-control" id="blog_ai_instruction" rows="5" placeholder="{{ __('e.g. Shorten paragraphs, add a FAQ section, more formal tone, fix grammar…') }}"></textarea>
                    <small class="text-muted d-block mt-2">{{ __('Applies to the language shown in the editor, or to all languages if the option above is enabled.') }}</small>
                </div>
                <div id="blog-ai-error" class="alert alert-danger mt-2 d-none rounded-3 border-0" role="alert"></div>
                <div id="blog-ai-loading" class="d-none text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <div class="mt-2 small text-muted">{{ __('Working…') }}</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border rounded-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-success" id="blog_ai_run_btn">{{ __('Apply to editor') }}</button>
            </div>
        </div>
    </div>
</div>
