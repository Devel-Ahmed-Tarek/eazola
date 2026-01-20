@php
    $route_name ='landlord';
@endphp

@extends($route_name.'.admin.admin-master')

@section('title')
    {{__('User Details')}}
@endsection

@section('style')
    <style>
        .user_details ul li {
            list-style-type: none;
            margin-top: 15px;
        }
    </style>
    <x-datatable.css/>
    <x-summernote.css/>
@endsection

@section('content')

    <div class="col-12 stretch-card">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h4 class="d-flex justify-content-between">{{__('User Details :') . $user->name ?? ''}}
                    <a href="{{route('landlord.admin.tenant')}}"
                       class="btn btn-primary btn-sm my-2">{{__('Go Back')}}</a>
                </h4>
            </div>
            <div class="card-body user_details">

                <x-error-msg/>
                <x-flash-msg/>

                <div class="row">
                    <div class="col-lg-3">
                        <ul>
                            <li><strong>{{__('Name:')}}</strong> {{$user->name}}</li>
                            <li><strong>{{__('Email:')}}</strong> {{$user->email}}</li>
                            <li><strong>{{__('Username:')}}</strong> {{$user->username}}</li>
                            <li>
                                @php
                                    $markup = '';
                                    $li = '';
                                    $i = 0;
                                    foreach($user->tenant_details ?? [] as $tenant)
                                        {
                                            $li .= '<li class="mb-2">';
                                            $li .= '<span>'.++$i.'. </span>';
                                            $li .= '<a href="'.tenant_url_with_protocol(optional($tenant->domain)->domain).'">'.tenant_url_with_protocol(optional($tenant->domain)->domain).'</a>';
                                            $li .= '</li>';
                                        }
                                    $markup = '<ul>'.$li.'</ul>';
                                @endphp

                                <strong>{{__('Subdomains:')}}</strong>
                                <a href="#" data-bs-target="#all-site-domain" data-bs-toggle="modal" id="view-button"
                                   data-markup="{{$markup}}"><small>{{__('(Click to view all site)') }}</small></a>

                                <x-modal.markup :target="'all-site-domain'" :title="__('User Site List')"/>
                            </li>
                            <li><strong>{{__('Mobile:')}}</strong> {{$user->mobile}}</li>
                            <li><strong>{{__('Company:')}}</strong> {{$user->company}}</li>
                            <li><strong>{{__('Address:')}}</strong> {{$user->address}}</li>
                            <li><strong>{{__('State:')}}</strong> {{$user->state}}</li>
                            <li><strong>{{__('Country:')}}</strong> {{$user->country_name?->name}}</li>
                        </ul>
                    </div>
                    <div class="col-lg-9">
                        <h3 class="title my-3">{{__('Package Information')}}</h3>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <th>{{__('SL#')}}</th>
                                <th>{{__('Package Info')}}</th>
                                <th>{{__('Subscription Period')}}</th>
                                <th>{{__('Domain')}}</th>
                                <th>{{__('Payment History')}}</th>
                                <th>{{__('Action')}}</th>
                                </thead>

                                <tbody>
                                @foreach($user->tenant_details ?? [] as $key => $tenant)
                                    @php
                                        $colors = ['info','success','primary','dark','danger'];
                                        $tenantHelper = \App\Helpers\TenantHelper\TenantHelpers::init()->setTenantId($tenant->id);
                                    @endphp
                                    <tr>
                                        <td>{{ $key+1}}</td>
                                        <td>


                                            <span class="d-block mb-2"><span>{{__('Package Name:')}}</span>
                                                <span class="badge badge-{{$colors[$key % count($colors)]}}">
                                                    {{optional($tenant->payment_log)->package_name ?? __('Trial')}}
                                                </span>
                                            </span>

                                            <span class="d-block mb-2"><span>{{__('Package Type')}}</span> : {{ \App\Enums\PricePlanTypEnums::getText(optional(optional($tenant->payment_log)->package)->type ?? 0) }}</span>
                                            <span class="d-block mb-2"><span>{{__('Package Price')}}</span> : {{amount_with_currency_symbol(optional(optional($tenant->payment_log)->package)->price)}}</span>
                                            <span class="d-block mb-2"><span>{{__('Paid Amount')}}</span> : {{amount_with_currency_symbol(optional($tenant->payment_log)->package_price)}}</span>
                                            <span class="d-block mb-2"><span>{{__('Payment Gateway')}}</span> : {{str_replace('_', ' ',ucwords(optional($tenant->payment_log)->package_gateway))}}</span>
                                            <span class="d-block mb-2"><span>{{__('Order Status')}}</span> :

                                                @if(optional($tenant->payment_log)->status == 'trial')
                                                    <span class="text-danger">{{ __('Trial') }}</span>
                                                @elseif(optional($tenant->payment_log)->status == 'complete')
                                                    <span class="text-primary">{{ __('Active Subscription') }}</span>
                                                @elseif(optional($tenant->payment_log)->status == 'cancel')
                                                    <span class="text-danger">{{ __('Canceled Subscription') }}</span>
                                                @else
                                                    <span class="text-primary">{{ __('Pending') }}</span>
                                                @endif


                                            </span>
                                        </td>

                                        <td>
                                            <span class="d-block mb-2"><span>{{__('Start Date')}} :</span> {{ $tenantHelper->getTenantStartDate() }} </span>
                                            <span class="d-block mb-2"><span>{{__('Package Expire Date')}} :</span> {{$tenantHelper->getTenantExpiredDate() }} </span>

                                            <span class="d-block mb-2">

                                                @php
                                                    if ($tenant->expire_date != null)
                                                    {
                                                        $status = get_price_plan_expire_status($tenant->expire_date);
                                                    }
                                                    else
                                                    {
                                                        $log = $tenant?->payment_log?->status;
                                                        $status = $log == 'trial' ? 'trial' : 'active';
                                                    }


                                                    $class = ['active' => 'text-success', 'expired' => 'text-danger', 'pending' => 'text-warning', 'trial' => 'text-info'];
                                                @endphp

                                                <span class="{{$class[$status]}} text-capitalize">

                                                        <span>{{__('Status:')}}</span>
                                                        @if($status != 'active' && $status != 'expired')
                                                        @if($status == 'trial')
                                                            {{$status}}
                                                        @else
                                                            {{__('Pending')}}
                                                        @endif
                                                    @else
                                                        {{$status}}
                                                    @endif
                                                </span>
                                            </span>

                                        </td>
                                        <td>
                                            @if(!empty($tenant->domain))
                                                @php
                                                    $local_url = $tenant->id .'.'.env('CENTRAL_DOMAIN');
                                                    $url = tenant_url_with_protocol($local_url);
                                                    $hash_token = hash_hmac('sha512',$user->username.'_'.$tenant->id,$tenant->unique_key);
                                                @endphp
                                                <a href="{{tenant_url_with_protocol(optional($tenant->domain)->domain)}}"
                                                   target="_blank">{{tenant_url_with_protocol(optional($tenant->domain)->domain)}}</a>
                                                <br> <br>
                                                <a class="badge rounded-pill bg-info px-4 mt-2"
                                                   href="{{tenant_url_with_protocol(optional($tenant->domain)->domain).'/token-wise-login/'.$hash_token}}"
                                                   target="_blank"
                                                   style="text-decoration: none">{{__('Login as super admin')}}</a>
                                            @else
                                                <a href="{{tenant_url_with_protocol(optional($tenant->domain)->domain)}}"
                                                   target="_blank">{{tenant_url_with_protocol(optional($tenant->domain)->domain)}}</a>
                                                <br>
                                                <small
                                                    class="text-danger mt-2">{{__('Database and domain not generated yet')}}</small>

                                                <br><br>

                                                <form action="{{route('landlord.admin.failed.domain.generate')}}"
                                                      method="post">
                                                    <input type="hidden" name="id"
                                                           value="{{optional($tenant->issue)->id}}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-info btn-sm">{{__('Generate domain & database')}}</button>
                                                </form>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-info btn-sm"
                                               href="{{route('landlord.admin.package.order.manage.view',$tenant->id)}}"
                                               target="_blank">{{__('View Details')}}</a>
                                        </td>
                                        <td>
                                            <a class="btn btn-success btn-sm tenant_website_instruction"
                                               data-bs-toggle="modal"
                                               data-bs-target="#tenant_website_instruction"
                                               data-id="{{$tenant->id}}"
                                               data-instruction_status="{{$tenant->instruction_status}}"
                                            >{{__('Instruction')}}</a><br><br>
                                            <x-delete-popover permissions="domain-delete"
                                                              url="{{route(route_prefix().'admin.tenant.domain.delete', $tenant->id)}}"/>
                                            <br>

                                            <a href="#" class="btn btn-primary btn-sm" data-subdomain="{{$tenant->id}}"
                                               data-bs-toggle="modal" id="upgrade_plan"
                                               data-bs-target="#upgrade_website_modal">{{__('Upgrade Plan')}}</a>

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


    {{--    plan upgrade modal --}}
    <div class="modal fade" id="upgrade_website_modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Upgrade plan')}}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><span>×</span></button>
                </div>

                <form action="{{route('landlord.admin.tenant.assign.subscription')}}" id="user_add_subscription_form"
                      method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="subs_user_id" value="{{$user->id}}" id="subs_user_id">
                        <input type="hidden" name="subs_pack_id" id="subs_pack_id">
                        <input type="hidden" name="plan_upgrade" value="plan_upgrade" id="subs_pack_id">

                        <div class="form-group">
                            <label for="">{{__('Select A Package')}}</label>
                            <select class="form-control package_id_selector" name="package">
                                <option value="">{{__('Select Package')}}</option>
                                @foreach(\App\Models\PricePlan::all() as $price)
                                    <option value="{{$price->id}}" data-id="{{$price->id}}"
                                            data-type="{{$price->type}}">
                                        {{$price->getTranslation('title',default_lang())}} {{ '('.amount_with_currency_symbol($price->price).')' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subdomain">{{__('Subdomain')}}</label>
                            <input class="form-control subdomain form-control sub_domain_value_pass" id="subdomain"
                                   name="subdomain" readonly>

                        </div>

                        <div class="form-group theme_parent d-none">

                            @php
                                $themes = getAllThemeDataForAdmin();
                            @endphp
                            <label for="">{{__('Select Theme')}}</label>
                            <select class="form-control tenant_select_theme" name="theme">
                                <option selected disabled>{{__('Select a theme')}}</option>
                                @foreach(getAllThemeDataForAdmin() as $theme)
                                    @php
                                        $continueLoop  = false;
                                        if($theme->status === true)
                                        {
                                            $theme_details = getIndividualThemeDetails($theme->slug);
                                            $lang_suffix = '_'.default_lang();
                                            $theme_name = get_static_option_central($theme->slug.'_theme_name'.$lang_suffix) ?? $theme_details['name'];
                                            $is_available = get_static_option_central($theme->slug.'_theme_is_available');
                                            $formattedName1 = str_replace([' ', '-'], '', ucwords($theme_name, ' -'));

                                             $only_path = 'core/modules_statuses.json';

                                              if (!file_exists($only_path) && !is_dir($only_path))
                                              {
                                                  $module_status_path = file_get_contents($only_path);
                                                  $all_data_decoded = json_decode($module_status_path);

                                                   foreach ($all_data_decoded as $key => $item) {
                                                     if($formattedName1 == $key)
                                                     {
                                                         if($item == false)
                                                         {
                                                            $continueLoop = true;
                                                         }
                                                     }
                                                   }
                                              }
                                              if($is_available != 'on')
                                              {
                                               $continueLoop = true;
                                              }
                                        }
                                    @endphp

                                    @continue($continueLoop ?? false)

                                    <option value="{{$theme->slug}}">{{$theme_name}}</option>

                                @endforeach

                            </select>
                        </div>

                        <div class="form-group custom_expire_date_container d-none">
                            <label for="custom-subdomain">{{__('Custom Expire Date')}}</label>
                            <input class="form-control custom_expire_date" type="date" name="custom_expire_date"
                                   placeholder="{{__('Click to set custom expire date')}}">
                        </div>

                        <div class="form-group">
                            <label for="">{{__('Payment Status')}}</label>
                            <select class="form-control" name="payment_status">
                                <option value="complete">{{__('Complete')}}</option>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Close')}}</button>
                        <button type="submit"
                                class="btn btn-primary assign_subscription_submit_btn">{{__('Submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>





    <x-modal.markup :target="'account_manage_modal'" :title="__('Change Tenant Account Status')">
        <form action="{{route('landlord.admin.tenant.account.status')}}" method="POST">
            @csrf
            <input type="hidden" name="payment_log_id" value="">
            <div class="form-group">
                <label for="change_account_status">{{__('Change Account Status')}}</label>
                <select class="form-control" name="account_status" id="change_account_status">
                    <option value="pending">Pending</option>
                    <option value="complete">Complete</option>
                    <option value="cancel">Cancel</option>
                </select>
            </div>

            <div class="form-group">
                <label for="change_account_status">{{__('Change Payment Status')}}</label>
                <select class="form-control" name="payment_status" id="change_payment_status">
                    <option value="pending">{{__("Pending")}}</option>
                    <option value="complete">{{__("Complete")}}</option>
                    <option value="cancel">{{__("Cancel")}}</option>
                </select>
            </div>

            <div class="form-group float-end">
                <button class="btn btn-success" type="submit">{{__('Update')}}</button>
            </div>
        </form>
    </x-modal.markup>

    <x-modal.markup :target="'tenant_website_instruction'" :title="'Change Tenant Website Instruction'">
        <form action="{{route('landlord.admin.tenant.website.instruction.status')}}" method="POST"
              class="tenant_website_instruction_form" id="tenant_website_instruction_form">
            @csrf
            <input type="hidden" name="id" class="tenant_id">

            <x-fields.switcher name="instruction_status" extra="instruction_status"
                               label="{{__('Show/Hide tenant instruction')}}"/>

            <div class="form-group float-end">
                <button class="btn btn-primary" type="submit">{{__('Update')}}</button>
            </div>
        </form>
    </x-modal.markup>
@endsection

@section('scripts')
    <x-datatable.js/>
    <x-summernote.js/>

    <script>
        $(function () {
            $(document).on('click', '#view-button', function (e) {
                let list = $(this).data('markup');
                $('#all-site-domain').find('.modal-body').html('');
                $('#all-site-domain').find('.modal-body').append(list);
            });

            $(document).on('click', '.tenant_website_instruction', function (e) {
                let allData = $(this).data();
                let id = $(this).data('id');

                let form = $('#tenant_website_instruction_form');
                form.find('input[name="id"]').val(id);

                form.find('input[name="instruction_status"]').attr('checked', allData.instruction_status == 1 ? true : false);

            });

            $(document).on('click', '.account_manage_button', function (e) {
                let el = $(this);
                let id = el.data('id');
                let account = el.data('account');
                let payment = el.data('payment');

                let modal = $('#account_manage_modal').find('.modal-body');
                modal.find('input[name=payment_log_id]').val(id);

                modal.find('#change_account_status option[value=' + account + ']').prop('selected', true);
                modal.find('#change_payment_status option[value=' + payment + ']').prop('selected', true);
            });
        });


        // plan upgrade code start
        $(document).on('click', '#upgrade_plan', function (e) {
            e.preventDefault();

            let el = $(this);
            let modal = $('#upgrade_website_modal');
            modal.find('input[name="subdomain"]').val(el.data('subdomain'));
            // modal.find('select[name="package_id"] option[value="'+el.data('package_id')+'"]').attr('selected',true);
            modal.find('form').attr('action', el.data('action'));
        });

        $(document).on('change', '.package_id_selector', function () {
            let el = $(this);
            $('#subs_pack_id').val(el.val());
        });

        // end


    </script>
@endsection

