@extends('landlord.admin.admin-master')
@section('title')
    {{__('Pending Tenant Approvals')}}
@endsection
@section('style')
    <x-datatable.css/>
    <style>
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .stats-card {
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
        }
        .stats-card h3 { font-size: 32px; margin: 0; }
        .stats-card p { margin: 5px 0 0; opacity: 0.9; }
        .stats-pending { background: linear-gradient(135deg, #f0ad4e, #ec971f); }
        .stats-approved { background: linear-gradient(135deg, #5cb85c, #449d44); }
        .stats-rejected { background: linear-gradient(135deg, #d9534f, #c9302c); }
        .tenant-info { font-size: 13px; }
        .tenant-info strong { display: block; font-size: 14px; }
    </style>
@endsection
@section('content')
    <div class="col-lg-12 stretch-card">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card stats-pending">
                    <h3>{{$pending_count}}</h3>
                    <p><i class="las la-clock"></i> {{__('Pending')}}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card stats-approved">
                    <h3>{{$approved_count}}</h3>
                    <p><i class="las la-check-circle"></i> {{__('Approved')}}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card stats-rejected">
                    <h3>{{$rejected_count}}</h3>
                    <p><i class="las la-times-circle"></i> {{__('Rejected')}}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">{{__('Pending Tenant Approvals')}}</h4>
                    <a href="{{route('landlord.admin.tenant.approval.all')}}" class="btn btn-outline-primary btn-sm">
                        <i class="las la-list"></i> {{__('View All Tenants')}}
                    </a>
                </div>

                <x-error-msg/>
                <x-flash-msg/>

                @if($pending_tenants->count() > 0)
                <form id="bulk-action-form" action="" method="POST">
                    @csrf
                    <div class="bulk-actions mb-3">
                        <button type="button" class="btn btn-success btn-sm bulk-approve-btn" disabled>
                            <i class="las la-check"></i> {{__('Bulk Approve')}}
                        </button>
                        <button type="button" class="btn btn-danger btn-sm bulk-reject-btn" disabled>
                            <i class="las la-times"></i> {{__('Bulk Reject')}}
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="pending-tenants-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all-checkbox">
                                    </th>
                                    <th>{{__('Tenant ID')}}</th>
                                    <th>{{__('User Info')}}</th>
                                    <th>{{__('Package')}}</th>
                                    <th>{{__('Created')}}</th>
                                    <th>{{__('Actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pending_tenants as $tenant)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="tenant_ids[]" value="{{$tenant->id}}" class="tenant-checkbox">
                                    </td>
                                    <td>
                                        <strong>{{$tenant->id}}</strong>
                                        <br>
                                        <small class="text-muted">{{$tenant->id}}.{{env('CENTRAL_DOMAIN')}}</small>
                                    </td>
                                    <td class="tenant-info">
                                        <strong>{{optional($tenant->user)->name ?? 'N/A'}}</strong>
                                        <span>{{optional($tenant->user)->email ?? 'N/A'}}</span>
                                    </td>
                                    <td>
                                        @if($tenant->payment_log)
                                            <span class="badge badge-info">{{$tenant->payment_log->package_name}}</span>
                                            <br>
                                            <small>{{amount_with_currency_symbol($tenant->payment_log->package_price)}}</small>
                                        @else
                                            <span class="text-muted">{{__('N/A')}}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{$tenant->created_at->format('d M Y')}}
                                        <br>
                                        <small class="text-muted">{{$tenant->created_at->diffForHumans()}}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-success btn-sm approve-btn" 
                                                    data-tenant-id="{{$tenant->id}}"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#approveModal">
                                                <i class="las la-check"></i> {{__('Approve')}}
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm reject-btn"
                                                    data-tenant-id="{{$tenant->id}}"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#rejectModal">
                                                <i class="las la-times"></i> {{__('Reject')}}
                                            </button>
                                            <a href="{{route('landlord.admin.tenant.approval.show', $tenant->id)}}" 
                                               class="btn btn-info btn-sm">
                                                <i class="las la-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>

                <div class="mt-4">
                    {{$pending_tenants->links()}}
                </div>
                @else
                <div class="alert alert-success text-center">
                    <i class="las la-check-circle la-3x"></i>
                    <h5 class="mt-3">{{__('No Pending Approvals')}}</h5>
                    <p>{{__('All tenant registrations have been processed.')}}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="approve-form" method="POST" action="">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="las la-check-circle"></i> {{__('Approve Tenant')}}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{__('Are you sure you want to approve this tenant?')}}</p>
                        <p class="text-muted">{{__('The tenant will receive a notification email and their website will become active.')}}</p>
                        <div class="form-group">
                            <label>{{__('Note (Optional)')}}</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="{{__('Add any notes about this approval...')}}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                        <button type="submit" class="btn btn-success">{{__('Approve')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="reject-form" method="POST" action="">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="las la-times-circle"></i> {{__('Reject Tenant')}}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{__('Are you sure you want to reject this tenant?')}}</p>
                        <div class="form-group">
                            <label>{{__('Rejection Reason')}} <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="{{__('Please provide a reason for rejection...')}}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                        <button type="submit" class="btn btn-danger">{{__('Reject')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Reject Modal -->
    <div class="modal fade" id="bulkRejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="bulk-reject-form" method="POST" action="{{route('landlord.admin.tenant.approval.bulk.reject')}}">
                    @csrf
                    <input type="hidden" name="tenant_ids" id="bulk-reject-tenant-ids">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="las la-times-circle"></i> {{__('Bulk Reject Tenants')}}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{__('Are you sure you want to reject the selected tenants?')}}</p>
                        <div class="form-group">
                            <label>{{__('Rejection Reason')}} <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="{{__('Please provide a reason for rejection...')}}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                        <button type="submit" class="btn btn-danger">{{__('Reject Selected')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <x-datatable.js/>
    <script>
        $(document).ready(function(){
            // Set form action for approve modal
            $('.approve-btn').on('click', function(){
                var tenantId = $(this).data('tenant-id');
                $('#approve-form').attr('action', '{{route("landlord.admin.tenant.approval.approve", "")}}/'+tenantId);
            });

            // Set form action for reject modal
            $('.reject-btn').on('click', function(){
                var tenantId = $(this).data('tenant-id');
                $('#reject-form').attr('action', '{{route("landlord.admin.tenant.approval.reject", "")}}/'+tenantId);
            });

            // Select all checkbox
            $('#select-all-checkbox').on('change', function(){
                $('.tenant-checkbox').prop('checked', $(this).prop('checked'));
                updateBulkButtons();
            });

            // Individual checkbox change
            $('.tenant-checkbox').on('change', function(){
                updateBulkButtons();
            });

            function updateBulkButtons(){
                var checkedCount = $('.tenant-checkbox:checked').length;
                $('.bulk-approve-btn, .bulk-reject-btn').prop('disabled', checkedCount === 0);
            }

            // Bulk approve
            $('.bulk-approve-btn').on('click', function(){
                if(confirm('{{__("Are you sure you want to approve all selected tenants?")}}')){
                    var ids = [];
                    $('.tenant-checkbox:checked').each(function(){
                        ids.push($(this).val());
                    });
                    
                    var form = $('<form>', {
                        'method': 'POST',
                        'action': '{{route("landlord.admin.tenant.approval.bulk.approve")}}'
                    });
                    form.append($('<input>', {'type': 'hidden', 'name': '_token', 'value': '{{csrf_token()}}'}));
                    ids.forEach(function(id){
                        form.append($('<input>', {'type': 'hidden', 'name': 'tenant_ids[]', 'value': id}));
                    });
                    $('body').append(form);
                    form.submit();
                }
            });

            // Bulk reject
            $('.bulk-reject-btn').on('click', function(){
                var ids = [];
                $('.tenant-checkbox:checked').each(function(){
                    ids.push($(this).val());
                });
                $('#bulk-reject-tenant-ids').val(JSON.stringify(ids));
                $('#bulkRejectModal').modal('show');
            });

            // Fix bulk reject form submission
            $('#bulk-reject-form').on('submit', function(e){
                e.preventDefault();
                var ids = JSON.parse($('#bulk-reject-tenant-ids').val());
                var form = $(this);
                ids.forEach(function(id){
                    form.append($('<input>', {'type': 'hidden', 'name': 'tenant_ids[]', 'value': id}));
                });
                this.submit();
            });
        });
    </script>
@endsection
