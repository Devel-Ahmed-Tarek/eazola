@canany(['advertisement-create','advertisement-edit'])
<script>
(function ($) {
    'use strict';
    $(document).ready(function () {
        var advAiMode = 'generate';
        var advTarget = 'new';
        var advModalEl = document.getElementById('advAiModal');
        var advModal = advModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(advModalEl) : null;

        function advShowError(msg) {
            $('#adv-ai-error').removeClass('d-none').text(msg || @json(__('Request failed.')));
        }
        function advHideError() {
            $('#adv-ai-error').addClass('d-none').text('');
        }
        function advGetForm() {
            return $('form.js-advertisement-form').first();
        }
        function advSyncModalDataId() {
            var fid = parseInt($('input[name="advertisement_id_for_ai"]').val() || '0', 10);
            $('#advAiModal').attr('data-advertisement-id', fid > 0 ? String(fid) : '');
        }

        $(document).on('click', '.adv-ai-open-modal', function () {
            advAiMode = $(this).data('adv-ai-mode') || 'generate';
            advTarget = $(this).data('adv-target') || 'new';
            advHideError();
            advSyncModalDataId();
            var $all = $('#adv_ai_all_langs');
            if (advAiMode === 'refine' && advTarget !== 'edit') {
                $all.prop('checked', false).prop('disabled', true);
            } else {
                $all.prop('disabled', false);
                if (advAiMode === 'generate') $all.prop('checked', true);
            }
            if (advAiMode === 'generate') {
                $('#adv-ai-panel-generate').removeClass('d-none');
                $('#adv-ai-panel-refine').addClass('d-none');
                $('#advAiModalTitle').text(@json(__('Generate advertisement with AI')));
            } else {
                $('#adv-ai-panel-generate').addClass('d-none');
                $('#adv-ai-panel-refine').removeClass('d-none');
                $('#advAiModalTitle').text(@json(__('Improve advertisement with AI')));
            }
            if (advModal) advModal.show();
        });

        $('#adv_ai_run_btn').on('click', function () {
            advHideError();
            var form = advGetForm();
            if (!form.length) {
                advShowError(@json(__('Form not found.')));
                return;
            }
            var allLangs = $('#adv_ai_all_langs').is(':checked');
            var currentLang = $('select[name="lang"]').val() || $('input[name="lang"]').val() || ($('#advAiModal').data('ai-lang') || 'en');
            var payload = {
                mode: advAiMode,
                lang: currentLang,
                all_languages: allLangs
            };
            if (advAiMode === 'generate') {
                payload.topic = $('#adv_ai_topic').val() || '';
            } else {
                payload.instruction = $('#adv_ai_instruction').val() || '';
                payload.current_title = form.find('input[name="title"], textarea[name="title"]').first().val() || '';
                var aid = parseInt($('#advAiModal').attr('data-advertisement-id') || $('input[name="advertisement_id_for_ai"]').val() || '0', 10);
                if (aid > 0) payload.advertisement_id = aid;
            }

            $('#adv-ai-loading').removeClass('d-none');
            $('#adv_ai_run_btn').prop('disabled', true);

            $.ajax({
                url: $('#advAiModal').data('ai-url'),
                method: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json; charset=UTF-8',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            }).done(function (res) {
                if (!res.success) {
                    advShowError(res.message);
                    return;
                }
                var dataForLang = (res.all_languages && res.translations) ? (res.translations[currentLang] || {}) : res;
                var titleVal = dataForLang.title || res.title || '';
                form.find('input[name="title"], textarea[name="title"]').first().val(titleVal);

                if (res.type) {
                    form.find('select[name="type"]').val(res.type).trigger('change');
                }
                if (res.size) {
                    form.find('select[name="size"]').val(res.size);
                }
                if (res.redirect_url !== undefined) {
                    form.find('input[name="redirect_url"]').val(res.redirect_url || '');
                }
                if (res.slot !== undefined) {
                    form.find('input[name="slot"]').val(res.slot || '');
                }
                if (res.embed_code !== undefined) {
                    form.find('textarea[name="embed_code"]').val(res.embed_code || '');
                }
                if (res.image_id) {
                    var $imgIn = form.find('.media-upload-btn-wrapper input[name="image"]');
                    if (!$imgIn.length) {
                        $imgIn = form.find('.media-upload-btn-wrapper input[type="hidden"]').first();
                    }
                    $imgIn.val(String(res.image_id));
                }

                if (res.all_languages && res.translations) {
                    $('#adv_ai_bulk_translations_json').val(JSON.stringify(res.translations));
                } else {
                    $('#adv_ai_bulk_translations_json').val('');
                }

                if (typeof toastr !== 'undefined') toastr.success(@json(__('AI content applied. Please review before saving.')));
                if (advModal) advModal.hide();
            }).fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : @json(__('Request failed.'));
                advShowError(msg);
            }).always(function () {
                $('#adv-ai-loading').addClass('d-none');
                $('#adv_ai_run_btn').prop('disabled', false);
            });
        });
    });
})(jQuery);
</script>
@endcanany
