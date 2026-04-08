<script>
(function ($) {
    'use strict';
    var modalEl = document.getElementById('serviceAiModal');
    if (!modalEl) return;

    var assistUrl = modalEl.getAttribute('data-ai-url');
    var mode = 'generate';
    var bsModal = typeof bootstrap !== 'undefined' && modalEl ? new bootstrap.Modal(modalEl) : null;

    function getServiceId(){ var v=parseInt(modalEl.getAttribute('data-service-id')||'0',10); return v>0?v:0; }
    function getCurrentLang(){ return $('select[name="lang"]').val() || $('input[name="lang"]').val() || modalEl.getAttribute('data-ai-lang') || 'en'; }
    function showError(msg){ $('#service-ai-error').removeClass('d-none').text(msg || @json(__('Request failed.'))); }
    function hideError(){ $('#service-ai-error').addClass('d-none').text(''); }
    function getCsrf(){ return $('meta[name="csrf-token"]').attr('content'); }

    function setEditorHtml(html){ var $div=$('div.summernote').first(); if($div.length){ try{$div.summernote('code',html);}catch(e){} $div.prev('input[name="description"]').val(html);} }
    function getEditorHtml(){ var $div=$('div.summernote').first(); if(!$div.length) return $('input[name="description"]').val()||''; try{return $div.summernote('code')||'';}catch(e){return $('input[name="description"]').val()||'';} }
    function setMediaField(name,id){ id=parseInt(id||0,10); if(!id)return; var $i=$('input[name="'+name+'"]'); if(!$i.length)return; $i.val(id); $i.closest('.media-upload-btn-wrapper').find('.media_upload_form_btn').attr('data-imgid',id).text(@json(__('Change'))); }

    function applySingleLocaleFields(t){
        if(!t) return;
        if(typeof t.title!=='undefined') $('input[name="title"]').val(t.title).trigger('keyup');
        if(typeof t.description!=='undefined') setEditorHtml(t.description);
        if(typeof t.meta_tag!=='undefined') $('input[name="meta_tag"]').val(t.meta_tag).trigger('change');
        if(typeof t.meta_title!=='undefined') $('input[name="meta_title"]').val(t.meta_title);
        if(typeof t.meta_description!=='undefined') $('textarea[name="meta_description"]').val(t.meta_description);
        if(typeof t.meta_fb_title!=='undefined') $('input[name="meta_fb_title"]').val(t.meta_fb_title);
        if(typeof t.meta_fb_description!=='undefined') $('textarea[name="meta_fb_description"]').val(t.meta_fb_description);
        if(typeof t.meta_tw_title!=='undefined') $('input[name="meta_tw_title"]').val(t.meta_tw_title);
        if(typeof t.meta_tw_description!=='undefined') $('textarea[name="meta_tw_description"]').val(t.meta_tw_description);
    }

    $(document).on('click','.service-ai-open-modal',function(){
        mode=$(this).data('service-ai-mode')||'generate';
        hideError();
        $('#service_ai_topic,#service_ai_instruction').val('');
        var sid=getServiceId();
        var $all=$('#service_ai_all_langs');
        if(mode==='refine' && sid===0){ $all.prop('checked',false).prop('disabled',true); }
        else { $all.prop('disabled',false); if(mode==='generate') $all.prop('checked',true); }
        if(mode==='generate'){ $('#service-ai-panel-generate').removeClass('d-none'); $('#service-ai-panel-refine').addClass('d-none'); }
        else { $('#service-ai-panel-generate').addClass('d-none'); $('#service-ai-panel-refine').removeClass('d-none'); }
        if(bsModal) bsModal.show();
    });

    $('#service_ai_run_btn').on('click', function(){
        hideError();
        var allLangs=$('#service_ai_all_langs').is(':checked');
        var sid=getServiceId();
        if(mode==='refine' && allLangs && sid===0){ showError(@json(__('To improve all languages, save the service first, then use this option on edit screen.'))); return; }

        var payload={ lang:getCurrentLang(), mode:mode, all_languages:allLangs, current_title:$('input[name="title"]').val()||'' };
        if(mode==='generate') payload.topic=$('#service_ai_topic').val()||'';
        else { payload.instruction=$('#service_ai_instruction').val()||''; payload.current_content=getEditorHtml(); if(allLangs&&sid>0) payload.service_id=sid; }

        $('#service-ai-loading').removeClass('d-none');
        $('#service_ai_run_btn').prop('disabled',true);

        $.ajax({url:assistUrl,method:'POST',data:JSON.stringify(payload),contentType:'application/json; charset=UTF-8',headers:{'X-CSRF-TOKEN':getCsrf(),'Accept':'application/json'}})
         .done(function(res){
            if(!res.success){ showError(res.message || @json(__('Request failed.'))); return; }
            var lang=getCurrentLang();
            if(res.all_languages && res.translations){
                var $bulk=$('#service_ai_bulk_translations_json');
                if($bulk.length) $bulk.val(JSON.stringify(res.translations));
                applySingleLocaleFields(res.translations[lang]);
            } else {
                applySingleLocaleFields(res);
            }
            if(res.category_id) $('select[name="category_id"]').val(String(res.category_id)).trigger('change');
            if(res.image_id){
                setMediaField('image',res.image_id);
                if(!$('input[name="meta_image"]').val()) setMediaField('meta_image',res.image_id);
                if(!$('input[name="meta_fb_image"]').val()) setMediaField('meta_fb_image',res.image_id);
                if(!$('input[name="meta_tw_image"]').val()) setMediaField('meta_tw_image',res.image_id);
            }
            if(typeof toastr!=='undefined') toastr.success(@json(__('AI content applied. Please review before saving.')));
            if(bsModal) bsModal.hide();
         }).fail(function(xhr){
            var msg=(xhr.responseJSON&&xhr.responseJSON.message)?xhr.responseJSON.message:@json(__('Request failed.')); showError(msg);
         }).always(function(){
            $('#service-ai-loading').addClass('d-none');
            $('#service_ai_run_btn').prop('disabled',false);
         });
    });
})(jQuery);
</script>
