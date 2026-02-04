@extends('landlord.admin.admin-master')
@section('title')
    {{__('Tenant Details')}} - {{$tenant->id}}
@endsection
@section('style')
    <style>
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .detail-card {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .detail-card .card-header {
            border-radius: 10px 10px 0 0;
            font-weight: 600;
        }
        .info-row {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .domain-link {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
@endsection
@section('content')
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <a href="{{route('landlord.admin.tenant.pending.approval')}}" class="text-muted">
                    <i class="las la-arrow-left"></i>
                </a>
                {{__('Tenant Details')}}
            </h4>
            <div>
                @php
                    $statusClass = [
                        'pending' => 'status-pending',
                        'approved' => 'status-approved',
                        'rejected' => 'status-rejected',
                    ][$tenant->approval_status ?? 'approved'] ?? 'status-approved';
                    
                    $statusText = [
                        'pending' => __('Pending'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                    ][$tenant->approval_status ?? 'approved'] ?? __('Approved');
                @endphp
                <span class="status-badge {{$statusClass}}">{{$statusText}}</span>
            </div>
        </div>

        <x-error-msg/>
        <x-flash-msg/>

        <div class="row">
            <!-- Tenant Info -->
            <div class="col-md-6">
                <div class="card detail-card">
                    <div class="card-header bg-primary text-white">
                        <i class="las la-globe"></i> {{__('Tenant Information')}}
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <span class="info-label">{{__('Tenant ID')}}</span>
                            <div><strong>{{$tenant->id}}</strong></div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{__('Website URL')}}</span>
                            <div class="domain-link">
                                <a href="https://{{$tenant->id}}.{{env('CENTRAL_DOMAIN')}}" target="_blank">
                                    {{$tenant->id}}.{{env('CENTRAL_DOMAIN')}}
                                    <i class="las la-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                        @if($tenant->domain)
                        <div class="info-row">
                            <span class="info-label">{{__('Custom Domain')}}</span>
                            <div>{{$tenant->domain->custom_domain ?? '-'}}</div>
                        </div>
                        @endif
                        <div class="info-row">
                            <span class="info-label">{{__('Theme')}}</span>
                            <div>{{$tenant->theme_slug ?? '-'}}</div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{__('Created At')}}</span>
                            <div>{{$tenant->created_at->format('d M Y H:i')}}</div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{__('Start Date')}}</span>
                            <div>{{$tenant->start_date ?? '-'}}</div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{__('Expire Date')}}</span>
                            <div>{{$tenant->expire_date ?? '-'}}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div class="col-md-6">
                <div class="card detail-card">
                    <div class="card-header bg-info text-white">
                        <i class="las la-user"></i> {{__('User Information')}}
                    </div>
                    <div class="card-body">
                        @if($tenant->user)
                        <div class="info-row">
                            <span class="info-label">{{__('Name')}}</span>
                            <div><strong>{{$tenant->user->name}}</strong></div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{__('Email')}}</span>
                            <div>
                                <a href="mailto:{{$tenant->user->email}}">{{$tenant->user->email}}</a>
                            </div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{__('Phone')}}</span>
                            <div>{{$tenant->user->mobile ?? '-'}}</div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{__('Registered At')}}</span>
                            <div>{{$tenant->user->created_at->format('d M Y H:i')}}</div>
                        </div>
                        @else
                        <div class="alert alert-warning">{{__('User information not available')}}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Approval Info -->
            <div class="col-md-6">
                <div class="card detail-card">
                    <div class="card-header bg-warning text-dark">
                        <i class="las la-clipboard-check"></i> {{__('Approval Information')}}
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <span class="info-label">{{__('Status')}}</span>
                            <div><span class="status-badge {{$statusClass}}">{{$statusText}}</span></div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{__('Approved/Rejected At')}}</span>
                            <div>{{$tenant->approved_at ? $tenant->approved_at->format('d M Y H:i') : '-'}}</div>
                        </div>
                        @if($tenant->approval_note)
                        <div class="info-row">
                            <span class="info-label">{{__('Note')}}</span>
                            <div>{{$tenant->approval_note}}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="col-md-6">
                <div class="card detail-card">
                    <div class="card-header bg-success text-white">
                        <i class="las la-credit-card"></i> {{__('Payment Information')}}
                    </div>
                    <div class="card-body">
                        @if($tenant->payment_log)
                        <div class="info-row">
                            <span class="info-label">{{__('Package')}}</span>
                            <div><strong>{{$tenant->payment_log->package_name}}</strong></div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{__('Price')}}</span>
                            <div>{{amount_with_currency_symbol($tenant->payment_log->package_price)}}</div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{__('Gateway')}}</span>
                            <div>{{ucfirst($tenant->payment_log->package_gateway ?? '-')}}</div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{__('Payment Status')}}</span>
                            <div>
                                @if($tenant->payment_log->payment_status == 'complete')
                                <span class="badge bg-success">{{__('Complete')}}</span>
                                @else
                                <span class="badge bg-warning">{{$tenant->payment_log->payment_status}}</span>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="alert alert-info">{{__('No payment information available')}}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card detail-card">
            <div class="card-body">
                <h5 class="mb-3">{{__('Actions')}}</h5>
                <div class="d-flex gap-2 flex-wrap">
                    @if(($tenant->approval_status ?? 'approved') == 'pending')
                        <form action="{{route('landlord.admin.tenant.approval.approve', $tenant->id)}}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('{{__("Approve this tenant?")}}')" >
                                <i class="las la-check"></i> {{__('Approve Tenant')}}
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="las la-times"></i> {{__('Reject Tenant')}}
                        </button>
                    @else
                        <form action="{{route('landlord.admin.tenant.approval.reset', $tenant->id)}}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-secondary" onclick="return confirm('{{__("Reset status to pending?")}}')" >
                                <i class="las la-undo"></i> {{__('Reset to Pending')}}
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{route('landlord.admin.tenant.pending.approval')}}" class="btn btn-outline-secondary">
                        <i class="las la-arrow-left"></i> {{__('Back to List')}}
                    </a>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        @if($payment_logs->count() > 0)
        <div class="card detail-card">
            <div class="card-header">
                <i class="las la-history"></i> {{__('Payment History')}}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Package')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('Gateway')}}</th>
                                <th>{{__('Status')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payment_logs as $log)
                            <tr>
                                <td>{{$log->created_at->format('d M Y')}}</td>
                                <td>{{$log->package_name}}</td>
                                <td>{{amount_with_currency_symbol($log->package_price)}}</td>
                                <td>{{ucfirst($log->package_gateway ?? '-')}}</td>
                                <td>
                                    <span class="badge {{$log->payment_status == 'complete' ? 'bg-success' : 'bg-warning'}}">
                                        {{$log->payment_status}}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{route('landlord.admin.tenant.approval.reject', $tenant->id)}}">
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
@endsection
