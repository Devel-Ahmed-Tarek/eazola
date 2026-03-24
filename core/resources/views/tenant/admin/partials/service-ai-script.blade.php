<script>
(function ($) {
    'use strict';

    var modalEl = document.getElementById('serviceAiModal');
    if (!modalEl) return;

    var assistUrl = modalEl.getAttribute('data-ai-url');
    var lang = modalEl.getAttribute('data-ai-lang') || 'en';
    var mode = 'generate';
    var bsModal = typeof bootstrap !== 'undefined' && modalEl ? new bootstrap.Modal(modalEl) : null;

    var strRequestFailed = @json(__('Request failed.'));
    var strToastrOk = @json(__('AI content applied. Please review before saving.'));

    function showError(msg) { $('#service-ai-error').removeClass('d-none').text(msg || strRequestFailed); }
    function hideError() { $('#service-ai-error').addClass('d-none').text(''); }
    function getCsrf() { return $('meta[name="csrf-token"]').attr('content'); }

    function setEditorHtml(html) {
        var $div = $('div.summernote').first();
        if ($div.length) {
            try { $div.summernote('code', html); } catch (e) {}
            $div.prev('input[name="description"]').val(html);
        }
    }
    function getEditorHtml() {
        var $div = $('div.summernote').first();
        if (!$div.length) return $('input[name="description"]').val() || '';
        try { return $div.summernote('code') || ''; } catch (e) { return $('input[name="description"]').val() || ''; }
    }
    function setMediaField(name, imageId) {
        var id = parseInt(imageId || 0, 10);
        if (!id) return;
        var $input = $('input[name="' + name + '"]');
        if (!$input.length) return;
        $input.val(id);
        $input.closest('.media-upload-btn-wrapper').find('.media_upload_form_btn').attr('data-imgid', id).text(@json(__('Change')));
    }

    $(document).on('click', '.service-ai-open-modal', function () {
        mode = $(this).data('service-ai-mode') || 'generate';
        hideError();
        $('#service_ai_topic').val('');
        $('#service_ai_instruction').val('');
        if (mode === 'generate') {
            $('#service-ai-panel-generate').removeClass('d-none');
            $('#service-ai-panel-refine').addClass('d-none');
            $('#serviceAiModalTitle').text(@json(__('Generate service draft with AI')));
        } else {
            $('#service-ai-panel-generate').addClass('d-none');
            $('#service-ai-panel-refine').removeClass('d-none');
            $('#serviceAiModalTitle').text(@json(__('Improve service with AI')));
        }
        if (bsModal) bsModal.show();
    });

    $('#service_ai_run_btn').on('click', function () {
        hideError();
        var payload = { lang: lang, mode: mode, current_title: $('input[name="title"]').val() || '' };
        if (mode === 'generate') payload.topic = $('#service_ai_topic').val() || '';
        else { payload.instruction = $('#service_ai_instruction').val() || ''; payload.current_content = getEditorHtml(); }

        $('#service-ai-loading').removeClass('d-none');
        $('#service_ai_run_btn').prop('disabled', true);

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
            if (typeof res.meta_tag !== 'undefined') $('input[name="meta_tag"]').val(res.meta_tag).trigger('change');
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
            $('#service-ai-loading').addClass('d-none');
            $('#service_ai_run_btn').prop('disabled', false);
        });
    });
})(jQuery);
</script>

