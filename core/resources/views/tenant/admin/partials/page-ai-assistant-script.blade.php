<script>
    (function ($) {
        "use strict";

        const $modal = $('#pageAiModal');
        if (!$modal.length) return;

        const modalInstance = new bootstrap.Modal($modal[0]);
        const aiUrl = $modal.data('ai-url');
        const lang = $modal.data('ai-lang');
        const pageId = $modal.data('page-id') || '';
        const submissionsUrl = $modal.data('ai-submissions-url');
        const csrf = $('meta[name="csrf-token"]').attr('content');

        const $modeSelect = $('#page_ai_mode_select');
        const $prompt = $('#page_ai_prompt');
        const $rawWrap = $('#page_ai_html_wrap');
        const $rawHtml = $('#page_ai_raw_html');
        const $goal = $('#page_ai_generation_goal');
        const $targetSection = $('#page_ai_target_section');
        const $error = $('#page-ai-error');
        const $loading = $('#page-ai-loading');
        const $runBtn = $('#page_ai_run_btn');
        const esc = (v) => String(v === null || typeof v === 'undefined' ? '' : v)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        function toggleModeUI() {
            const mode = $modeSelect.val();
            $rawWrap.toggleClass('d-none', mode !== 'raw_html');
        }

        function showError(msg) {
            $error.text(msg || '{{ __('Something went wrong') }}').removeClass('d-none');
        }

        function clearError() {
            $error.addClass('d-none').text('');
        }

        function setLoading(isLoading) {
            $loading.toggleClass('d-none', !isLoading);
            $runBtn.prop('disabled', isLoading);
        }

        function fillPageForm(data) {
            if (data.title) {
                $('input[name="title"]').val(data.title);
            }
            if (typeof data.page_content !== 'undefined') {
                const $content = $('textarea[name="page_content"]');
                $content.val(data.page_content);
                if ($content.hasClass('summernote')) {
                    $content.summernote('code', data.page_content);
                }
            }

            $('input[name="meta_title"]').val(data.meta_title || '');
            $('textarea[name="meta_description"]').val(data.meta_description || '');
            $('input[name="meta_fb_title"]').val(data.meta_fb_title || '');
            $('textarea[name="meta_fb_description"]').val(data.meta_fb_description || '');
            $('input[name="meta_tw_title"]').val(data.meta_tw_title || '');
            $('textarea[name="meta_tw_description"]').val(data.meta_tw_description || '');

            $('[name="ai_custom_schema_json"]').val(JSON.stringify(data.schema_json || {}));
            $('[name="ai_custom_bindings_json"]').val(JSON.stringify(data.data_bindings || {}));
            $('[name="ai_custom_required_routes_json"]').val(JSON.stringify(data.required_routes || []));
            $('[name="ai_custom_mode"]').val($modeSelect.val() || 'structured');
            $('textarea[name="ai_custom_sanitized_html"]').val(data.page_content || '');
        }

        $(document).on('click', '.page-ai-open-modal', function () {
            const mode = $(this).data('page-ai-mode') || 'structured';
            $modeSelect.val(mode);
            toggleModeUI();
            clearError();
            modalInstance.show();
        });

        $modeSelect.on('change', toggleModeUI);

        $runBtn.on('click', function () {
            clearError();
            setLoading(true);

            $.ajax({
                url: aiUrl,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf},
                data: {
                    mode: $modeSelect.val(),
                    prompt: $prompt.val(),
                    raw_html: $rawHtml.val(),
                    generation_goal: $goal.val() || 'new_page',
                    target_section: $targetSection.val(),
                    current_content: $('textarea[name="page_content"]').val() || '',
                    lang: lang,
                    page_id: pageId
                },
                success: function (res) {
                    if (!res || !res.success) {
                        showError(res && res.message ? res.message : '{{ __('AI generation failed') }}');
                        return;
                    }
                    fillPageForm(res);
                    modalInstance.hide();
                    loadSubmissions();
                },
                error: function (xhr) {
                    const msg = xhr?.responseJSON?.message || '{{ __('AI generation failed') }}';
                    showError(msg);
                },
                complete: function () {
                    setLoading(false);
                }
            });
        });

        function renderSubmissions(rows) {
            const $tbody = $('#page_ai_submissions_tbody');
            if (!$tbody.length) return;

            if (!rows || !rows.length) {
                $tbody.html('<tr><td colspan="3">{{ __('No data yet') }}</td></tr>');
                return;
            }

            let html = '';
            rows.forEach(function (row, idx) {
                const payload = row.payload || {};
                const payloadText = Object.keys(payload).map(function (key) {
                    return '<strong>' + esc(key) + ':</strong> ' + esc(payload[key]);
                }).join('<br>');
                html += '<tr><td>' + (idx + 1) + '</td><td>' + payloadText + '</td><td>' + esc(row.created_at || '') + '</td></tr>';
            });
            $tbody.html(html);
        }

        function loadSubmissions() {
            if (!pageId || !submissionsUrl) return;
            $.ajax({
                url: submissionsUrl,
                method: 'GET',
                data: {page_id: pageId, limit: 25},
                success: function (res) {
                    if (res && res.success) {
                        renderSubmissions(res.rows || []);
                    }
                }
            });
        }

        $('#page_ai_refresh_submissions').on('click', function () {
            loadSubmissions();
        });

        loadSubmissions();
    })(jQuery);
</script>
