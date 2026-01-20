@extends(route_prefix().'admin.admin-master')
@section('title')
    {{__('Menu Sub Category')}}
@endsection
@section('style')
    <x-media-upload.css/>
    <x-datatable.css/>
    <x-bulk-action.css/>

    <style>
        .img-wrap img {
            width: 100%;
        }
    </style>
@endsection

@php
    $statuses = \App\Models\Status::all();
    $lang_slug = request()->get('lang') ?? \App\Facades\GlobalLanguage::default_slug();
@endphp

@section('content')
    <div class="col-lg-12 col-ml-12">
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <x-error-msg/>
                        <x-flash-msg/>
                        <x-admin.header-wrapper>
                            <x-slot name="left">
                                <h4 class="card-title mb-5">{{__('All Sub Categories')}}</h4>
                                <x-bulk-action.dropdown/>
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
                                <a href="#"
                                   data-bs-toggle="modal"
                                   data-bs-target="#category_create_modal"
                                   class="btn btn-sm btn-info btn-xs mb-3 mr-1 text-light">{{__('New Sub Category')}}</a>
                                <a class="btn btn-sm btn-danger btn-xs mb-3 mr-1 text-light" href="{{route('tenant.admin.menu.subcategory.trash.all')}}">{{__('Trash')}}</a>
                            </x-slot>
                        </x-admin.header-wrapper>
                        <div class="table-wrap table-responsive">
                            <table class="table table-default">
                                <thead>
                                <x-bulk-action.th/>
                                <th>{{__('ID')}}</th>
                                <th>{{__('Sub Category Name')}}</th>
                                <th>{{__('Category Name')}}</th>
                                <th>{{__('Image')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Action')}}</th>
                                </thead>
                                <tbody>

                                @foreach($data['all_sub_category'] as $sub_category)
                                    <tr>
                                        <x-bulk-action.td :id="$sub_category->id"/>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$sub_category->getTranslation('name',$default_lang)}}</td>
                                        <td>{{$sub_category->menu_category?->getTranslation('name',$default_lang)}}</td>
                                        <td>
                                            <div class="attachment-preview">
                                                <div class="img-wrap">
                                                    {!! render_image_markup_by_attachment_id($sub_category->image_id) !!}
                                                </div>
                                            </div>
                                        </td>
                                        <td> {{ \App\Enums\StatusEnums::getText($sub_category->status) }}</td>
                                        <td>
                                            @can('restaurant-menu-sub-category-delete')
                                                <x-table.btn.swal.delete :route="route('tenant.admin.menu.subcategory.single.delete', $sub_category->id)"/>
                                            @endcan

                                            @can('restaurant-menu-sub-category-edit')
                                                @php
                                                    $image = get_attachment_image_by_id($sub_category->image_id, null, true);
                                                    $img_path = $image['img_url'];
                                                @endphp

                                                <a href="#"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#category_edit_modal"
                                                   class="btn btn-sm btn-primary btn-xs mb-3 mr-1 category_edit_btn"
                                                   data-id="{{$sub_category->id}}"
                                                   data-category="{{$sub_category->menu_category_id}}"
                                                   data-name="{{$sub_category->getTranslation('name',$default_lang)}}"
                                                   data-status="{{$sub_category->status}}"
                                                   data-slug="{{$sub_category->slug}}"
                                                   data-description="{{$sub_category->getTranslation('description',$default_lang)}}"
                                                   data-imageid="{{$sub_category->image_id}}"
                                                   data-image="{{$img_path}}"
                                                >
                                                    <i class="mdi mdi-lead-pencil"></i>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @can('restaurant-menu-sub-category-edit')
        <div class="modal fade" id="category_edit_modal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('Update Sub Category')}}</h5>
                        <button type="button" class="close" data-bs-dismiss="modal"><span>×</span></button>
                    </div>
                    <form action="{{ route('tenant.admin.menu.subcategory.single.update') }}" method="post">
                        <input type="hidden" name="id" id="category_id">
                        <input type="hidden" name="lang" value="{{$default_lang}}">
                        <div class="modal-body">
                            @csrf
                            <div class="form-group">
                                <label for="edit_name">{{__('Name')}}</label>
                                <input type="text" class="form-control" id="edit_name" name="name"
                                       placeholder="{{__('Name')}}">
                            </div>

                            <div class="form-group edit-category-wrapper">
                                <label for="name">{{__('Category')}}</label>
                                <select type="text" class="form-control" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    @foreach($data['all_category'] as $category)
                                        <option value="{{ $category->id }}">{{ $category->getTranslation('name',$default_lang) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="edit_description">{{__('Description')}}</label>
                                <textarea type="text" class="form-control" id="edit_description" name="description" placeholder="{{__('Description')}}"></textarea>
                            </div>

                            <x-fields.media-upload :title="__('Image')" :name="'image_id'" :dimentions="'200x200'"/>
                            <div class="form-group edit-status-wrapper">
                                <x-fields.select name="status" title="{{__('Status')}}">
                                    <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                    <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                                </x-fields.select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary"
                                    data-bs-dismiss="modal">{{__('Close')}}</button>
                            <button type="submit" class="btn btn-primary">{{__('Save Change')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
    @can('restaurant-menu-category-create')
        <div class="modal fade" id="category_create_modal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('Create Sub Category')}}</h5>
                        <button type="button" class="close" data-bs-dismiss="modal"><span>×</span></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('tenant.admin.menu.subcategory.new') }}" method="post"
                              enctype="multipart/form-data">
                            <input type="hidden" name="lang" value="{{$default_lang}}">
                            @csrf
                            <div class="form-group">
                                <label for="name">{{__('Name')}}</label>
                                <input type="text" class="form-control" id="create-name" name="name"
                                       placeholder="{{__('Name')}}">
                            </div>

                            <div class="form-group">
                                <label for="name">{{__('Category')}}</label>
                                <select type="text" class="form-control" id="create_category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    @foreach($data['all_category'] as $category)
                                        <option value="{{ $category->id }}">{{ $category->getTranslation('name',$default_lang) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="description">{{__('Description')}}</label>
                                <textarea type="text" class="form-control" id="description" name="description"
                                          placeholder="{{__('Description')}}"></textarea>
                            </div>

                            <x-fields.media-upload :title="__('Image')" :name="'image_id'" :dimentions="'200x200'"/>

                            <div class="form-group">
                                <x-fields.select name="status" title="{{__('Status')}}">
                                    <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                    <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                                </x-fields.select>
                            </div>
                            <button type="submit" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Add New')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
    <div class="body-overlay-desktop"></div>
    <x-media-upload.markup/>
@endsection
@section('scripts')
    <x-datatable.js/>
    <x-media-upload.js/>
    <x-table.btn.swal.js/>

        <x-bulk-action.js :route="route('tenant.admin.menu.subcategory.bulk.delete.all')"/>


    <script>

        (function($)
        {
            "use strict";

            $(document).ready(function () {

                $(document).on('change','select[name="lang"]',function (e){
                    $(this).closest('form').trigger('submit');
                    $('input[name="lang"]').val($(this).val());
                });

                $(document).on('click', '.category_edit_btn', function () {
                    let el = $(this);
                    let id = el.data('id');
                    let name = el.data('name');
                    let slug = el.data('slug');
                    let description = el.data('description');
                    let category = el.data("category");

                    let status = el.data('status');
                    let modal = $('#category_edit_modal');

                    modal.find('#category_id').val(id);
                    modal.find('#edit_status option[value="' + status + '"]').attr('selected', true);
                    modal.find('#edit_name').val(name);
                    modal.find('#edit_slug').val(slug);
                    modal.find('#edit_description').val(description);
                    modal.find(".edit-category-wrapper select option[value="+ category + "]").attr('selected', true);

                    let image = el.data('image');
                    let imageid = el.data('imageid');

                    if (imageid != '') {
                        modal.find('.media-upload-btn-wrapper .img-wrap').html('<div class="attachment-preview"><div class="thumbnail"><div class="centered"><img class="avatar user-thumb" src="' + image + '" > </div></div></div>');
                        modal.find('.media-upload-btn-wrapper input').val(imageid);
                        modal.find('.media-upload-btn-wrapper .media_upload_form_btn').text('Change Image');
                    }

                });

                $('#create-name , #create-slug').on('keyup', function () {
                    let title_text = $(this).val();
                    $('#create-slug').val(convertToSlug(title_text))
                });

                $('#edit_name , #edit_slug').on('keyup', function () {
                    let title_text = $(this).val();
                    $('#edit_slug').val(convertToSlug(title_text))
                });
            });

        })(jQuery);
    </script>
@endsection
