@extends(route_prefix().'admin.admin-master')
@section('title')
    {{__('Menu Manage')}}
@endsection
@section('style')
    <x-media-upload.css/>
    <x-datatable.css/>
    <x-bulk-action.css/>

    <style>
        .img-wrap img{
            width: 100%;
        }
    </style>
@endsection

@section('content')
    @php
        $lang_slug = request()->get('lang') ?? \App\Facades\GlobalLanguage::default_slug();
    @endphp
    <div class="col-lg-12 col-ml-12">
        <x-error-msg/>
        <x-flash-msg/>
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <x-error-msg/>
                        <x-flash-msg/>
                        <x-admin.header-wrapper>
                            <x-slot name="left">
                                <h4 class="card-title mb-5">{{__('All Menu')}}</h4>
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
                                <a class="btn btn-sm btn-info btn-xs mb-3 mr-1 text-light" href="{{route('tenant.admin.menu.manage.create')}}">{{__('Add New Menu')}}</a>
                            </x-slot>
                        </x-admin.header-wrapper>

                        <div class="table-wrap table-responsive">
                            <table class="table table-default">
                                <thead>
                                <x-bulk-action.th/>
                                <th>{{__('ID')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Image')}}</th>
                                <th>{{__('Regular Price')}}</th>
                                <th>{{__('Sale Price')}}</th>
                                <th>{{__('Sold Count')}}</th>
                                <th>{{__('Is Orderable')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Action')}}</th>
                                </thead>
                                <tbody>

                                @foreach($all_menus as $key => $menu)
                                    <tr>
                                        <x-bulk-action.td :id="$menu->id"/>
                                        <td>{{$menu->id}}</td>
                                        <td>{{$menu->getTranslation('name',$default_lang)}}</td>
                                        <td>
                                            <div class="attachment-preview">
                                                <div class="img-wrap">
                                                    {!! render_image_markup_by_attachment_id($menu->image_id) !!}
                                                </div>
                                            </div>
                                        </td>
                                        <td> {{ $menu->regular_price ?? '' }}</td>
                                        <td> {{ $menu->sale_price ?? '' }}</td>
                                        <td> {{ $menu->sold_count ?? '' }}</td>

                                        <td>
                                            <span class="badge badge-<?php echo $menu->is_orderable == 1 ? 'success' : 'danger' ?>"><?php echo $menu->is_orderable == 1 ? 'Yes' : 'No' ?></span>
                                        </td>

                                        <td> {{ \App\Enums\StatusEnums::getText($menu->status) }}</td>
                                        <td data-label="Actions">
                                            <a href="#"
                                               data-bs-toggle="modal"
                                               data-item-id="{{$menu->id}}" data-orderable-status="{{$menu->is_orderable}}"
                                               data-bs-target="#UpdatePaymentStatus"
                                               class="btn update-status btn-sm btn-info">{{__('Update Status')}}
                                            </a>
                                            <div class="action-icon">
                                                <a href="{{ route("tenant.admin.menu.manage.edit", $menu->id) }}"
                                                   class="icon edit"> <i class="las la-pen-alt"></i> Edit</a>
                                                    <x-table.btn.swal.delete :route="route('tenant.admin.menu.manage.destroy', $menu->id)"/>
                                            </div>
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
    @can('menu-category-edit')
        <div class="modal fade" id="category_edit_modal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('Update Category')}}</h5>
                        <button type="button" class="close" data-bs-dismiss="modal"><span>×</span></button>
                    </div>
                    <form action="" method="post">
                        <input type="hidden" name="id" id="category_id">
                        <input type="hidden" name="lang" value="{{$default_lang}}">
                        <div class="modal-body">
                            @csrf
                            <div class="form-group">
                                <label for="edit_name">{{__('Name')}}</label>
                                <input type="text" class="form-control" id="edit_name" name="name"
                                       placeholder="{{__('Name')}}">
                            </div>

                            <div class="form-group">
                                <label for="edit_description">{{__('Description')}}</label>
                                <textarea type="text" class="form-control" id="edit_description" rows="5" cols="10" name="description"
                                          placeholder="{{__('Description')}}"></textarea>
                            </div>

                            <x-fields.media-upload :title="__('Image')" :name="'image_id'" :dimentions="'120x120'"/>

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
    @can('menu-category-create')
        <div class="modal fade" id="category_create_modal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('Create Category')}}</h5>
                        <button type="button" class="close" data-bs-dismiss="modal"><span>×</span></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('tenant.admin.menu.category.new') }}" method="post"
                              enctype="multipart/form-data">
                            <input type="hidden" name="lang" value="{{$default_lang}}">
                            @csrf
                            <div class="form-group">
                                <label for="name">{{__('Name')}}</label>
                                <input type="text" class="form-control" id="create-name" name="name"
                                       placeholder="{{__('Name')}}">
                            </div>

                            <div class="form-group">
                                <label for="description">{{__('Description')}}</label>
                                <textarea type="text" class="form-control" id="description" name="description"
                                          placeholder="{{__('Description')}}" rows="5" cols="10"></textarea>
                            </div>

                            <x-fields.media-upload :title="__('Image')" :name="'image_id'" :dimentions="'120x120'"/>
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
    <div class="modal fade" id="UpdatePaymentStatus" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Update Status')}}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tenant.admin.menu.manage.orderable_status_updated') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="menu_id" id="menu_id" value="" required>
                            <label> {{__('Select Orderable Status')}}</label>
                            <select class="form-control form-control-sm" id="orderable_status" name="orderable_status">
                                <option data-payment-status="0" value="1"> {{__('Yes')}}</option>
                                <option data-payment-status="1" value="0"> {{__('No')}}</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"> {{__('Close')}}</button>
                            <button type="submit" class="btn btn-info btn-sm">{{__('Save changes')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="body-overlay-desktop"></div>
    <x-media-upload.markup/>
@endsection

@section('scripts')
    <x-datatable.js/>
    <x-media-upload.js/>
    <x-table.btn.swal.js/>
        <x-bulk-action.js :route="route('tenant.admin.menu.manage.bulk.destroy')"/>
    <script>

        (function($) {
            "use strict";

            $(document).ready(function ()
            {
                //lang form submit
                $(document).on('change','select[name="lang"]',function (e)
                {
                    $(this).closest('form').trigger('submit');
                    $('input[name="lang"]').val($(this).val());
                });
                // end

                // booking status update
                $(document).on("click", ".update-status", function ()
                {
                    let data = $(this);
                    let id = data.data("item-id")
                    let status = data.data("orderable-status")

                    $("#menu_id").val(id);

                    $("#orderable_status option").each(function ()
                    {
                        if (status == $(this).val()) {
                            $(this).attr("selected", true);
                        }
                    });
                });
            });

        })(jQuery);

    </script>
@endsection
