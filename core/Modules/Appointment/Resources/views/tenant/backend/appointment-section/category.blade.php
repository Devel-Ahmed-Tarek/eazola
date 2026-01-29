@extends('tenant.admin.admin-master')
@section('title')
    {{__('All Appointment Category')}}
@endsection

@section('style')
    <x-media-upload.css/>
    <x-datatable.css/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .category-icon { font-size: 20px; margin-right: 8px; }
        .category-color { width: 25px; height: 25px; border-radius: 4px; display: inline-block; border: 1px solid #ddd; }
        .category-image { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; }
        .badge-featured { background: linear-gradient(135deg, #2ECC71, #27AE60); color: white; }
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
                        <h4 class="card-title mb-5">{{__('All Appointment Category')}}</h4>
                    <x-bulk-action permissions="appointment-category-delete"/>
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

                        @can('appointment-category-create')
                           <button class="btn btn-info btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#new_category_modal">{{__('Add New Category')}}</button>
                        @endcan

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
                        <th>{{__('Color')}}</th>
                        <th>{{__('Order')}}</th>
                        <th>{{__('Featured')}}</th>
                        <th>{{__('Status')}}</th>
                        <th>{{__('Action')}}</th>
                    </x-slot>
                    <x-slot name="tr">
                        @foreach($all_categories as $data)
                            <tr>
                                <td>
                                    <x-bulk-delete-checkbox :id="$data->id"/>
                                </td>
                                <td>{{$data->id}}</td>
                                <td>
                                    @if($data->image)
                                        {!! render_image_markup_by_attachment_id($data->image, 'category-image') !!}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($data->icon)
                                        <i class="{{$data->icon}} category-icon" style="color: {{$data->color ?? '#333'}}"></i>
                                    @endif
                                    {{ $data->getTranslation('title',$lang_slug)}}
                                </td>
                                <td>
                                    @if($data->icon)
                                        <code>{{$data->icon}}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($data->color)
                                        <span class="category-color" style="background-color: {{$data->color}}" title="{{$data->color}}"></span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{$data->sort_order ?? 0}}</td>
                                <td>
                                    @if($data->is_featured)
                                        <span class="badge badge-featured">{{__('Featured')}}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ \App\Enums\StatusEnums::getText($data->status) }}</td>
                                <td>
                                @can('appointment-category-edit')
                                    <a href="#"
                                       data-bs-toggle="modal"
                                       data-bs-target="#edit_category_modal"
                                       class="btn btn-primary btn-xs mb-3 mr-1 category_edit_btn"
                                       data-bs-placement="top"
                                       title="{{__('Edit')}}"
                                       data-id="{{$data->id}}"
                                       data-action="{{route('tenant.admin.appointment.category.update')}}"
                                       data-title="{{$data->getTranslation('title',$default_lang)}}"
                                       data-description="{{$data->getTranslation('description',$default_lang) ?? ''}}"
                                       data-slug="{{$data->slug ?? ''}}"
                                       data-image="{{$data->image ?? ''}}"
                                       data-icon="{{$data->icon ?? ''}}"
                                       data-color="{{$data->color ?? '#2ECC71'}}"
                                       data-sort_order="{{$data->sort_order ?? 0}}"
                                       data-is_featured="{{$data->is_featured ? 1 : 0}}"
                                       data-status="{{$data->status}}"
                                    >
                                        <i class="las la-edit"></i>
                                    </a>
                                    @endcan
                                    <x-delete-popover permissions="appointment-category-delete" url="{{route('tenant.admin.appointment.category.delete', $data->id)}}"/>
                                </td>
                            </tr>
                        @endforeach
                    </x-slot>
                </x-datatable.table>

            </div>
        </div>
    </div>

    @can('appointment-category-create')
        <div class="modal fade" id="new_category_modal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('New Category')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('tenant.admin.appointment.category')}}" method="post" enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="lang" value="{{$default_lang}}">
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <x-fields.input name="title" label="{{__('Title')}}" />
                                </div>
                                <div class="col-md-4">
                                    <x-fields.input name="slug" label="{{__('Slug')}}" info="{{__('Leave empty to auto-generate')}}" />
                                </div>
                            </div>
                            
                            <x-fields.textarea name="description" label="{{__('Description')}}" />
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <x-fields.input name="icon" label="{{__('Icon Class')}}" placeholder="fa-solid fa-hospital" info="{{__('Font Awesome icon class')}}" />
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('Color')}}</label>
                                        <input type="color" name="color" class="form-control" value="#2ECC71" style="height: 45px;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <x-fields.input type="number" name="sort_order" label="{{__('Sort Order')}}" value="0" />
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <x-fields.media-upload name="image" title="{{__('Category Image')}}" dimentions="{{__('Recommended: 400x300')}}" />
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-fields.select name="is_featured" title="{{__('Featured')}}">
                                                <option value="0">{{__('No')}}</option>
                                                <option value="1">{{__('Yes')}}</option>
                                            </x-fields.select>
                                        </div>
                                        <div class="col-md-6">
                                            <x-fields.select name="status" title="{{__('Status')}}">
                                                <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                                <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                                            </x-fields.select>
                                        </div>
                                    </div>
                                </div>
                            </div>

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

    @can('appointment-category-edit')
        <div class="modal fade" id="edit_category_modal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('Edit Category')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="#" id="edit_category_form" method="post" enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="lang" value="{{$default_lang}}">
                            <input type="hidden" name="id" class="edit_category_id" value="">
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <x-fields.input name="title" label="{{__('Title')}}" class="edit_title" />
                                </div>
                                <div class="col-md-4">
                                    <x-fields.input name="slug" label="{{__('Slug')}}" class="edit_slug" />
                                </div>
                            </div>
                            
                            <x-fields.textarea name="description" label="{{__('Description')}}" class="edit_description" />
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <x-fields.input name="icon" label="{{__('Icon Class')}}" class="edit_icon" placeholder="fa-solid fa-hospital" />
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('Color')}}</label>
                                        <input type="color" name="color" class="form-control edit_color" style="height: 45px;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <x-fields.input type="number" name="sort_order" label="{{__('Sort Order')}}" class="edit_sort_order" />
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <x-fields.media-upload name="image" title="{{__('Category Image')}}" dimentions="{{__('Recommended: 400x300')}}" />
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-fields.select name="is_featured" title="{{__('Featured')}}" class="edit_is_featured">
                                                <option value="0">{{__('No')}}</option>
                                                <option value="1">{{__('Yes')}}</option>
                                            </x-fields.select>
                                        </div>
                                        <div class="col-md-6">
                                            <x-fields.select name="status" title="{{__('Status')}}" class="edit_status">
                                                <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                                <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                                            </x-fields.select>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
    <x-media-upload.js/>
    <x-datatable.js/>
    <script>
        $(document).ready(function($){
            "use strict";

            <x-bulk-action-js :url="route( 'tenant.admin.appointment.category.bulk.action')"/>
            
            $(document).on('change','select[name="lang"]',function (e){
                $(this).closest('form').trigger('submit');
                $('input[name="lang"]').val($(this).val());
            });

            $(document).on('click', '.category_edit_btn', function () {
                var el = $(this);
                var form = $('#edit_category_form');
                
                form.attr('action', el.data('action'));
                form.find('.edit_category_id').val(el.data('id'));
                form.find('.edit_title').val(el.data('title'));
                form.find('.edit_description').val(el.data('description'));
                form.find('.edit_slug').val(el.data('slug'));
                form.find('.edit_icon').val(el.data('icon'));
                form.find('.edit_color').val(el.data('color'));
                form.find('.edit_sort_order').val(el.data('sort_order'));
                form.find('.edit_is_featured').val(el.data('is_featured'));
                form.find('.edit_status').val(el.data('status'));
                
                // Handle image preview if exists
                if(el.data('image')) {
                    // You may need to implement image preview logic here
                }
            });

        });
    </script>
@endsection
