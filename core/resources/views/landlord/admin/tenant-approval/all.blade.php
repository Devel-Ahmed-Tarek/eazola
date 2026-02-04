@extends('landlord.admin.admin-master')
@section('title')
    {{__('All Tenants Approval Status')}}
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
        .filter-tabs {
            margin-bottom: 20px;
        }
        .filter-tabs .nav-link {
            border-radius: 20px;
            margin-right: 10px;
            padding: 8px 20px;
        }
        .filter-tabs .nav-link.active {
            font-weight: 600;
        }
        .stats-mini {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: 5px;
        }
    </style>
@endsection
@section('content')
    <div class="col-lg-12 stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">{{__('All Tenants Approval Status')}}</h4>
                    <a href="{{route('landlord.admin.tenant.pending.approval')}}" class="btn btn-warning btn-sm">
                        <i class="las la-clock"></i> {{__('Pending Approvals')}} 
                        @if($pending_count > 0)
                        <span class="badge bg-light text-warning">{{$pending_count}}</span>
                        @endif
                    </a>
                </div>

                <!-- Filter Tabs -->
                <ul class="nav nav-pills filter-tabs">
                    <li class="nav-item">
                        <a class="nav-link {{$status == 'all' ? 'active' : ''}}" href="{{route('landlord.admin.tenant.approval.all')}}">
                            {{__('All')}}
                            <span class="stats-mini bg-secondary text-white">{{$approved_count + $pending_count + $rejected_count}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$status == 'approved' ? 'active' : ''}} text-success" href="{{route('landlord.admin.tenant.approval.all', ['status' => 'approved'])}}">
                            {{__('Approved')}}
                            <span class="stats-mini bg-success text-white">{{$approved_count}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$status == 'pending' ? 'active' : ''}} text-warning" href="{{route('landlord.admin.tenant.approval.all', ['status' => 'pending'])}}">
                            {{__('Pending')}}
                            <span class="stats-mini bg-warning text-dark">{{$pending_count}}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$status == 'rejected' ? 'active' : ''}} text-danger" href="{{route('landlord.admin.tenant.approval.all', ['status' => 'rejected'])}}">
                            {{__('Rejected')}}
                            <span class="stats-mini bg-danger text-white">{{$rejected_count}}</span>
                        </a>
                    </li>
                </ul>

                <x-error-msg/>
                <x-flash-msg/>

                <div class="table-responsive">
                    <table class="table table-hover" id="all-tenants-table">
                        <thead class="thead-light">
                            <tr>
                                <th>{{__('Tenant ID')}}</th>
                                <th>{{__('User')}}</th>
                                <th>{{__('Package')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Approved/Rejected')}}</th>
                                <th>{{__('Actions')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tenants as $tenant)
                            <tr>
                                <td>
                                    <strong>{{$tenant->id}}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <a href="https://{{$tenant->id}}.{{env('CENTRAL_DOMAIN')}}" target="_blank">
                                            {{$tenant->id}}.{{env('CENTRAL_DOMAIN')}}
                                            <i class="las la-external-link-alt"></i>
                                        </a>
                                    </small>
                                </td>
                                <td>
                                    <strong>{{optional($tenant->user)->name ?? 'N/A'}}</strong>
                                    <br>
                                    <small>{{optional($tenant->user)->email ?? 'N/A'}}</small>
                                </td>
                                <td>
                                    @if($tenant->payment_log)
                                        {{$tenant->payment_log->package_name}}
                                    @else
                                        <span class="text-muted">{{__('N/A')}}</span>
                                    @endif
                                </td>
                                <td>
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
                                </td>
                                <td>
                                    @if($tenant->approved_at)
                                        {{$tenant->approved_at->format('d M Y H:i')}}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                    @if($tenant->approval_note)
                                        <br>
                                        <small class="text-muted" title="{{$tenant->approval_note}}">
                                            <i class="las la-comment"></i> {{Str::limit($tenant->approval_note, 30)}}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        @if(($tenant->approval_status ?? 'approved') == 'pending')
                                            <form action="{{route('landlord.admin.tenant.approval.approve', $tenant->id)}}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('{{__("Approve this tenant?")}}')"  title="{{__('Approve')}}">
                                                    <i class="las la-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if(($tenant->approval_status ?? 'approved') != 'pending')
                                            <form action="{{route('landlord.admin.tenant.approval.reset', $tenant->id)}}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('{{__("Reset to pending?")}}')" title="{{__('Reset to Pending')}}">
                                                    <i class="las la-undo"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <a href="{{route('landlord.admin.tenant.approval.show', $tenant->id)}}" class="btn btn-info btn-sm" title="{{__('View Details')}}">
                                            <i class="las la-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    {{__('No tenants found.')}}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{$tenants->appends(['status' => $status])->links()}}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <x-datatable.js/>
@endsection
