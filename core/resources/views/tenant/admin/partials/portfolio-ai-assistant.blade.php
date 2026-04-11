@php
    $portfolioAiUrl = route('tenant.admin.portfolio.ai.assist');
    $portfolioAiId = $portfolio_id ?? null;
@endphp

<style>
    .portfolio-ai-card{border-radius:16px;border:1px solid rgba(15,23,42,.08);background:linear-gradient(135deg,rgba(248,250,252,.95) 0%,#fff 50%,rgba(224,231,255,.4) 100%);box-shadow:0 10px 40px rgba(15,23,42,.06)}
    #portfolioAiModal .modal-content{border:none;border-radius:18px;overflow:hidden;box-shadow:0 24px 60px rgba(15,23,42,.15)}
    #portfolioAiModal .modal-header{border-bottom:none;padding:1.2rem 1.4rem;background:linear-gradient(125deg,#4338ca 0%,#6366f1 50%,#818cf8 100%);color:#fff}
    #portfolioAiModal .modal-header .btn-close{filter:invert(1)}
    #portfolioAiModal .modal-body{background:#fafafa}
    #portfolioAiModal .portfolio-ai-inner-panel{background:#fff;border-radius:12px;padding:1rem;border:1px solid rgba(15,23,42,.06)}
</style>

@canany(['portfolio-create','portfolio-edit'])
<div class="portfolio-ai-card mb-4 p-3 p-md-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width:40px;height:40px;"><i class="mdi mdi-robot-outline mdi-24px"></i></span>
            <div>
                <div class="fw-bold text-dark">{{ __('AI portfolio assistant') }}</div>
                <small class="text-muted">{{ __('Draft or refine case-study content, SEO meta, and fields using your site reference.') }}</small>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('portfolio-create')
            <button type="button" class="btn btn-primary portfolio-ai-open-modal" data-portfolio-ai-mode="generate"><i class="mdi mdi-auto-fix me-1"></i>{{ __('Generate draft') }}</button>
            @endcan
            @can('portfolio-edit')
                @if(! empty($portfolioAiId))
                <button type="button" class="btn btn-outline-dark portfolio-ai-open-modal" data-portfolio-ai-mode="refine"><i class="mdi mdi-pencil-outline me-1"></i>{{ __('Improve / edit with AI') }}</button>
                @endif
            @endcan
        </div>
    </div>
</div>

<div class="modal fade" id="portfolioAiModal" tabindex="-1" aria-hidden="true"
     data-ai-url="{{ $portfolioAiUrl }}"
     data-ai-lang="{{ $lang_slug }}"
     data-portfolio-id="{{ $portfolioAiId }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="portfolioAiModalTitle">{{ __('AI portfolio assistant') }}</h5>
                    <small class="opacity-90">{{ __('Review output before publishing.') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="bg-light rounded-3 p-3 border mb-3">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="portfolio_ai_all_langs" checked>
                        <label class="form-check-label fw-semibold" for="portfolio_ai_all_langs">{{ __('All site languages at once') }}</label>
                    </div>
                    <small class="text-muted d-block mt-1">{{ __('For improve-all, save the portfolio first (edit screen).') }}</small>
                </div>
                <div id="portfolio-ai-panel-generate" class="portfolio-ai-inner-panel mb-3">
                    <label class="form-label fw-semibold">{{ __('Topic or brief') }}</label>
                    <textarea class="form-control" id="portfolio_ai_topic" rows="5" placeholder="{{ __('e.g. SaaS dashboard redesign case study for a fintech client') }}"></textarea>
                </div>
                <div id="portfolio-ai-panel-refine" class="portfolio-ai-inner-panel mb-3 d-none">
                    <label class="form-label fw-semibold">{{ __('How should the content change?') }}</label>
                    <textarea class="form-control" id="portfolio_ai_instruction" rows="5"></textarea>
                </div>
                <div id="portfolio-ai-error" class="alert alert-danger mt-2 d-none" role="alert"></div>
                <div id="portfolio-ai-loading" class="d-none text-center py-3"><span class="spinner-border text-primary"></span><div class="mt-2 small text-muted">{{ __('Working…') }}</div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="portfolio_ai_run_btn">{{ __('Apply to editor') }}</button>
            </div>
        </div>
    </div>
</div>
@endcanany
