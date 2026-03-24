<script>
(function ($) {
    'use strict';

    var modalEl = document.getElementById('blogAiModal');
    if (!modalEl) return;

    var assistUrl = modalEl.getAttribute('data-ai-url');
    var lang = modalEl.getAttribute('data-ai-lang') || 'en';
    var mode = 'generate';
    var bsModal = typeof bootstrap !== 'undefined' && modalEl ? new bootstrap.Modal(modalEl) : null;

    var strGenericError = @json(__('Something went wrong.'));
    var strRequestFailed = @json(__('Request failed.'));
    var strToastrOk = @json(__('AI content applied. Please review before saving.'));

    function showError(msg) {
        var box = $('#blog-ai-error');
        box.removeClass('d-none').text(msg || strGenericError);
    }
    function hideError() {
        $('#blog-ai-error').addClass('d-none').text('');
    }

    function getCsrf() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function getSummernoteEl() {
        return $('textarea.summernote').first();
    }

    function getEditorHtml() {
        var $el = getSummernoteEl();
        if (!$el.length) return '';
        try {
            return $el.summernote('code') || '';
        } catch (e) {
            return $el.val() || '';
        }
    }

    function setEditorHtml(html) {
        var $el = getSummernoteEl();
        if (!$el.length) return;
        try {
            $el.summernote('code', html);
        } catch (e) {
            $el.val(html);
        }
    }

    function setMediaField(fieldName, imageId) {
        var id = parseInt(imageId || 0, 10);
        if (!id) return;
        var $input = $('input[name="' + fieldName + '"]');
        if (!$input.length) return;
        $input.val(id);
        var $btn = $input.closest('.media-upload-btn-wrapper').find('.media_upload_form_btn');
        if ($btn.length) {
            $btn.attr('data-imgid', id);
            $btn.text(@json(__('Change')));
        }
    }

    $(document).on('click', '.blog-ai-open-modal', function () {
        mode = $(this).data('blog-ai-mode') || 'generate';
        hideError();
        $('#blog_ai_topic').val('');
        $('#blog_ai_instruction').val('');
        if (mode === 'generate') {
            $('#blog-ai-panel-generate').removeClass('d-none');
            $('#blog-ai-panel-refine').addClass('d-none');
            $('#blogAiModalTitle').text(@json(__('Generate draft with AI')));
        } else {
            $('#blog-ai-panel-generate').addClass('d-none');
            $('#blog-ai-panel-refine').removeClass('d-none');
            $('#blogAiModalTitle').text(@json(__('Improve content with AI')));
        }
        if (bsModal) bsModal.show();
    });

    $('#blog_ai_run_btn').on('click', function () {
        hideError();
        var payload = { lang: lang, mode: mode };
        if (mode === 'generate') {
            payload.topic = $('#blog_ai_topic').val() || '';
        } else {
            payload.instruction = $('#blog_ai_instruction').val() || '';
            payload.current_content = getEditorHtml();
        }

        $('#blog-ai-loading').removeClass('d-none');
        $('#blog_ai_run_btn').prop('disabled', true);

        $.ajax({
            url: assistUrl,
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json; charset=UTF-8',
            headers: {
                'X-CSRF-TOKEN': getCsrf(),
                'Accept': 'application/json'
            }
        }).done(function (res) {
            if (!res.success) {
                showError(res.message || strRequestFailed);
                return;
            }
            if (res.title) {
                $('input[name="title"]').val(res.title);
            }
            if (typeof res.excerpt !== 'undefined') {
                $('textarea[name="excerpt"]').val(res.excerpt);
            }
            if (res.blog_content) {
                setEditorHtml(res.blog_content);
            }
            if (res.category_id) {
                $('select[name="category_id"]').val(String(res.category_id)).trigger('change');
            }
            if (typeof res.meta_title !== 'undefined') {
                $('input[name="meta_title"]').val(res.meta_title);
            }
            if (typeof res.meta_description !== 'undefined') {
                $('textarea[name="meta_description"]').val(res.meta_description);
            }
            if (typeof res.meta_fb_title !== 'undefined') {
                $('input[name="meta_fb_title"]').val(res.meta_fb_title);
            }
            if (typeof res.meta_fb_description !== 'undefined') {
                $('textarea[name="meta_fb_description"]').val(res.meta_fb_description);
            }
            if (typeof res.meta_tw_title !== 'undefined') {
                $('input[name="meta_tw_title"]').val(res.meta_tw_title);
            }
            if (typeof res.meta_tw_description !== 'undefined') {
                $('textarea[name="meta_tw_description"]').val(res.meta_tw_description);
            }
            if (res.image_id) {
                setMediaField('image', res.image_id);
                // Fill SEO images too if empty
                if (!$('input[name="meta_image"]').val()) setMediaField('meta_image', res.image_id);
                if (!$('input[name="meta_fb_image"]').val()) setMediaField('meta_fb_image', res.image_id);
                if (!$('input[name="meta_tw_image"]').val()) setMediaField('meta_tw_image', res.image_id);
            }
            if (typeof toastr !== 'undefined') {
                toastr.success(strToastrOk);
            }
            if (bsModal) bsModal.hide();
        }).fail(function (xhr) {
            var msg = strRequestFailed;
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            showError(msg);
        }).always(function () {
            $('#blog-ai-loading').addClass('d-none');
            $('#blog_ai_run_btn').prop('disabled', false);
        });
    });
})(jQuery);
</script>
