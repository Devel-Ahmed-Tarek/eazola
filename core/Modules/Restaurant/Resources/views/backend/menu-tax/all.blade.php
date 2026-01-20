@extends(route_prefix().'admin.admin-master')
@section('title')
    {{__('Menu Tax')}}
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
                                <h4 class="card-title mb-5">{{__('All Menu Taxes')}}</h4>
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
                                   class="btn btn-sm btn-info btn-xs mb-3 mr-1 text-light">{{__('New Menu Tax')}}</a>
                            </x-slot>
                        </x-admin.header-wrapper>

                        <div class="table-wrap table-responsive">
                            <table class="table table-default">
                                <thead>
                                <x-bulk-action.th/>
                                <th>{{__('ID')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Tax')}}</th>
                                <th>{{__('Description')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Action')}}</th>
                                </thead>
                                <tbody>

                                @foreach($all_menu_tax as $key => $item)
                                    <tr>
                                        <x-bulk-action.td :id="$item->id"/>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$item->getTranslation('name',$default_lang)}}</td>
                                        <td>{{$item->tax}}%</td>
                                        <td>{{$item->getTranslation('description',$default_lang)}}</td>
                                        <td> {{ \App\Enums\StatusEnums::getText($item->status) }}</td>
                                        <td>
                                            <x-table.btn.swal.delete :route="route('tenant.admin.menu.tax.delete', $item->id)"/>
                                            @can('restaurant-menu-tax-edit')
                                                @php
                                                    $image = get_attachment_image_by_id($item->image_id, null, true);
                                                    $img_path = $image['img_url'];
                                                @endphp

                                                <a href="#"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#category_edit_modal"
                                                   class="btn btn-sm btn-primary btn-xs mb-3 mr-1 category_edit_btn"
                                                   data-id="{{$item->id}}"
                                                   data-name="{{$item->getTranslation('name',$default_lang)}}"
                                                   data-tax="{{$item->tax}}"
                                                   data-status="{{$item->status}}"
                                                   data-slug="{{$item->slug}}"
                                                   data-description="{{$item->getTranslation('description',$default_lang)}}"
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
    @can('restaurant-menu-tax-edit')
        <div class="modal fade" id="category_edit_modal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('Update Menu Tax')}}</h5>
                        <button type="button" class="close" data-bs-dismiss="modal"><span>×</span></button>
                    </div>
                    <form action="{{ route('tenant.admin.menu.tax.update') }}" method="post">
                        @csrf
                        <input type="hidden" name="id" id="menu_tax_id">
                        <input type="hidden" name="lang" value="{{$default_lang}}">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="edit_name">{{__('Name')}}</label>
                                <input type="text" class="form-control" id="edit_name" name="name"
                                       placeholder="{{__('Name')}}">
                            </div>

                            <div class="form-group">
                                <label for="edit_name">{{__('Tax')}}</label>
                                <input type="number" class="form-control" id="tax" name="tax"
                                       placeholder="{{__('Tax')}}">
                            </div>

                            <div class="form-group">
                                <label for="edit_description">{{__('Description')}}</label>
                                <textarea type="text" class="form-control" id="edit_description" rows="5" cols="10" name="description"
                                          placeholder="{{__('Description')}}"></textarea>
                            </div>

                            <div class="form-group edit-status-wrapper">
                                <x-fields.select name="status" title="{{__('Status')}}">
                                    <option value="{{\App\Enums\StatusEnums::PUBLISH}}">{{__('Publish')}}</option>
                                    <option value="{{\App\Enums\StatusEnums::DRAFT}}">{{__('Draft')}}</option>
                                </x-fields.select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn btn-secondary"
                                    data-bs-dismiss="modal">{{__('Close')}}</button>
                            <button type="submit" class="btn btn-primary">{{__('Save Change')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
    @can('restaurant-menu-tax-create')
        <div class="modal fade" id="category_create_modal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('Create Menu Tax')}}</h5>
                        <button type="button" class="close" data-bs-dismiss="modal"><span>×</span></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('tenant.admin.menu.tax.new') }}" method="post"
                              enctype="multipart/form-data">
                            <input type="hidden" name="lang" value="{{$default_lang}}">
                            @csrf
                            <div class="form-group">
                                <label for="name">{{__('Name')}}</label>
                                <input type="text" class="form-control" id="create-name" name="name"
                                       placeholder="{{__('Name')}}">
                            </div>
                            <div class="form-group">
                                <label for="name">{{__('Tax')}}</label>
                                <input type="number" class="form-control" id="create-name" name="tax"
                                       placeholder="{{__('Tax')}}">
                            </div>

                            <div class="form-group">
                                <label for="description">{{__('Description')}}</label>
                                <textarea type="text" class="form-control" id="description" name="description"
                                          placeholder="{{__('Description')}}" rows="5" cols="10"></textarea>
                            </div>

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
        <x-bulk-action.js :route="route('tenant.admin.menu.tax.bulk.action')"/>
    <script>
        (function($)
        {
            "use strict";
            $(document).ready(function ()
            {
                $(document).on('change','select[name="lang"]',function (e){
                    $(this).closest('form').trigger('submit');
                    $('input[name="lang"]').val($(this).val());
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
