@extends('landlord.admin.admin-master')
@section('title')
    {{__('Pages Demo Data')}}
@endsection

@section('style')
    <x-datatable.css/>
    <x-summernote.css/>
@endsection

@section('content')
    @php
        $lang_slug = request()->get('lang') ?? \App\Facades\GlobalLanguage::default_slug();
    @endphp
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-lg-12">
                <div class="margin-top-40"></div>
                <x-error-msg/>
                <x-flash-msg/>
            </div>
            <div class="col-lg-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <x-admin.header-wrapper>
                            <x-slot name="left">
                                <h4 class="header-title">{{__('Pages Demo Data')}}</h4>
                            </x-slot>
                            <x-slot name="right" class="d-flex">

                                <form action="" method="get">
                                    <x-fields.select name="lang" title="{{__('Language')}}">
                                        @foreach(\App\Models\Language::all() as $lang)
                                            @continue($lang->slug == 'en_GB')
                                            @php
                                                $slug = $lang->slug;
                                            @endphp
                                            <option value="{{$slug}}" @if($lang->slug === $lang_slug) selected @endif>{{$lang->name}}</option>
                                        @endforeach
                                    </x-fields.select>
                                </form>

                                <p></p>

                                <x-link-with-popover  url="{{route('landlord.admin.seeder.pages.index')}}" extraclass="ml-3">
                                    {{__('Go Back')}}
                                </x-link-with-popover>

                            </x-slot>
                        </x-admin.header-wrapper>
                        <div class="table-wrap table-responsive">
                            <table class="table table-default table-striped table-bordered">

                                <thead>
                                <th>{{__('SL#')}}</th>
                                <th>{{__('Title')}}</th>
                                <th>{{__('Default for themes')}}</th>
                                <th>{{__('Action')}}</th>
                                </thead>

                                <tbody>

                                @foreach($all_data_decoded->data ?? [] as $data)
                                    @php
                                        $title_decoded = (array) json_decode($data->title) ?? [];
                                        $default_themes = $data->default_for_themes ?? [];
                                        $default_themes = is_array($default_themes) ? $default_themes : (array) $default_themes;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{!! $title_decoded[$lang_slug] ?? '' !!}</td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary btn-choose-themes"
                                                    data-id="{{ $data->id }}"
                                                    data-default-themes="{{ json_encode($default_themes) }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#choose_default_themes_modal"
                                                    title="{{ __('Choose themes for which this page is default') }}">
                                                {{ __('Choose themes') }}
                                                @if(count($default_themes) > 0)
                                                    <span class="badge bg-primary ms-1">{{ count($default_themes) }}</span>
                                                @endif
                                            </button>
                                        </td>
                                        <td>
                                            <a href="#"
                                               data-id="{{$data->id}}"
                                               data-title="{{$title_decoded[$lang_slug] ?? ''}}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#donation_category_seeder_edit_modal"
                                               class="btn btn-lg btn-info btn-sm mb-3 mr-1 donation_category_seeder_edit_btn"
                                            >
                                                {{__("Edit Data")}}

                                            </a>
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



    <div class="modal fade" id="donation_category_seeder_edit_modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content ">
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Edit Demo Data')}}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><span>×</span></button>
                </div>

                <form action="" method="post" enctype="multipart/form-data" class="donation_category_seeder_edit_form">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id" class="donation_id">
                        <input type="hidden" name="lang" value="{{$default_lang}}">

                        <div class="form-group">
                            <label for="order_status">{{__('Title')}}</label>
                            <input type="text" name="title" class="form-control title">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{__('Close')}}</button>
                        <button type="submit" class="btn btn-primary btn-sm">{{__('Save Change')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- مودال: اختر السيمات التي تكون فيها هذه الصفحة ديفولت --}}
    <div class="modal fade" id="choose_default_themes_modal" tabindex="-1" aria-labelledby="choose_default_themes_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="choose_default_themes_label">{{ __('Choose themes') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">{{ __('Select the themes for which this page is a default page (imported when tenant switches to that theme with demo data).') }}</p>
                    <input type="hidden" id="default_themes_page_id" value="">
                    <div class="theme-checkboxes border rounded p-3" style="max-height: 280px; overflow-y: auto;">
                        @forelse($themes ?? [] as $theme)
                            <div class="form-check">
                                <input class="form-check-input theme-slug-cb" type="checkbox" name="theme_slugs[]" value="{{ $theme->slug }}" id="theme_cb_{{ $theme->id }}">
                                <label class="form-check-label" for="theme_cb_{{ $theme->id }}">{{ $theme->title ?? $theme->slug }}</label>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('No themes available.') }}</p>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="button" class="btn btn-primary" id="save_default_themes_btn">{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>

@endsection


@section('scripts')
    <x-datatable.js/>
    <x-summernote.js/>
    <script>
        $(document).ready(function($){
            "use strict";

            $(document).on('click','.donation_category_seeder_edit_btn',function(){
                let el = $(this);
                let form = $('.donation_category_seeder_edit_form');
                let id = el.data('id');
                let title = el.data('title');

                form.find('.donation_id').val(id);
                form.find('.title').val(title);

            });

            $(document).on('change','select[name="lang"]',function (e){
                $(this).closest('form').trigger('submit');
                $('input[name="lang"]').val($(this).val());
            });

            // اختر السيمات: عند فتح المودال نملأ الصفحة والـ checkboxes
            $('#choose_default_themes_modal').on('show.bs.modal', function (e) {
                var btn = $(e.relatedTarget);
                if (!btn.hasClass('btn-choose-themes')) return;
                var pageId = btn.data('id');
                var defaultThemes = btn.data('default-themes') || [];
                if (typeof defaultThemes === 'string') defaultThemes = JSON.parse(defaultThemes || '[]');
                $('#default_themes_page_id').val(pageId);
                $('.theme-slug-cb').prop('checked', false);
                defaultThemes.forEach(function(slug) {
                    $('.theme-slug-cb[value="' + slug + '"]').prop('checked', true);
                });
            });

            $('#save_default_themes_btn').on('click', function() {
                var pageId = $('#default_themes_page_id').val();
                var themeSlugs = [];
                $('.theme-slug-cb:checked').each(function() { themeSlugs.push($(this).val()); });
                var $btn = $(this);
                $btn.prop('disabled', true);
                $.ajax({
                    url: "{{ route('landlord.admin.seeder.pages.default.themes') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        page_id: pageId,
                        theme_slugs: themeSlugs
                    },
                    success: function(res) {
                        if (res.success) {
                            var $row = $('.btn-choose-themes[data-id="' + pageId + '"]').closest('tr');
                            var $badge = $row.find('.btn-choose-themes .badge');
                            if (themeSlugs.length > 0) {
                                if ($badge.length) $badge.text(themeSlugs.length);
                                else $row.find('.btn-choose-themes').append('<span class="badge bg-primary ms-1">' + themeSlugs.length + '</span>');
                            } else {
                                $badge.remove();
                            }
                            $row.find('.btn-choose-themes').data('default-themes', themeSlugs);
                            $('#choose_default_themes_modal').modal('hide');
                            toastr.success(res.message || '{{ __("Saved") }}');
                        }
                    },
                    error: function() {
                        toastr.error('{{ __("Error saving") }}');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection
