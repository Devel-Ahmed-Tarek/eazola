@extends(route_prefix().'admin.admin-master')
@section('title') {{__('User Stores')}} - {{ $user->name }} @endsection

@section('style')
<style>
    .user-info-card {
        background: linear-gradient(135deg, #2C3E50, #34495E);
        color: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 25px;
    }
    
    .user-info-card h3 {
        margin: 0 0 10px 0;
        font-size: 22px;
    }
    
    .user-info-card .user-email {
        opacity: 0.8;
        font-size: 14px;
    }
    
    .user-info-card .user-stats {
        display: flex;
        gap: 30px;
        margin-top: 15px;
    }
    
    .user-info-card .stat-item {
        text-align: center;
    }
    
    .user-info-card .stat-number {
        font-size: 28px;
        font-weight: bold;
        color: #2ECC71;
    }
    
    .user-info-card .stat-label {
        font-size: 12px;
        opacity: 0.7;
    }
    
    .stores-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
    }
    
    .store-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }
    
    .store-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .store-card-header {
        background: linear-gradient(135deg, #2ECC71, #27AE60);
        color: white;
        padding: 20px;
        position: relative;
    }
    
    .store-card-header .store-id {
        font-size: 12px;
        opacity: 0.8;
        margin-bottom: 5px;
    }
    
    .store-card-header .store-domain {
        font-size: 18px;
        font-weight: 600;
        word-break: break-all;
    }
    
    .store-card-header .store-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255,255,255,0.2);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
    }
    
    .store-card-body {
        padding: 20px;
    }
    
    .store-info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f1f1f1;
        font-size: 14px;
    }
    
    .store-info-row:last-child {
        border-bottom: none;
    }
    
    .store-info-row .label {
        color: #7F8C8D;
    }
    
    .store-info-row .value {
        color: #2C3E50;
        font-weight: 500;
    }
    
    .store-card-actions {
        padding: 15px 20px;
        background: #f8f9fa;
        display: flex;
        gap: 10px;
    }
    
    .store-card-actions .btn {
        flex: 1;
        text-align: center;
    }
    
    .btn-sidebar-settings {
        background: linear-gradient(135deg, #2ECC71, #27AE60);
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-sidebar-settings:hover {
        background: linear-gradient(135deg, #27AE60, #1ABC9C);
        color: white;
        transform: scale(1.02);
    }
    
    .btn-visit-store {
        background: #3498DB;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 6px;
        font-weight: 500;
    }
    
    .btn-visit-store:hover {
        background: #2980B9;
        color: white;
    }
    
    .empty-stores {
        text-align: center;
        padding: 60px 20px;
        color: #7F8C8D;
    }
    
    .empty-stores i {
        font-size: 60px;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .back-btn {
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="col-12 stretch-card">
    <div class="card">
        <div class="card-body">
            
            <!-- Back Button -->
            <div class="back-btn">
                <a href="{{ route('landlord.admin.tenant') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> {{__('Back to Users List')}}
                </a>
            </div>
            
            <!-- User Info Card -->
            <div class="user-info-card">
                <h3><i class="mdi mdi-account"></i> {{ $user->name }}</h3>
                <div class="user-email"><i class="mdi mdi-email"></i> {{ $user->email }}</div>
                <div class="user-stats">
                    <div class="stat-item">
                        <div class="stat-number">{{ $tenants->count() }}</div>
                        <div class="stat-label">{{__('Total Stores')}}</div>
                    </div>
                </div>
            </div>
            
            <x-error-msg/>
            <x-flash-msg/>
            
            <h4 class="mb-4"><i class="mdi mdi-store"></i> {{__('User Stores')}}</h4>
            
            @if($tenants->count() > 0)
                <div class="stores-grid">
                    @foreach($tenants as $tenant)
                        @php
                            $domain = optional($tenant->domain)->domain;
                            $fullUrl = $domain ? 'https://' . $domain : null;
                        @endphp
                        <div class="store-card">
                            <div class="store-card-header">
                                <div class="store-id">ID: {{ $tenant->id }}</div>
                                <div class="store-domain">{{ $domain ?? $tenant->id }}</div>
                                <span class="store-badge">
                                    <i class="mdi mdi-check-circle"></i> {{__('Active')}}
                                </span>
                            </div>
                            <div class="store-card-body">
                                <div class="store-info-row">
                                    <span class="label">{{__('Created')}}</span>
                                    <span class="value">{{ $tenant->created_at?->format('Y-m-d') ?? '-' }}</span>
                                </div>
                                <div class="store-info-row">
                                    <span class="label">{{__('Subdomain')}}</span>
                                    <span class="value">{{ $tenant->id }}.eazola.com</span>
                                </div>
                            </div>
                            <div class="store-card-actions">
                                <a href="{{ route('landlord.admin.tenant.menu.settings', $tenant->id) }}" 
                                   class="btn btn-sidebar-settings">
                                    <i class="mdi mdi-menu"></i> {{__('Sidebar Settings')}}
                                </a>
                                @if($fullUrl)
                                    <a href="{{ $fullUrl }}" target="_blank" class="btn btn-visit-store">
                                        <i class="mdi mdi-open-in-new"></i> {{__('Visit')}}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-stores">
                    <i class="mdi mdi-store-off"></i>
                    <h5>{{__('No Stores Found')}}</h5>
                    <p>{{__('This user does not have any stores yet.')}}</p>
                </div>
            @endif
            
        </div>
    </div>
</div>
@endsection
