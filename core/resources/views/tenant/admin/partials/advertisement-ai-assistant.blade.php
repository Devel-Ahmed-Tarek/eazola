@php
    $advAiUrl = route('tenant.admin.advertisement.ai.assist');
    $advAiId = $advertisement_id ?? null;
@endphp

<style>
    .adv-ai-card{border-radius:16px;border:1px solid rgba(15,23,42,.08);background:linear-gradient(135deg,rgba(248,250,252,.95) 0%,#fff 50%,rgba(207,250,254,.4) 100%);box-shadow:0 10px 40px rgba(15,23,42,.06)}
    #advAiModal .modal-content{border:none;border-radius:18px;overflow:hidden;box-shadow:0 24px 60px rgba(15,23,42,.15)}
    #advAiModal .modal-header{border-bottom:none;padding:1.2rem 1.4rem;background:linear-gradient(125deg,#0e7490 0%,#0891b2 50%,#22d3ee 100%);color:#fff}
    #advAiModal .modal-header .btn-close{filter:invert(1)}
    #advAiModal .modal-body{background:#fafafa}
    #advAiModal .adv-ai-inner-panel{background:#fff;border-radius:12px;padding:1rem;border:1px solid rgba(15,23,42,.06)}
</style>

@canany(['advertisement-create','advertisement-edit'])
<div class="adv-ai-card mb-4 p-3 p-md-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-info bg-opacity-10 text-info" style="width:40px;height:40px;"><i class="mdi mdi-robot-outline mdi-24px"></i></span>
            <div>
                <div class="fw-bold text-dark">{{ __('AI advertisement assistant') }}</div>
                <small class="text-muted">{{ __('Draft type, size, titles (all languages), and related fields using your site reference.') }}</small>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('advertisement-create')
            <button type="button" class="btn btn-info btn-sm text-white adv-ai-open-modal" data-adv-ai-mode="generate" data-adv-target="new"><i class="mdi mdi-auto-fix me-1"></i>{{ __('Generate draft') }}</button>
            @endcan
            @can('advertisement-edit')
                @if(! empty($advertisement_id))
                <button type="button" class="btn btn-outline-dark btn-sm adv-ai-open-modal" data-adv-ai-mode="refine" data-adv-target="edit"><i class="mdi mdi-pencil-outline me-1"></i>{{ __('Improve / edit with AI') }}</button>
                @endif
            @endcan
        </div>
    </div>
</div>

<div class="modal fade" id="advAiModal" tabindex="-1" aria-hidden="true"
     data-ai-url="{{ $advAiUrl }}"
     data-ai-lang="{{ $lang_slug }}"
     data-advertisement-id="{{ $advAiId }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="advAiModalTitle">{{ __('AI advertisement assistant') }}</h5>
                    <small class="opacity-90">{{ __('Review before saving. For script/Adsense types, verify embed/slot values.') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="bg-light rounded-3 p-3 border mb-3">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="adv_ai_all_langs" checked>
                        <label class="form-check-label fw-semibold" for="adv_ai_all_langs">{{ __('All site languages at once') }}</label>
                    </div>
                    <small class="text-muted d-block mt-1">{{ __('Titles are saved per language. Other fields are shared. Improve-all requires a saved advertisement (edit page).') }}</small>
                </div>
                <div id="adv-ai-panel-generate" class="adv-ai-inner-panel mb-3">
                    <label class="form-label fw-semibold">{{ __('Topic or brief') }}</label>
                    <textarea class="form-control" id="adv_ai_topic" rows="5" placeholder="{{ __('e.g. Leaderboard banner for spring sale, image type, 728x90') }}"></textarea>
                </div>
                <div id="adv-ai-panel-refine" class="adv-ai-inner-panel mb-3 d-none">
                    <label class="form-label fw-semibold">{{ __('How should it change?') }}</label>
                    <textarea class="form-control" id="adv_ai_instruction" rows="5"></textarea>
                </div>
                <div id="adv-ai-error" class="alert alert-danger mt-2 d-none" role="alert"></div>
                <div id="adv-ai-loading" class="d-none text-center py-3"><span class="spinner-border text-info"></span><div class="mt-2 small text-muted">{{ __('Working…') }}</div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-info" id="adv_ai_run_btn">{{ __('Apply to form') }}</button>
            </div>
        </div>
    </div>
</div>
@endcanany
