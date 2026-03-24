<script>
(function ($) {
    'use strict';

    var modalEl = document.getElementById('kbAiModal');
    if (!modalEl) return;

    var assistUrl = modalEl.getAttribute('data-ai-url');
    var lang = modalEl.getAttribute('data-ai-lang') || 'en';
    var mode = 'generate';
    var bsModal = typeof bootstrap !== 'undefined' && modalEl ? new bootstrap.Modal(modalEl) : null;

    var strRequestFailed = @json(__('Request failed.'));
    var strToastrOk = @json(__('AI content applied. Please review before saving.'));

    function showError(msg) { $('#kb-ai-error').removeClass('d-none').text(msg || strRequestFailed); }
    function hideError() { $('#kb-ai-error').addClass('d-none').text(''); }
    function getCsrf() { return $('meta[name="csrf-token"]').attr('content'); }

    function getEditorHtml() {
        var $el = $('textarea.summernote').first();
        if (!$el.length) return '';
        try { return $el.summernote('code') || ''; } catch (e) { return $el.val() || ''; }
    }
    function setEditorHtml(html) {
        var $el = $('textarea.summernote').first();
        if (!$el.length) return;
        try { $el.summernote('code', html); } catch (e) { $el.val(html); }
    }
    function setMediaField(name, imageId) {
        var id = parseInt(imageId || 0, 10);
        if (!id) return;
        var $input = $('input[name="' + name + '"]');
        if (!$input.length) return;
        $input.val(id);
        $input.closest('.media-upload-btn-wrapper').find('.media_upload_form_btn').attr('data-imgid', id).text(@json(__('Change')));
    }

    $(document).on('click', '.kb-ai-open-modal', function () {
        mode = $(this).data('kb-ai-mode') || 'generate';
        hideError();
        $('#kb_ai_topic').val('');
        $('#kb_ai_instruction').val('');
        if (mode === 'generate') {
            $('#kb-ai-panel-generate').removeClass('d-none');
            $('#kb-ai-panel-refine').addClass('d-none');
            $('#kbAiModalTitle').text(@json(__('Generate knowledgebase draft with AI')));
        } else {
            $('#kb-ai-panel-generate').addClass('d-none');
            $('#kb-ai-panel-refine').removeClass('d-none');
            $('#kbAiModalTitle').text(@json(__('Improve knowledgebase with AI')));
        }
        if (bsModal) bsModal.show();
    });

    $('#kb_ai_run_btn').on('click', function () {
        hideError();
        var payload = { lang: lang, mode: mode, current_title: $('input[name="title"]').val() || '' };
        if (mode === 'generate') payload.topic = $('#kb_ai_topic').val() || '';
        else { payload.instruction = $('#kb_ai_instruction').val() || ''; payload.current_content = getEditorHtml(); }

        $('#kb-ai-loading').removeClass('d-none');
        $('#kb_ai_run_btn').prop('disabled', true);

        $.ajax({
            url: assistUrl,
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json; charset=UTF-8',
            headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' }
        }).done(function (res) {
            if (!res.success) { showError(res.message || strRequestFailed); return; }
            if (typeof res.title !== 'undefined') $('input[name="title"]').val(res.title).trigger('keyup');
            if (typeof res.description !== 'undefined') setEditorHtml(res.description);
            if (res.category_id) $('select[name="category_id"]').val(String(res.category_id)).trigger('change');
            $('input[name="meta_title"]').val(res.meta_title || '');
            $('textarea[name="meta_description"]').val(res.meta_description || '');
            $('input[name="meta_fb_title"]').val(res.meta_fb_title || '');
            $('textarea[name="meta_fb_description"]').val(res.meta_fb_description || '');
            $('input[name="meta_tw_title"]').val(res.meta_tw_title || '');
            $('textarea[name="meta_tw_description"]').val(res.meta_tw_description || '');
            if (res.image_id) {
                setMediaField('image', res.image_id);
                if (!$('input[name="meta_image"]').val()) setMediaField('meta_image', res.image_id);
                if (!$('input[name="meta_fb_image"]').val()) setMediaField('meta_fb_image', res.image_id);
                if (!$('input[name="meta_tw_image"]').val()) setMediaField('meta_tw_image', res.image_id);
            }
            if (typeof toastr !== 'undefined') toastr.success(strToastrOk);
            if (bsModal) bsModal.hide();
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : strRequestFailed;
            showError(msg);
        }).always(function () {
            $('#kb-ai-loading').addClass('d-none');
            $('#kb_ai_run_btn').prop('disabled', false);
        });
    });
})(jQuery);
</script>

