<script>
(function ($) {
    'use strict';

    var modalEl = document.getElementById('blogAiModal');
    if (!modalEl) return;

    var assistUrl = modalEl.getAttribute('data-ai-url');
    var applyUrl = modalEl.getAttribute('data-ai-apply-url');
    var mode = 'generate';
    var bsModal = typeof bootstrap !== 'undefined' && modalEl ? new bootstrap.Modal(modalEl) : null;

    function getBlogId() {
        var v = parseInt(modalEl.getAttribute('data-blog-id') || '0', 10);
        return v > 0 ? v : 0;
    }

    var strGenericError = @json(__('Something went wrong.'));
    var strRequestFailed = @json(__('Request failed.'));
    var strToastrOk = @json(__('AI content applied. Please review before saving.'));
    var strToastrSavedAll = @json(__('All language versions have been saved. You can switch the language selector to review.'));
    var strNeedSaveFirst = @json(__('To improve all languages, save the post first, then use this option on the edit screen.'));

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

    function getCurrentBlogLang() {
        var v = $('select[name="lang"]').val();
        if (v) return v;
        v = $('input[name="lang"]').val();
        if (v) return v;
        return modalEl.getAttribute('data-ai-lang') || 'en';
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

    function applySingleLocaleFields(t) {
        if (!t) return;
        if (t.title) $('input[name="title"]').val(t.title);
        if (typeof t.excerpt !== 'undefined') $('textarea[name="excerpt"]').val(t.excerpt);
        if (t.blog_content) setEditorHtml(t.blog_content);
        if (typeof t.meta_title !== 'undefined') $('input[name="meta_title"]').val(t.meta_title);
        if (typeof t.meta_description !== 'undefined') $('textarea[name="meta_description"]').val(t.meta_description);
        if (typeof t.meta_fb_title !== 'undefined') $('input[name="meta_fb_title"]').val(t.meta_fb_title);
        if (typeof t.meta_fb_description !== 'undefined') $('textarea[name="meta_fb_description"]').val(t.meta_fb_description);
        if (typeof t.meta_tw_title !== 'undefined') $('input[name="meta_tw_title"]').val(t.meta_tw_title);
        if (typeof t.meta_tw_description !== 'undefined') $('textarea[name="meta_tw_description"]').val(t.meta_tw_description);
    }

    $(document).on('click', '.blog-ai-open-modal', function () {
        mode = $(this).data('blog-ai-mode') || 'generate';
        hideError();
        $('#blog_ai_topic').val('');
        $('#blog_ai_instruction').val('');
        var bid = getBlogId();
        var $all = $('#blog_ai_all_langs');
        if (mode === 'refine' && bid === 0) {
            $all.prop('checked', false).prop('disabled', true);
        } else {
            $all.prop('disabled', false);
            if (mode === 'generate') {
                $all.prop('checked', true);
            }
        }
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
        var allLangs = $('#blog_ai_all_langs').is(':checked');
        var bid = getBlogId();
        var lang = getCurrentBlogLang();

        if (mode === 'refine' && allLangs && bid === 0) {
            showError(strNeedSaveFirst);
            return;
        }

        var payload = {
            lang: lang,
            mode: mode,
            all_languages: allLangs
        };
        if (mode === 'generate') {
            payload.topic = $('#blog_ai_topic').val() || '';
        } else {
            payload.instruction = $('#blog_ai_instruction').val() || '';
            payload.current_content = getEditorHtml();
            if (allLangs && bid > 0) {
                payload.blog_id = bid;
            }
        }

        $('#blog-ai-loading').removeClass('d-none');
        $('#blog_ai_run_btn').prop('disabled', true);

        var spinnerDeferred = false;

        function finishSpinner() {
            $('#blog-ai-loading').addClass('d-none');
            $('#blog_ai_run_btn').prop('disabled', false);
        }

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

            if (res.all_languages && res.translations) {
                if (bid > 0 && applyUrl) {
                    spinnerDeferred = true;
                    $.ajax({
                        url: applyUrl,
                        method: 'POST',
                        data: JSON.stringify({
                            blog_id: bid,
                            translations: res.translations,
                            category_id: res.category_id,
                            image_id: res.image_id
                        }),
                        contentType: 'application/json; charset=UTF-8',
                        headers: {
                            'X-CSRF-TOKEN': getCsrf(),
                            'Accept': 'application/json'
                        }
                    }).done(function (applyRes) {
                        if (!applyRes.success) {
                            showError(applyRes.message || strRequestFailed);
                            return;
                        }
                        applySingleLocaleFields(res.translations[lang]);
                        if (res.category_id) {
                            $('select[name="category_id"]').val(String(res.category_id)).trigger('change');
                        }
                        if (res.image_id) {
                            setMediaField('image', res.image_id);
                            if (!$('input[name="meta_image"]').val()) setMediaField('meta_image', res.image_id);
                            if (!$('input[name="meta_fb_image"]').val()) setMediaField('meta_fb_image', res.image_id);
                            if (!$('input[name="meta_tw_image"]').val()) setMediaField('meta_tw_image', res.image_id);
                        }
                        if (typeof toastr !== 'undefined') {
                            toastr.success(applyRes.message || strToastrSavedAll);
                        }
                        if (bsModal) bsModal.hide();
                    }).fail(function (xhr) {
                        var msg = strRequestFailed;
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showError(msg);
                    }).always(finishSpinner);
                } else {
                    var jsonStr = JSON.stringify(res.translations);
                    var $bulk = $('#ai_bulk_translations_json');
                    if ($bulk.length) {
                        $bulk.val(jsonStr);
                    }
                    applySingleLocaleFields(res.translations[lang]);
                    if (res.category_id) {
                        $('select[name="category_id"]').val(String(res.category_id)).trigger('change');
                    }
                    if (res.image_id) {
                        setMediaField('image', res.image_id);
                        if (!$('input[name="meta_image"]').val()) setMediaField('meta_image', res.image_id);
                        if (!$('input[name="meta_fb_image"]').val()) setMediaField('meta_fb_image', res.image_id);
                        if (!$('input[name="meta_tw_image"]').val()) setMediaField('meta_tw_image', res.image_id);
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.success(strToastrOk);
                    }
                    if (bsModal) bsModal.hide();
                }
                return;
            }

            if (res.title) $('input[name="title"]').val(res.title);
            if (typeof res.excerpt !== 'undefined') $('textarea[name="excerpt"]').val(res.excerpt);
            if (res.blog_content) setEditorHtml(res.blog_content);
            if (res.category_id) $('select[name="category_id"]').val(String(res.category_id)).trigger('change');
            if (typeof res.meta_title !== 'undefined') $('input[name="meta_title"]').val(res.meta_title);
            if (typeof res.meta_description !== 'undefined') $('textarea[name="meta_description"]').val(res.meta_description);
            if (typeof res.meta_fb_title !== 'undefined') $('input[name="meta_fb_title"]').val(res.meta_fb_title);
            if (typeof res.meta_fb_description !== 'undefined') $('textarea[name="meta_fb_description"]').val(res.meta_fb_description);
            if (typeof res.meta_tw_title !== 'undefined') $('input[name="meta_tw_title"]').val(res.meta_tw_title);
            if (typeof res.meta_tw_description !== 'undefined') $('textarea[name="meta_tw_description"]').val(res.meta_tw_description);
            if (res.image_id) {
                setMediaField('image', res.image_id);
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
            if (!spinnerDeferred) {
                finishSpinner();
            }
        });
    });
})(jQuery);
</script>
