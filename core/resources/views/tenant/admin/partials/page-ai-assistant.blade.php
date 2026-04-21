@php
    $aiAssistUrl = route(route_prefix().'admin.pages.ai.assist');
    $aiSubmissionsUrl = route(route_prefix().'admin.pages.ai.submissions');
    $currentPageId = $page_id ?? null;
@endphp

<style>
    .page-ai-card{border-radius:16px;border:1px solid rgba(15,23,42,.08);background:linear-gradient(135deg,rgba(248,250,252,.95) 0%,#fff 50%,rgba(236,253,245,.4) 100%);box-shadow:0 10px 34px rgba(15,23,42,.06)}
    #pageAiModal .modal-content{border:none;border-radius:16px;overflow:hidden}
    #pageAiModal .modal-header{border-bottom:none;background:linear-gradient(125deg,#0f766e 0%,#059669 45%,#16a34a 100%);color:#fff}
    #pageAiModal .modal-header .btn-close{filter:invert(1)}
</style>

<div class="page-ai-card mb-4 p-3 p-md-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success" style="width:40px;height:40px;">
                <i class="mdi mdi-robot-outline mdi-24px"></i>
            </span>
            <div>
                <div class="fw-bold text-dark">{{ __('AI custom page assistant') }}</div>
                <small class="text-muted">{{ __('Generate schema-based pages or map raw HTML to data bindings and routes.') }}</small>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-success page-ai-open-modal" data-page-ai-mode="structured">
                <i class="mdi mdi-auto-fix me-1"></i>{{ __('Generate custom page') }}
            </button>
            <button type="button" class="btn btn-outline-dark page-ai-open-modal" data-page-ai-mode="raw_html">
                <i class="mdi mdi-code-tags me-1"></i>{{ __('Map raw HTML') }}
            </button>
        </div>
    </div>
</div>

@if(!empty($currentPageId))
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">{{ __('AI custom page submissions') }}</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="page_ai_refresh_submissions">{{ __('Refresh') }}</button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th style="width:70px">#</th>
                            <th>{{ __('Payload') }}</th>
                            <th style="width:180px">{{ __('Created At') }}</th>
                        </tr>
                    </thead>
                    <tbody id="page_ai_submissions_tbody">
                        <tr><td colspan="3">{{ __('No data yet') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<div class="modal fade" id="pageAiModal" tabindex="-1" aria-hidden="true"
     data-ai-url="{{ $aiAssistUrl }}"
     data-ai-submissions-url="{{ $aiSubmissionsUrl }}"
     data-ai-lang="{{ $lang_slug }}"
     data-page-id="{{ $currentPageId }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">{{ __('AI custom page assistant') }}</h5>
                    <small class="opacity-90">{{ __('Review output before saving the page.') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('Mode') }}</label>
                    <select class="form-control" id="page_ai_mode_select">
                        <option value="structured">{{ __('Structured (schema + bindings)') }}</option>
                        <option value="raw_html">{{ __('Raw HTML mapping') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('Describe what you want') }}</label>
                    <textarea class="form-control" id="page_ai_prompt" rows="4" placeholder="{{ __('Example: Build a custom booking page with name, email, date, service and status list') }}"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">{{ __('Task type') }}</label>
                        <select class="form-control" id="page_ai_generation_goal">
                            <option value="new_page">{{ __('Generate/Regenerate full page') }}</option>
                            <option value="section_edit">{{ __('Edit specific section only') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">{{ __('Target section (optional)') }}</label>
                        <input type="text" class="form-control" id="page_ai_target_section" placeholder="{{ __('Example: Hero, Features, FAQ, Footer') }}">
                    </div>
                </div>
                <div class="mb-3 d-none" id="page_ai_html_wrap">
                    <label class="form-label fw-semibold">{{ __('Paste custom HTML') }}</label>
                    <textarea class="form-control" id="page_ai_raw_html" rows="8" placeholder="{{ __('Paste your HTML template here') }}"></textarea>
                </div>
                <div id="page-ai-error" class="alert alert-danger mt-2 d-none" role="alert"></div>
                <div id="page-ai-loading" class="d-none text-center py-3">
                    <span class="spinner-border text-success"></span>
                    <div class="mt-2 small text-muted">{{ __('Working…') }}</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-success" id="page_ai_run_btn">{{ __('Apply to form') }}</button>
            </div>
        </div>
    </div>
</div>
