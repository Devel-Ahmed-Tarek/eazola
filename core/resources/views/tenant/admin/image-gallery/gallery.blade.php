@extends(route_prefix().'admin.admin-master')
@section('title') {{__('All Image Gallery')}} @endsection

@section('style')
    <x-media-upload.css/>
    <x-datatable.css/>
    <style>
        .gallery-ai-card{border-radius:16px;border:1px solid rgba(15,23,42,.08);background:linear-gradient(135deg,rgba(248,250,252,.95) 0%,#fff 50%,rgba(224,231,255,.35) 100%);box-shadow:0 10px 40px rgba(15,23,42,.06)}
        #galleryAiModal .modal-content{border:none;border-radius:18px;overflow:hidden;box-shadow:0 24px 60px rgba(15,23,42,.15)}
        #galleryAiModal .modal-header{border-bottom:none;padding:1.2rem 1.4rem;background:linear-gradient(125deg,#4f46e5 0%,#6366f1 45%,#818cf8 100%);color:#fff}
        #galleryAiModal .modal-header .btn-close{filter:invert(1)}
        #galleryAiModal .modal-body{background:#fafafa}
        #galleryAiModal .gallery-ai-inner-panel{background:#fff;border-radius:12px;padding:1rem;border:1px solid rgba(15,23,42,.06)}
    </style>
@endsection

@section('content')
    @php
        $lang_slug = request()->get('lang') ?? \App\Facades\GlobalLanguage::default_slug();
    @endphp
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <x-error-msg/>
                <x-flash-msg/>

                <div class="gallery-ai-card mb-4 p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width:40px;height:40px;"><i class="mdi mdi-robot-outline mdi-24px"></i></span>
                            <div>
                                <div class="fw-bold text-dark">{{ __('AI image gallery assistant') }}</div>
                                <small class="text-muted">{{ __('Draft titles and captions using your site reference and OpenAI.') }}</small>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary btn-sm gallery-ai-open-modal" data-gallery-ai-mode="generate" data-target-form="create"><i class="mdi mdi-auto-fix me-1"></i>{{ __('Generate draft') }}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm gallery-ai-open-modal" data-gallery-ai-mode="refine" data-target-form="edit"><i class="mdi mdi-pencil-outline me-1"></i>{{ __('Improve / edit with AI') }}</button>
                        </div>
                    </div>
                </div>

                <x-admin.header-wrapper>

                    <x-slot name="left">
                        <h4 class="card-title mb-5">{{__('All Image Gallery')}}</h4>
                    <x-bulk-action permissions="testimonial-delete"/>
                    </x-slot>

                    <x-slot name="right" class="d-flex">
                        <form action="" method="get">
                            <x-fields.select name="lang" title="{{__('Language')}}">
                                @foreach(\App\Facades\GlobalLanguage::all_languages() as $lang)
                                    <option value="{{$lang->slug}}" @if($lang->slug === $lang_slug) selected @endif>{{$lang->name}}</option>
                                @endforeach
                            </x-fields.select>
                        </form>
                        <p></p>
                        <button class="btn btn-info btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#new_testimonial">{{__('Add New')}}</button>
                    </x-slot>
                </x-admin.header-wrapper>

                <x-datatable.table>
                    <x-slot name="th">
                        <th class="no-sort">
                            <div class="mark-all-checkbox">
                                <input type="checkbox" class="all-checkbox">
                            </div>
                        </th>
                        <th>{{__('ID')}}</th>
                        <th>{{__('Image')}}</th>
                        <th>{{__('Title')}}</th>
                        <th>{{__('Category')}}</th>
                        <th>{{__('Status')}}</th>
                        <th>{{__('Action')}}</th>
                    </x-slot>

                    <x-slot name="tr">
                        @foreach($all_faqs as $data)
                            <tr>
                                <td>
                                    <x-bulk-delete-checkbox :id="$data->id"/>
                                </td>
                                <td>{{$data->id}}</td>
                                <td>
                                    @php
                                        $testimonial_img = get_attachment_image_by_id($data->image,null,true);
                                    @endphp
                                    {!! render_attachment_preview_for_admin($data->image ?? '') !!}
                                    @php  $img_url = $testimonial_img['img_url']; @endphp
                                </td>

                                <td>
                                    {{ $data->getTranslation('title',$lang_slug)}}
                                </td>
                                <td>{{ optional($data->category)->getTranslation('title',$default_lang) }}</td>
                                <td>{{ \App\Enums\StatusEnums::getText($data->status)  }}</td>
                                <td>
                                @can('testimonial-edit')
                                    <a href="#"
                                       data-bs-toggle="modal"
                                       data-bs-target="#testimonial_item_edit_modal"
                                       class="btn btn-primary btn-xs mb-3 mr-1 testimonial_edit_btn"
                                       data-bs-placement="top"
                                       title="{{__('Edit')}}"
                                       data-id="{{$data->id}}"
                                       data-action="{{route('tenant.admin.image.gallery.update')}}"
                                       data-title="{{$data->getTranslation('title',$default_lang)}}"
                                       data-subtitle="{{$data->getTranslation('subtitle',$default_lang)}}"
                                       data-status="{{$data->status}}"
                                       data-imageid="{{$data->image}}"
                                       data-image="{{$img_url}}"
                                       data-category_id="{{$data->category_id}}">
                                        <i class="las la-edit"></i>
                                    </a>
                                    @endcan
                                    <x-clone-icon :action="route('tenant.admin.image.gallery.clone')" :id="$data->id"/>
                                    <x-delete-popover url="{{route('tenant.admin.image.gallery.delete', $data->id)}}"/>
                                </td>
                            </tr>
                        @endforeach
                    </x-slot>
                </x-datatable.table>
            </div>
        </div>
    </div>

    @can('testimonial-create')
        <div class="modal fade" id="new_testimonial" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">{{__('New Item')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('tenant.admin.image.gallery')}}" method="post" enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="lang" value="{{$default_lang}}">
                            <input type="hidden" name="ai_bulk_translations_json" id="gallery_ai_bulk_translations_json_create" value="">
                            <x-fields.input name="title" label="{{__('Title')}}" />
                            <x-fields.input name="subtitle" label="{{__('Subtitle')}}"/>

                            <x-fields.select name="category_id" title="{{__('Category')}}">
                                @foreach($all_categories as $cat)
                                  <option value="{{$cat->id}}">{{$cat->getTranslation('title',$default_lang)}}</option>
                                @endforeach
                            </x-fields.select>

                            <x-fields.select name="status" title="{{__('Status')}}">
                                <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                            </x-fields.select>

                            <x-fields.media-upload name="image" title="{{__('Image')}}" dimentions="{{__('360x360 px image recommended')}}"/>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Close')}}</button>
                            <button type="submit" class="btn btn-primary">{{__('Save Changes')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @can('testimonial-edit')
        <div class="modal fade" id="testimonial_item_edit_modal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">{{__('Edit Image Gallery Item')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="#" id="testimonial_edit_modal_form" method="post"
                          enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="lang" value="{{$default_lang}}">
                            <input type="hidden" name="id" class="faq_id" value="">
                            <input type="hidden" name="ai_bulk_translations_json" id="gallery_ai_bulk_translations_json_edit" value="">
                            <x-fields.input name="title" label="{{__('Title')}}" class="edit_title" />
                            <x-fields.input name="subtitle" label="{{__('Subtitle')}}" class="edit_subtitle" />

                            <x-fields.select name="category_id" title="{{__('Category')}}" class="edit_cat">
                                @foreach($all_categories as $cat)
                                    <option value="{{$cat->id}}">{{$cat->getTranslation('title',$default_lang)}}</option>
                                @endforeach
                            </x-fields.select>


                            <x-fields.select name="status" title="{{__('Status')}}" class="edit_status">
                                <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                            </x-fields.select>
                            <x-fields.media-upload name="image" title="{{__('Image')}}" dimentions="{{__('360x360 px image recommended')}}" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Close')}}</button>
                            <button type="submit" class="btn btn-primary">{{__('Save Changes')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcan
    <div class="modal fade" id="galleryAiModal" tabindex="-1" aria-hidden="true" data-ai-url="{{ route('tenant.admin.image.gallery.ai.assist') }}" data-ai-lang="{{ $lang_slug }}">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="galleryAiModalTitle">{{ __('AI image gallery assistant') }}</h5>
                        <small class="opacity-90">{{ __('Review output before saving.') }}</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <div class="bg-light rounded-3 p-3 border mb-3">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="gallery_ai_all_langs" checked>
                            <label class="form-check-label fw-semibold" for="gallery_ai_all_langs">{{ __('All site languages at once') }}</label>
                        </div>
                        <small class="text-muted d-block mt-1">{{ __('For improve mode, save the gallery item first.') }}</small>
                    </div>
                    <div id="gallery-ai-panel-generate" class="gallery-ai-inner-panel mb-3">
                        <label class="form-label fw-semibold">{{ __('Topic or brief') }}</label>
                        <textarea class="form-control" id="gallery_ai_topic" rows="5" placeholder="{{ __('e.g. Summer outdoor wedding photoshoot, soft natural light') }}"></textarea>
                    </div>
                    <div id="gallery-ai-panel-refine" class="gallery-ai-inner-panel mb-3 d-none">
                        <label class="form-label fw-semibold">{{ __('How should the content change?') }}</label>
                        <textarea class="form-control" id="gallery_ai_instruction" rows="5"></textarea>
                    </div>
                    <div id="gallery-ai-error" class="alert alert-danger mt-2 d-none" role="alert"></div>
                    <div id="gallery-ai-loading" class="d-none text-center py-3"><span class="spinner-border text-primary"></span><div class="mt-2 small text-muted">{{ __('Working…') }}</div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="gallery_ai_run_btn">{{ __('Apply to form') }}</button>
                </div>
            </div>
        </div>
    </div>
    <x-media-upload.markup/>
@endsection
@section('scripts')
    <x-media-upload.js/>
    <x-datatable.js/>
    <script>
        $(document).ready(function($){
            "use strict";

            <x-bulk-action-js :url="route('tenant.admin.image.gallery.bulk.action')" />
            $(document).on('change','select[name="lang"]',function (e){
                $(this).closest('form').trigger('submit');
                $('input[name="lang"]').val($(this).val());
            });

            $(document).on('click', '.testimonial_edit_btn', function () {
                var el = $(this);
                var id = el.data('id');
                var name = el.data('title');
                var sub = el.data('subtitle');
                var action = el.data('action');


                var form = $('#testimonial_edit_modal_form');
                form.attr('action', action);
                form.find('.faq_id').val(id);
                form.find('.edit_title').val(name);
                form.find('.edit_subtitle').val(sub);
                form.find('.edit_status option[value="' + el.data('status') + '"]').attr('selected', true);
                form.find('.edit_cat option[value="' + el.data('category_id') + '"]').attr('selected', true);

                var image = el.data('image');
                var imageid = el.data('imageid');

                if (imageid != '') {
                    form.find('.media-upload-btn-wrapper .img-wrap').html('<div class="attachment-preview"><div class="thumbnail"><div class="centered">' +
                        '<img class="avatar user-thumb" src="' + image + '" > </div></div></div>');
                    form.find('.media-upload-btn-wrapper input').val(imageid);
                    form.find('.media-upload-btn-wrapper .media_upload_form_btn').text('Change Image');
                }
            });

            var galleryAiMode = 'generate';
            var galleryTargetForm = 'create';
            var galleryModalEl = document.getElementById('galleryAiModal');
            var galleryModal = galleryModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(galleryModalEl) : null;

            function galleryShowError(msg){
                $('#gallery-ai-error').removeClass('d-none').text(msg || @json(__('Request failed.')));
            }
            function galleryHideError(){
                $('#gallery-ai-error').addClass('d-none').text('');
            }
            function galleryGetForm(){
                if (galleryTargetForm === 'edit') return $('#testimonial_edit_modal_form');
                return $('#new_testimonial form');
            }

            $(document).on('click', '.gallery-ai-open-modal', function () {
                galleryAiMode = $(this).data('gallery-ai-mode') || 'generate';
                galleryTargetForm = $(this).data('target-form') || 'create';
                galleryHideError();
                var $all = $('#gallery_ai_all_langs');
                if (galleryAiMode === 'refine' && galleryTargetForm !== 'edit') {
                    $all.prop('checked', false).prop('disabled', true);
                } else {
                    $all.prop('disabled', false);
                    if (galleryAiMode === 'generate') $all.prop('checked', true);
                }
                if (galleryAiMode === 'generate') {
                    $('#gallery-ai-panel-generate').removeClass('d-none');
                    $('#gallery-ai-panel-refine').addClass('d-none');
                    $('#galleryAiModalTitle').text(@json(__('Generate gallery item with AI')));
                } else {
                    $('#gallery-ai-panel-generate').addClass('d-none');
                    $('#gallery-ai-panel-refine').removeClass('d-none');
                    $('#galleryAiModalTitle').text(@json(__('Improve gallery item with AI')));
                }
                if (galleryModal) galleryModal.show();
            });

            $('#gallery_ai_run_btn').on('click', function () {
                galleryHideError();
                var form = galleryGetForm();
                var allLangs = $('#gallery_ai_all_langs').is(':checked');
                var currentLang = $('select[name="lang"]').val() || $('input[name="lang"]').val() || ($('#galleryAiModal').data('ai-lang') || 'en');
                var payload = {
                    mode: galleryAiMode,
                    lang: currentLang,
                    all_languages: allLangs
                };
                if (galleryAiMode === 'generate') {
                    payload.topic = $('#gallery_ai_topic').val() || '';
                } else {
                    payload.instruction = $('#gallery_ai_instruction').val() || '';
                    payload.current_title = galleryTargetForm === 'edit' ? form.find('.edit_title').val() : form.find('input[name="title"]').val();
                    payload.current_subtitle = galleryTargetForm === 'edit' ? form.find('.edit_subtitle').val() : form.find('input[name="subtitle"]').val();
                    if (allLangs && galleryTargetForm === 'edit') {
                        payload.gallery_id = parseInt(form.find('.faq_id').val() || '0', 10);
                    }
                }

                $('#gallery-ai-loading').removeClass('d-none');
                $('#gallery_ai_run_btn').prop('disabled', true);

                $.ajax({
                    url: $('#galleryAiModal').data('ai-url'),
                    method: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json; charset=UTF-8',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (res) {
                    if (!res.success) {
                        galleryShowError(res.message);
                        return;
                    }
                    var dataForLang = (res.all_languages && res.translations) ? (res.translations[currentLang] || {}) : res;
                    if (galleryTargetForm === 'edit') {
                        form.find('.edit_title').val(dataForLang.title || '');
                        form.find('.edit_subtitle').val(dataForLang.subtitle || '');
                        if (res.all_languages && res.translations) {
                            $('#gallery_ai_bulk_translations_json_edit').val(JSON.stringify(res.translations));
                        }
                    } else {
                        form.find('input[name="title"]').val(dataForLang.title || '');
                        form.find('input[name="subtitle"]').val(dataForLang.subtitle || '');
                        if (res.all_languages && res.translations) {
                            $('#gallery_ai_bulk_translations_json_create').val(JSON.stringify(res.translations));
                        }
                    }
                    if (res.category_id !== undefined && res.category_id !== null && String(res.category_id) !== '') {
                        form.find('select[name="category_id"]').val(String(res.category_id));
                    }
                    if (res.image_id) {
                        var $imgIn = form.find('.media-upload-btn-wrapper input[name="image"]');
                        if (!$imgIn.length) {
                            $imgIn = form.find('.media-upload-btn-wrapper input[type="hidden"]').first();
                        }
                        $imgIn.val(String(res.image_id));
                    }
                    if (typeof toastr !== 'undefined') toastr.success(@json(__('AI content applied. Please review before saving.')));
                    if (galleryModal) galleryModal.hide();
                }).fail(function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : @json(__('Request failed.'));
                    galleryShowError(msg);
                }).always(function () {
                    $('#gallery-ai-loading').addClass('d-none');
                    $('#gallery_ai_run_btn').prop('disabled', false);
                });
            });

        });
    </script>
@endsection
