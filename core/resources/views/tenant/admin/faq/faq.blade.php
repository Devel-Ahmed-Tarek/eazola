@extends(route_prefix().'admin.admin-master')
@section('title') {{__('All Faq')}} @endsection

@section('style')
    <x-media-upload.css/>
    <x-datatable.css/>
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

                <x-admin.header-wrapper>

                    <x-slot name="left">
                        <h4 class="card-title mb-5">{{__('All Faq')}}</h4>
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
                <div class="alert alert-light border mb-3 faq-ai-toolbar">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="text-muted small fw-semibold">
                            <i class="mdi mdi-lightbulb text-success"></i> {{ __('AI assistant') }}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-success faq-ai-open-modal" data-faq-ai-mode="generate" data-target-form="create">{{ __('Generate draft') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-primary faq-ai-open-modal" data-faq-ai-mode="refine" data-target-form="edit">{{ __('Improve / edit with AI') }}</button>
                    </div>
                    <small class="text-muted d-block mt-2 mb-0">{{ __('Uses your AI site reference and OPENAI_API_KEY. Review text before saving.') }}</small>
                </div>

                <x-datatable.table>
                    <x-slot name="th">
                        <th class="no-sort">
                            <div class="mark-all-checkbox">
                                <input type="checkbox" class="all-checkbox">
                            </div>
                        </th>
                        <th>{{__('ID')}}</th>
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
                                       data-action="{{route('tenant.admin.faq.update')}}"
                                       data-title="{{$data->getTranslation('title',$default_lang)}}"
                                       data-description="{{$data->getTranslation('description',$default_lang)}}"
                                       data-status="{{$data->status}}"
                                       data-category_id="{{$data->category_id}}">
                                        <i class="las la-edit"></i>
                                    </a>
                                    @endcan
                                    <x-delete-popover url="{{route('tenant.admin.faq.delete', $data->id)}}"/>
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
                        <h5 class="modal-title" id="staticBackdropLabel">{{__('New Faq')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('tenant.admin.faq')}}" method="post" enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="lang" value="{{$default_lang}}">
                            <x-fields.input name="title" label="{{__('Title')}}" />
                            <x-fields.textarea name="description" label="{{__('Description')}}"/>

                            <x-fields.select name="category_id" title="{{__('Category')}}">
                                @foreach($all_categories as $cat)
                                  <option value="{{$cat->id}}">{{$cat->getTranslation('title',$default_lang)}}</option>
                                @endforeach
                            </x-fields.select>

                            <x-fields.select name="status" title="{{__('Status')}}">
                                <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                            </x-fields.select>
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
                        <h5 class="modal-title" id="staticBackdropLabel">{{__('Edit Faq Item')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="#" id="testimonial_edit_modal_form" method="post"
                          enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="lang" value="{{$default_lang}}">
                            <input type="hidden" name="id" class="faq_id" value="">
                            <x-fields.input name="title" label="{{__('Title')}}" class="edit_title" />

                            <x-fields.textarea name="description" label="{{__('Description')}}" class="edit_description"/>

                            <x-fields.select name="category_id" title="{{__('Category')}}" class="edit_cat">
                                @foreach($all_categories as $cat)
                                    <option value="{{$cat->id}}">{{$cat->getTranslation('title',$default_lang)}}</option>
                                @endforeach
                            </x-fields.select>


                            <x-fields.select name="status" title="{{__('Status')}}" class="edit_status">
                                <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                            </x-fields.select>

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
    <x-media-upload.markup/>
    <div class="modal fade" id="faqAiModal" tabindex="-1" aria-hidden="true" data-ai-url="{{ route('tenant.admin.faq.ai.assist') }}" data-ai-lang="{{ $lang_slug }}">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="faqAiModalTitle">{{ __('AI FAQ assistant') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="faq-ai-panel-generate">
                        <label class="form-label">{{ __('Topic or brief') }}</label>
                        <textarea class="form-control" id="faq_ai_topic" rows="4"></textarea>
                    </div>
                    <div id="faq-ai-panel-refine" class="d-none">
                        <label class="form-label">{{ __('How should the content change?') }}</label>
                        <textarea class="form-control" id="faq_ai_instruction" rows="4"></textarea>
                    </div>
                    <div id="faq-ai-error" class="alert alert-danger mt-3 d-none" role="alert"></div>
                    <div id="faq-ai-loading" class="d-none text-center py-3">
                        <span class="spinner-border spinner-border-sm text-success"></span>
                        <span class="ms-2">{{ __('Working…') }}</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-success" id="faq_ai_run_btn">{{ __('Apply to form') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <x-media-upload.js/>
    <x-datatable.js/>
    <script>
        $(document).ready(function($){
            "use strict";
            var faqAiMode = 'generate';
            var faqTargetForm = 'create';
            var faqModalEl = document.getElementById('faqAiModal');
            var faqModal = faqModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(faqModalEl) : null;

            function faqShowError(msg){
                $('#faq-ai-error').removeClass('d-none').text(msg || @json(__('Request failed.')));
            }
            function faqHideError(){
                $('#faq-ai-error').addClass('d-none').text('');
            }
            function faqGetForm(){
                if (faqTargetForm === 'edit') {
                    return $('#testimonial_edit_modal_form');
                }
                return $('#new_testimonial form');
            }

            <x-bulk-action-js :url="route('tenant.admin.faq.bulk.action')" />
            $(document).on('change','select[name="lang"]',function (e){
                $(this).closest('form').trigger('submit');
                $('input[name="lang"]').val($(this).val());
            });

            $(document).on('click', '.testimonial_edit_btn', function () {
                var el = $(this);
                var id = el.data('id');
                var name = el.data('title');
                var desc = el.data('description');
                var action = el.data('action');


                var form = $('#testimonial_edit_modal_form');
                form.attr('action', action);
                form.find('.faq_id').val(id);
                form.find('.edit_title').val(name);
                form.find('.edit_description').val(desc);
                form.find('.edit_status option[value="' + el.data('status') + '"]').attr('selected', true);
                form.find('.edit_cat option[value="' + el.data('category_id') + '"]').attr('selected', true);
            });

            $(document).on('click', '.faq-ai-open-modal', function () {
                faqAiMode = $(this).data('faq-ai-mode') || 'generate';
                faqTargetForm = $(this).data('target-form') || 'create';
                faqHideError();
                if (faqAiMode === 'generate') {
                    $('#faq-ai-panel-generate').removeClass('d-none');
                    $('#faq-ai-panel-refine').addClass('d-none');
                    $('#faqAiModalTitle').text(@json(__('Generate FAQ with AI')));
                } else {
                    $('#faq-ai-panel-generate').addClass('d-none');
                    $('#faq-ai-panel-refine').removeClass('d-none');
                    $('#faqAiModalTitle').text(@json(__('Improve FAQ with AI')));
                }
                if (faqModal) faqModal.show();
            });

            $('#faq_ai_run_btn').on('click', function () {
                faqHideError();
                var form = faqGetForm();
                var payload = {
                    mode: faqAiMode,
                    lang: $('#faqAiModal').data('ai-lang') || 'en'
                };
                if (faqAiMode === 'generate') {
                    payload.topic = $('#faq_ai_topic').val() || '';
                } else {
                    payload.instruction = $('#faq_ai_instruction').val() || '';
                    payload.current_title = faqTargetForm === 'edit' ? form.find('.edit_title').val() : form.find('input[name="title"]').val();
                    payload.current_description = faqTargetForm === 'edit' ? form.find('.edit_description').val() : form.find('textarea[name="description"]').val();
                }

                $('#faq-ai-loading').removeClass('d-none');
                $('#faq_ai_run_btn').prop('disabled', true);

                $.ajax({
                    url: $('#faqAiModal').data('ai-url'),
                    method: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json; charset=UTF-8',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (res) {
                    if (!res.success) {
                        faqShowError(res.message);
                        return;
                    }
                    if (faqTargetForm === 'edit') {
                        form.find('.edit_title').val(res.title || '');
                        form.find('.edit_description').val(res.description || '');
                        if (res.category_id) form.find('.edit_cat').val(String(res.category_id));
                    } else {
                        form.find('input[name="title"]').val(res.title || '');
                        form.find('textarea[name="description"]').val(res.description || '');
                        if (res.category_id) form.find('select[name="category_id"]').val(String(res.category_id));
                    }
                    if (typeof toastr !== 'undefined') toastr.success(@json(__('AI content applied. Please review before saving.')));
                    if (faqModal) faqModal.hide();
                }).fail(function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : @json(__('Request failed.'));
                    faqShowError(msg);
                }).always(function () {
                    $('#faq-ai-loading').addClass('d-none');
                    $('#faq_ai_run_btn').prop('disabled', false);
                });
            });

        });
    </script>
@endsection
