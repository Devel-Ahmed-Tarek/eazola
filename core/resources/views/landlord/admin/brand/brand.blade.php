@extends(route_prefix().'admin.admin-master')
@section('title') {{__('All Brands')}} @endsection

@section('style')
    <x-media-upload.css/>
    <x-datatable.css/>
    <style>
        .brand-ai-card{border-radius:16px;border:1px solid rgba(15,23,42,.08);background:linear-gradient(135deg,rgba(248,250,252,.95) 0%,#fff 50%,rgba(254,243,199,.45) 100%);box-shadow:0 10px 40px rgba(15,23,42,.06)}
        #brandAiModal .modal-content{border:none;border-radius:18px;overflow:hidden;box-shadow:0 24px 60px rgba(15,23,42,.15)}
        #brandAiModal .modal-header{border-bottom:none;padding:1.2rem 1.4rem;background:linear-gradient(125deg,#b45309 0%,#d97706 50%,#f59e0b 100%);color:#fff}
        #brandAiModal .modal-header .btn-close{filter:invert(1)}
        #brandAiModal .modal-body{background:#fafafa}
        #brandAiModal .brand-ai-inner-panel{background:#fff;border-radius:12px;padding:1rem;border:1px solid rgba(15,23,42,.06)}
    </style>
@endsection

@section('content')
    @php
        $lang_slug = request()->get('lang') ?? \App\Facades\GlobalLanguage::default_slug();
    @endphp
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <x-admin.header-wrapper>
                    <x-slot name="left">
                        <h4 class="card-title mb-5">{{__('All Brands')}}</h4>
                    <x-bulk-action permissions="brand-delete"/>
                    </x-slot>
                    <x-slot name="right" class="d-flex flex-wrap align-items-center gap-2">
                        <form action="" method="get" class="mb-0">
                            <x-fields.select name="lang" title="{{__('Language')}}">
                                @foreach(\App\Facades\GlobalLanguage::all_languages(1) as $lang)
                                    <option value="{{ $lang->slug }}" @if($lang->slug === $lang_slug) selected @endif>{{ $lang->name }}</option>
                                @endforeach
                            </x-fields.select>
                        </form>
                        <button class="btn btn-info btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#new_brands">{{__('Add New Brand')}}</button>
                    </x-slot>
                </x-admin.header-wrapper>
                <x-error-msg/>
                <x-flash-msg/>

                @canany(['brand-create','brand-edit'])
                <div class="brand-ai-card mb-4 p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 text-warning" style="width:40px;height:40px;"><i class="mdi mdi-robot-outline mdi-24px"></i></span>
                            <div>
                                <div class="fw-bold text-dark">{{ __('AI brand assistant') }}</div>
                                <small class="text-muted">{{ __('Suggest partner URLs per language and link the logo. Uses your site reference and OpenAI.') }}</small>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @can('brand-create')
                            <button type="button" class="btn btn-warning btn-sm text-dark brand-ai-open-modal" data-brand-ai-mode="generate" data-target-form="create"><i class="mdi mdi-auto-fix me-1"></i>{{ __('Generate draft') }}</button>
                            @endcan
                            @can('brand-edit')
                            <button type="button" class="btn btn-outline-dark btn-sm brand-ai-open-modal" data-brand-ai-mode="refine" data-target-form="edit"><i class="mdi mdi-pencil-outline me-1"></i>{{ __('Improve / edit with AI') }}</button>
                            @endcan
                        </div>
                    </div>
                </div>
                @endcanany

                <x-datatable.table>
                    <x-slot name="th">
                        <th class="no-sort">
                            <div class="mark-all-checkbox">
                                <input type="checkbox" class="all-checkbox">
                            </div>
                        </th>
                        <th>{{__('ID')}}</th>
                        <th>{{__('Image')}}</th>
                        <th>{{__('URL')}}</th>
                        <th>{{__('Status')}}</th>
                        <th>{{__('Action')}}</th>
                    </x-slot>
                    <x-slot name="tr">
                        @foreach($all_brands as $data)
                            <tr>
                                <td>
                                    <x-bulk-delete-checkbox :id="$data->id"/>
                                </td>
                                <td>{{$data->id}}</td>
                                <td>
                                    @php
                                        $brands_img = get_attachment_image_by_id($data->image,null,true);
                                    @endphp
                                    {!! render_attachment_preview_for_admin($data->image ?? '') !!}
                                    @php  $img_url = $brands_img['img_url']; @endphp
                                </td>
                                <td>{{ $data->getTranslation('url', $lang_slug) }}</td>

                                <td>{{ \App\Enums\StatusEnums::getText($data->status)  }}</td>
                                <td>
                                    @can('brand-edit')
                                    <a href="#"
                                       data-bs-toggle="modal"
                                       data-bs-target="#brands_item_edit_modal"
                                       class="btn btn-primary btn-xs mb-3 mr-1 brands_edit_btn"
                                       data-bs-placement="top"
                                       title="{{__('Edit')}}"
                                       data-id="{{$data->id}}"
                                       data-action="{{route(route_prefix().'admin.brands.update')}}"
                                       data-url="{{ $data->getTranslation('url', $default_lang) }}"
                                       data-status="{{$data->status}}"
                                       data-imageid="{{$data->image}}"
                                       data-image="{{$img_url}}"
                                    >
                                        <i class="las la-edit"></i>
                                    </a>
                                    @endcan
                                    <x-delete-popover permissions="brand-delete" url="{{route(route_prefix().'admin.brands.delete', $data->id)}}"/>
                                </td>
                            </tr>
                        @endforeach
                    </x-slot>
                </x-datatable.table>

            </div>
        </div>
    </div>

    @can('brand-create')
        <div class="modal fade" id="new_brands" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">{{__('New Brand')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route(route_prefix().'admin.brands')}}" method="post" enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="lang" value="{{ $default_lang }}">
                            <input type="hidden" name="ai_bulk_translations_json" id="brand_ai_bulk_translations_json_create" value="">

                            <x-fields.input name="url" label="{{__('URL')}}" />

                            <x-fields.select name="status" title="{{__('Status')}}">
                                <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                            </x-fields.select>

                            <x-fields.media-upload name="image" title="{{__('Image')}}" dimentions="{{__('151 X 46 px image recommended')}}"/>

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

    @can('brand-edit')

        <div class="modal fade" id="brands_item_edit_modal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">{{__('Edit Brand Item')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="#" id="brands_edit_modal_form" method="post"
                          enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="lang" value="{{ $default_lang }}">
                            <input type="hidden" name="id" class="brands_id" value="">
                            <input type="hidden" name="ai_bulk_translations_json" id="brand_ai_bulk_translations_json_edit" value="">
                            <x-fields.input name="url" label="{{__('URL')}}" class="edit_url" />

                            <x-fields.select name="status" title="{{__('Status')}}" class="edit_status">
                                <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                            </x-fields.select>

                            <x-fields.media-upload name="image" title="{{__('Image')}}" dimentions="{{__('151 X 46 px image recommended')}}"/>
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

    @canany(['brand-create','brand-edit'])
    <div class="modal fade" id="brandAiModal" tabindex="-1" aria-hidden="true" data-ai-url="{{ route(route_prefix().'admin.brands.ai.assist') }}" data-ai-lang="{{ $lang_slug }}">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="brandAiModalTitle">{{ __('AI brand assistant') }}</h5>
                        <small class="opacity-90">{{ __('Review output before saving. Upload a logo image if you prefer a specific asset.') }}</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <div class="bg-light rounded-3 p-3 border mb-3">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="brand_ai_all_langs" checked>
                            <label class="form-check-label fw-semibold" for="brand_ai_all_langs">{{ __('All site languages at once') }}</label>
                        </div>
                        <small class="text-muted d-block mt-1">{{ __('Generates a URL for each enabled language. For improve-all, save the brand first.') }}</small>
                    </div>
                    <div id="brand-ai-panel-generate" class="brand-ai-inner-panel mb-3">
                        <label class="form-label fw-semibold">{{ __('Topic or brief') }}</label>
                        <textarea class="form-control" id="brand_ai_topic" rows="5" placeholder="{{ __('e.g. Official partner link for Acme Corporation, technology sector') }}"></textarea>
                    </div>
                    <div id="brand-ai-panel-refine" class="brand-ai-inner-panel mb-3 d-none">
                        <label class="form-label fw-semibold">{{ __('How should the URL or brand change?') }}</label>
                        <textarea class="form-control" id="brand_ai_instruction" rows="5"></textarea>
                    </div>
                    <div id="brand-ai-error" class="alert alert-danger mt-2 d-none" role="alert"></div>
                    <div id="brand-ai-loading" class="d-none text-center py-3"><span class="spinner-border text-warning"></span><div class="mt-2 small text-muted">{{ __('Working…') }}</div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-warning text-dark" id="brand_ai_run_btn">{{ __('Apply to form') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endcanany
    <x-media-upload.markup/>
@endsection

@section('scripts')
    <x-media-upload.js/>
    <x-datatable.js/>
    <script>
        $(document).ready(function($){
            "use strict";

            <x-bulk-action-js :url="route( route_prefix().'admin.brands.bulk.action')" />
            $(document).on('change','select[name="lang"]',function (e){
                $(this).closest('form').trigger('submit');
                $('input[name="lang"]').val($(this).val());
            });
            //
            $(document).on('click', '.brands_edit_btn', function () {
                var el = $(this);
                var id = el.data('id');
                var url = el.data('url');
                var action = el.data('action');
                var image = el.data('image');
                var imageid = el.data('imageid');

                var form = $('#brands_edit_modal_form');
                form.attr('action', action);
                form.find('.brands_id').val(id);
                form.find('.edit_url').val(url);
                form.find('.edit_status option[value="' + el.data('status') + '"]').attr('selected', true);
                if (imageid != '') {
                    form.find('.media-upload-btn-wrapper .img-wrap').html('<div class="attachment-preview"><div class="thumbnail"><div class="centered"><img class="avatar user-thumb" src="' + image + '" > </div></div></div>');
                    form.find('.media-upload-btn-wrapper input').val(imageid);
                    form.find('.media-upload-btn-wrapper .media_upload_form_btn').text('Change Image');
                }
            });

            @canany(['brand-create','brand-edit'])
            var brandAiMode = 'generate';
            var brandTargetForm = 'create';
            var brandModalEl = document.getElementById('brandAiModal');
            var brandModal = brandModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(brandModalEl) : null;

            function brandShowError(msg){
                $('#brand-ai-error').removeClass('d-none').text(msg || @json(__('Request failed.')));
            }
            function brandHideError(){
                $('#brand-ai-error').addClass('d-none').text('');
            }
            function brandGetForm(){
                if (brandTargetForm === 'edit') return $('#brands_edit_modal_form');
                return $('#new_brands form');
            }

            $(document).on('click', '.brand-ai-open-modal', function () {
                brandAiMode = $(this).data('brand-ai-mode') || 'generate';
                brandTargetForm = $(this).data('target-form') || 'create';
                brandHideError();
                var $all = $('#brand_ai_all_langs');
                if (brandAiMode === 'refine' && brandTargetForm !== 'edit') {
                    $all.prop('checked', false).prop('disabled', true);
                } else {
                    $all.prop('disabled', false);
                    if (brandAiMode === 'generate') $all.prop('checked', true);
                }
                if (brandAiMode === 'generate') {
                    $('#brand-ai-panel-generate').removeClass('d-none');
                    $('#brand-ai-panel-refine').addClass('d-none');
                    $('#brandAiModalTitle').text(@json(__('Generate brand with AI')));
                } else {
                    $('#brand-ai-panel-generate').addClass('d-none');
                    $('#brand-ai-panel-refine').removeClass('d-none');
                    $('#brandAiModalTitle').text(@json(__('Improve brand with AI')));
                }
                if (brandModal) brandModal.show();
            });

            $('#brand_ai_run_btn').on('click', function () {
                brandHideError();
                var form = brandGetForm();
                var allLangs = $('#brand_ai_all_langs').is(':checked');
                var currentLang = $('select[name="lang"]').val() || $('input[name="lang"]').val() || ($('#brandAiModal').data('ai-lang') || 'en');
                var payload = {
                    mode: brandAiMode,
                    lang: currentLang,
                    all_languages: allLangs
                };
                if (brandAiMode === 'generate') {
                    payload.topic = $('#brand_ai_topic').val() || '';
                } else {
                    payload.instruction = $('#brand_ai_instruction').val() || '';
                    payload.current_url = brandTargetForm === 'edit' ? (form.find('.edit_url').val() || '') : (form.find('input[name="url"]').val() || '');
                    if (brandTargetForm === 'edit') {
                        var bid = parseInt(form.find('.brands_id').val() || '0', 10);
                        if (bid > 0) payload.brand_id = bid;
                    }
                }

                $('#brand-ai-loading').removeClass('d-none');
                $('#brand_ai_run_btn').prop('disabled', true);

                $.ajax({
                    url: $('#brandAiModal').data('ai-url'),
                    method: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json; charset=UTF-8',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (res) {
                    if (!res.success) {
                        brandShowError(res.message);
                        return;
                    }
                    var dataForLang = (res.all_languages && res.translations) ? (res.translations[currentLang] || {}) : res;
                    if (brandTargetForm === 'edit') {
                        form.find('.edit_url').val(dataForLang.url || '');
                        if (res.all_languages && res.translations) {
                            $('#brand_ai_bulk_translations_json_edit').val(JSON.stringify(res.translations));
                        } else {
                            $('#brand_ai_bulk_translations_json_edit').val('');
                        }
                    } else {
                        form.find('input[name="url"]').val(dataForLang.url || '');
                        if (res.all_languages && res.translations) {
                            $('#brand_ai_bulk_translations_json_create').val(JSON.stringify(res.translations));
                        } else {
                            $('#brand_ai_bulk_translations_json_create').val('');
                        }
                    }
                    if (res.image_id) {
                        var $imgIn = form.find('.media-upload-btn-wrapper input[name="image"]');
                        if (!$imgIn.length) {
                            $imgIn = form.find('.media-upload-btn-wrapper input[type="hidden"]').first();
                        }
                        $imgIn.val(String(res.image_id));
                    }
                    if (typeof toastr !== 'undefined') toastr.success(@json(__('AI content applied. Please review before saving.')));
                    if (brandModal) brandModal.hide();
                }).fail(function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : @json(__('Request failed.'));
                    brandShowError(msg);
                }).always(function () {
                    $('#brand-ai-loading').addClass('d-none');
                    $('#brand_ai_run_btn').prop('disabled', false);
                });
            });
            @endcanany

        });
    </script>
@endsection
