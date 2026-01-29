@extends('tenant.admin.admin-master')

@section('title')
    {{__('All Appointment Sub Category')}}
@endsection

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
                <x-admin.header-wrapper>
                    <x-slot name="left">
                        <h4 class="card-title mb-5">{{__('All Appointment Sub Category')}}</h4>
                    <x-bulk-action permissions="appointment-sub-category-delete"/>
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
                <x-error-msg/>
                <x-flash-msg/>
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
                        <th>{{__('Icon')}}</th>
                        <th>{{__('Category')}}</th>
                        <th>{{__('Sort Order')}}</th>
                        <th>{{__('Status')}}</th>
                        <th>{{__('Action')}}</th>
                    </x-slot>
                    <x-slot name="tr">
                        @foreach($all_subcategories as $data)
                            <tr>
                                <td>
                                    <x-bulk-delete-checkbox :id="$data->id"/>
                                </td>
                                <td>{{$data->id}}</td>
                                <td>
                                    @if($data->image)
                                        {!! render_attachment_preview_for_admin($data->image, 'max-width:50px;max-height:50px;') !!}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $data->getTranslation('title',$lang_slug)}}
                                    @if($data->slug)
                                        <br><small class="text-muted">{{ $data->slug }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($data->icon)
                                        <i class="{{ $data->icon }}" style="font-size: 20px;"></i>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $data->appointment_category?->getTranslation('title',$lang_slug) }}</span>
                                </td>
                                <td>{{ $data->sort_order ?? 0 }}</td>
                                <td>{{ \App\Enums\StatusEnums::getText($data->status) }}</td>
                                <td>
                                @can('appointment-sub-category-edit')
                                    <a href="#"
                                       data-bs-toggle="modal"
                                       data-bs-target="#testimonial_item_edit_modal"
                                       class="btn btn-primary btn-xs mb-3 mr-1 testimonial_edit_btn"
                                       data-bs-placement="top"
                                       title="{{__('Edit')}}"
                                       data-id="{{$data->id}}"
                                       data-action="{{route('tenant.admin.appointment.sub.category.update')}}"
                                       data-title="{{$data->getTranslation('title',$default_lang)}}"
                                       data-description="{{$data->getTranslation('description',$default_lang)}}"
                                       data-slug="{{$data->slug}}"
                                       data-image="{{$data->image}}"
                                       data-icon="{{$data->icon}}"
                                       data-sort_order="{{$data->sort_order}}"
                                       data-appointment_category_id="{{$data->appointment_category_id}}"
                                       data-status="{{$data->status}}"
                                    >
                                        <i class="las la-edit"></i>
                                    </a>
                                    @endcan
                                    <x-delete-popover permissions="appointment-sub-category-delete" url="{{route('tenant.admin.appointment.sub.category.delete', $data->id)}}"/>
                                </td>
                            </tr>
                        @endforeach
                    </x-slot>
                </x-datatable.table>

            </div>
        </div>
    </div>

    @can('appointment-sub-category-create')
        <div class="modal fade" id="new_testimonial" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">{{__('New Sub Category')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('tenant.admin.appointment.sub.category')}}" method="post" enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="lang" value="{{$default_lang}}">

                            <div class="row">
                                <div class="col-md-6">
                                    <x-fields.select name="appointment_category_id" title="{{__('Select Appointment Category')}}">
                                        @foreach($all_categories as $cat)
                                          <option value="{{ $cat->id }}">{{ $cat->getTranslation('title',$lang_slug) }}</option>
                                        @endforeach
                                    </x-fields.select>
                                </div>
                                <div class="col-md-6">
                                    <x-fields.select name="status" title="{{__('Status')}}">
                                        <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                        <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                                    </x-fields.select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <x-fields.input name="title" label="{{__('Title')}}" />
                                </div>
                                <div class="col-md-6">
                                    <x-fields.input name="slug" label="{{__('Slug')}}" info="{{__('Leave empty to auto-generate from title')}}" />
                                </div>
                            </div>

                            <x-fields.textarea name="description" label="{{__('Description')}}" info="{{__('Optional short description')}}" />

                            <div class="row">
                                <div class="col-md-6">
                                    <x-fields.input name="icon" label="{{__('Icon Class')}}" info="{{__('e.g., las la-spa, fas fa-cut')}}" />
                                </div>
                                <div class="col-md-6">
                                    <x-fields.input name="sort_order" type="number" label="{{__('Sort Order')}}" value="0" />
                                </div>
                            </div>

                            <x-fields.media-upload name="image" title="{{__('Image')}}" />

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

    @can('appointment-sub-category-edit')
        <div class="modal fade" id="testimonial_item_edit_modal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">{{__('Edit Sub Category Item')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="#" id="testimonial_edit_modal_form" method="post"
                          enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="lang" value="{{$default_lang}}">
                            <input type="hidden" name="id" class="edit_id" value="">

                            <div class="row">
                                <div class="col-md-6">
                                    <x-fields.select name="appointment_category_id" class="edit_appointment_category_id" title="{{__('Select Appointment Category')}}">
                                        @foreach($all_categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->getTranslation('title',$lang_slug) }}</option>
                                        @endforeach
                                    </x-fields.select>
                                </div>
                                <div class="col-md-6">
                                    <x-fields.select name="status" title="{{__('Status')}}" class="edit_status">
                                        <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                        <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                                    </x-fields.select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <x-fields.input name="title" label="{{__('Title')}}" class="edit_title" />
                                </div>
                                <div class="col-md-6">
                                    <x-fields.input name="slug" label="{{__('Slug')}}" class="edit_slug" info="{{__('Leave empty to auto-generate from title')}}" />
                                </div>
                            </div>

                            <x-fields.textarea name="description" label="{{__('Description')}}" class="edit_description" info="{{__('Optional short description')}}" />

                            <div class="row">
                                <div class="col-md-6">
                                    <x-fields.input name="icon" label="{{__('Icon Class')}}" class="edit_icon" info="{{__('e.g., las la-spa, fas fa-cut')}}" />
                                </div>
                                <div class="col-md-6">
                                    <x-fields.input name="sort_order" type="number" label="{{__('Sort Order')}}" class="edit_sort_order" value="0" />
                                </div>
                            </div>

                            <x-fields.media-upload name="image" title="{{__('Image')}}" id="edit_image" />

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
@endsection
@section('scripts')
    <x-datatable.js/>
    <script>
        $(document).ready(function($){
            "use strict";

            <x-bulk-action-js :url="route( 'tenant.admin.appointment.sub.category.bulk.action')"/>
            $(document).on('change','select[name="lang"]',function (e){
                $(this).closest('form').trigger('submit');
                $('input[name="lang"]').val($(this).val());
            });

            $(document).on('click', '.testimonial_edit_btn', function () {
                var el = $(this);
                var id = el.data('id');
                var action = el.data('action');

                var form = $('#testimonial_edit_modal_form');
                form.attr('action', action);
                form.find('.edit_id').val(id);
                form.find('.edit_title').val(el.data('title'));
                form.find('.edit_description').val(el.data('description') || '');
                form.find('.edit_slug').val(el.data('slug') || '');
                form.find('.edit_icon').val(el.data('icon') || '');
                form.find('.edit_sort_order').val(el.data('sort_order') || 0);
                
                // Reset and set select options
                form.find('.edit_status').val(el.data('status'));
                form.find('.edit_appointment_category_id').val(el.data('appointment_category_id'));

                // Handle image - update the media upload preview
                var imageId = el.data('image');
                if (imageId) {
                    form.find('input[name="image"]').val(imageId);
                    // Trigger media upload preview update if component supports it
                } else {
                    form.find('input[name="image"]').val('');
                }
            });
        });
    </script>
@endsection
