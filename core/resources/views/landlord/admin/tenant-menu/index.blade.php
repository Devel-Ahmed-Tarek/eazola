@extends(route_prefix().'admin.admin-master')
@section('title') {{__('Sidebar Menu Settings')}} - {{ optional($tenant->domain)->domain ?? $tenant->id }} @endsection

@section('style')
<style>
    .menu-settings-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 15px;
        overflow: hidden;
    }
    
    .menu-parent-header {
        background: linear-gradient(135deg, #2ECC71, #27AE60);
        color: white;
        padding: 12px 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .menu-parent-header:hover {
        background: linear-gradient(135deg, #27AE60, #1ABC9C);
    }
    
    .menu-parent-header.collapsed {
        background: #f8f9fa;
        color: #2C3E50;
    }
    
    .menu-parent-header .menu-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .menu-parent-header .menu-info i {
        font-size: 20px;
    }
    
    .menu-parent-header .menu-label {
        font-weight: 600;
        font-size: 15px;
    }
    
    .menu-parent-header .feature-badge {
        background: rgba(255,255,255,0.2);
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        margin-left: 10px;
    }
    
    .menu-parent-header.collapsed .feature-badge {
        background: #e9ecef;
        color: #7F8C8D;
    }
    
    .menu-children {
        background: #fff;
        padding: 15px;
        border-top: 1px solid #e9ecef;
    }
    
    .menu-child-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 15px;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
    }
    
    .menu-child-item:hover {
        background: #e9ecef;
    }
    
    .menu-child-item:last-child {
        margin-bottom: 0;
    }
    
    .menu-child-item .child-label {
        font-size: 14px;
        color: #2C3E50;
    }
    
    /* Custom Toggle Switch */
    .toggle-switch {
        position: relative;
        width: 50px;
        height: 26px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.3s;
        border-radius: 26px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .toggle-switch input:checked + .toggle-slider {
        background-color: #2ECC71;
    }
    
    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }
    
    .toggle-switch input:disabled + .toggle-slider {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Tenant Info Header */
    .tenant-info-header {
        background: linear-gradient(135deg, #2C3E50, #34495E);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .tenant-info-header h4 {
        margin: 0;
        font-size: 18px;
    }
    
    .tenant-info-header .domain-info {
        font-size: 14px;
        opacity: 0.8;
        margin-top: 5px;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .btn-reset {
        background: #E74C3C;
        color: white;
        border: none;
    }
    
    .btn-reset:hover {
        background: #C0392B;
        color: white;
    }
    
    .btn-save-all {
        background: #2ECC71;
        color: white;
        border: none;
    }
    
    .btn-save-all:hover {
        background: #27AE60;
        color: white;
    }
    
    /* Status indicator */
    .status-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    .status-indicator.visible {
        background: #2ECC71;
    }
    
    .status-indicator.hidden {
        background: #E74C3C;
    }
    
    /* Loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    .loading-overlay.active {
        display: flex;
    }
    
    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #2ECC71;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Collapse icon */
    .collapse-icon {
        transition: transform 0.3s ease;
    }
    
    .menu-parent-header.collapsed .collapse-icon {
        transform: rotate(-90deg);
    }
    
    /* Alert for changes */
    .unsaved-changes-alert {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #F1C40F;
        color: #2C3E50;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        display: none;
        z-index: 1000;
    }
    
    .unsaved-changes-alert.show {
        display: block;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>
@endsection

@section('content')
<div class="col-12 stretch-card">
    <div class="card">
        <div class="card-body">
            <!-- Tenant Info Header -->
            <div class="tenant-info-header">
                <h4><i class="mdi mdi-menu"></i> {{__('Sidebar Menu Settings')}}</h4>
                <div class="domain-info">
                    <strong>{{__('Tenant')}}:</strong> {{ optional($tenant->domain)->domain ?? $tenant->id }}
                    @if($tenant->user)
                        | <strong>{{__('Owner')}}:</strong> {{ $tenant->user->name }} ({{ $tenant->user->email }})
                    @endif
                </div>
            </div>

            <x-error-msg/>
            <x-flash-msg/>

            <!-- Info Alert -->
            <div class="alert alert-info mb-4">
                <i class="mdi mdi-information-outline"></i>
                {{__('Toggle OFF to hide menu items from this tenant\'s sidebar. Hidden items will not appear regardless of their subscription plan.')}}
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" class="btn btn-save-all" id="saveAllBtn">
                    <i class="mdi mdi-content-save"></i> {{__('Save All Changes')}}
                </button>
                <button type="button" class="btn btn-reset" id="resetBtn">
                    <i class="mdi mdi-refresh"></i> {{__('Reset to Default')}}
                </button>
                <a href="{{ route('landlord.admin.tenant') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> {{__('Back to Tenants')}}
                </a>
            </div>

            <!-- Menu Items -->
            <div class="menu-items-container" id="menuItemsContainer">
                @foreach($menuItems as $menuItem)
                    @php
                        $parentKey = $menuItem['key'];
                        $isParentVisible = $currentSettings[$parentKey] ?? true;
                        $hasChildren = !empty($menuItem['children']);
                    @endphp
                    
                    <div class="menu-settings-card" data-parent-key="{{ $parentKey }}">
                        <!-- Parent Menu Header -->
                        <div class="menu-parent-header {{ !$isParentVisible ? 'collapsed' : '' }}" 
                             data-bs-toggle="collapse" 
                             data-bs-target="#children-{{ Str::slug($parentKey) }}"
                             aria-expanded="{{ $isParentVisible ? 'true' : 'false' }}">
                            <div class="menu-info">
                                <i class="{{ $menuItem['icon'] ?? 'mdi mdi-menu' }}"></i>
                                <span class="menu-label">{{ $menuItem['label'] }}</span>
                                @if(isset($menuItem['feature']))
                                    <span class="feature-badge">{{ $menuItem['feature'] }}</span>
                                @endif
                                @if(isset($menuItem['plugin']))
                                    <span class="feature-badge">Plugin: {{ $menuItem['plugin'] }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <label class="toggle-switch mb-0" onclick="event.stopPropagation();">
                                    <input type="checkbox" 
                                           class="parent-toggle" 
                                           data-key="{{ $parentKey }}"
                                           data-label="{{ $menuItem['label'] }}"
                                           {{ $isParentVisible ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                @if($hasChildren)
                                    <i class="mdi mdi-chevron-down collapse-icon"></i>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Children Menu Items -->
                        @if($hasChildren)
                            <div class="collapse {{ $isParentVisible ? 'show' : '' }}" id="children-{{ Str::slug($parentKey) }}">
                                <div class="menu-children">
                                    @foreach($menuItem['children'] as $child)
                                        @php
                                            $childKey = $child['key'];
                                            $isChildVisible = $currentSettings[$childKey] ?? true;
                                        @endphp
                                        <div class="menu-child-item" data-parent="{{ $parentKey }}">
                                            <span class="child-label">
                                                <span class="status-indicator {{ $isChildVisible ? 'visible' : 'hidden' }}"></span>
                                                {{ $child['label'] }}
                                            </span>
                                            <label class="toggle-switch mb-0">
                                                <input type="checkbox" 
                                                       class="child-toggle" 
                                                       data-key="{{ $childKey }}"
                                                       data-label="{{ $child['label'] }}"
                                                       data-parent="{{ $parentKey }}"
                                                       {{ $isChildVisible ? 'checked' : '' }}
                                                       {{ !$isParentVisible ? 'disabled' : '' }}>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

<!-- Unsaved Changes Alert -->
<div class="unsaved-changes-alert" id="unsavedAlert">
    <i class="mdi mdi-alert"></i> {{__('You have unsaved changes')}}
</div>

@endsection

@section('scripts')
<script>
(function($) {
    "use strict";
    
    const tenantId = "{{ $tenant->id }}";
    const csrfToken = "{{ csrf_token() }}";
    let hasChanges = false;
    let changedItems = {};
    
    // Track changes
    function markChanged(key, isVisible, label, parentKey = null) {
        changedItems[key] = {
            menu_key: key,
            is_visible: isVisible,
            menu_label: label,
            parent_key: parentKey
        };
        hasChanges = true;
        $('#unsavedAlert').addClass('show');
    }
    
    // Parent toggle handler
    $(document).on('change', '.parent-toggle', function() {
        const $this = $(this);
        const key = $this.data('key');
        const label = $this.data('label');
        const isChecked = $this.is(':checked');
        const $card = $this.closest('.menu-settings-card');
        const $header = $card.find('.menu-parent-header');
        const $childToggles = $card.find('.child-toggle');
        
        // Update header style
        if (isChecked) {
            $header.removeClass('collapsed');
        } else {
            $header.addClass('collapsed');
        }
        
        // Enable/disable children
        $childToggles.prop('disabled', !isChecked);
        
        // If disabling parent, also disable all children
        if (!isChecked) {
            $childToggles.each(function() {
                const childKey = $(this).data('key');
                const childLabel = $(this).data('label');
                $(this).prop('checked', false);
                updateStatusIndicator($(this), false);
                markChanged(childKey, false, childLabel, key);
            });
        }
        
        markChanged(key, isChecked, label);
    });
    
    // Child toggle handler
    $(document).on('change', '.child-toggle', function() {
        const $this = $(this);
        const key = $this.data('key');
        const label = $this.data('label');
        const parentKey = $this.data('parent');
        const isChecked = $this.is(':checked');
        
        updateStatusIndicator($this, isChecked);
        
        // If enabling a child, make sure parent is enabled
        if (isChecked) {
            const $parentToggle = $this.closest('.menu-settings-card').find('.parent-toggle');
            if (!$parentToggle.is(':checked')) {
                $parentToggle.prop('checked', true).trigger('change');
            }
        }
        
        markChanged(key, isChecked, label, parentKey);
    });
    
    // Update status indicator
    function updateStatusIndicator($toggle, isVisible) {
        const $indicator = $toggle.closest('.menu-child-item').find('.status-indicator');
        $indicator.removeClass('visible hidden').addClass(isVisible ? 'visible' : 'hidden');
    }
    
    // Save all changes
    $('#saveAllBtn').on('click', function() {
        if (!hasChanges || Object.keys(changedItems).length === 0) {
            Swal.fire({
                icon: 'info',
                title: '{{ __("No Changes") }}',
                text: '{{ __("There are no changes to save.") }}'
            });
            return;
        }
        
        $('#loadingOverlay').addClass('active');
        
        // Prepare menu settings with proper boolean conversion
        const menuSettings = Object.values(changedItems).map(item => ({
            menu_key: item.menu_key,
            is_visible: item.is_visible ? 1 : 0,
            menu_label: item.menu_label || '',
            parent_key: item.parent_key || ''
        }));
        
        $.ajax({
            url: "{{ route('landlord.admin.tenant.menu.settings.update', $tenant->id) }}",
            method: 'POST',
            data: {
                _token: csrfToken,
                menu_settings: menuSettings
            },
            success: function(response) {
                $('#loadingOverlay').removeClass('active');
                if (response.success) {
                    hasChanges = false;
                    changedItems = {};
                    $('#unsavedAlert').removeClass('show');
                    
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __("Success") }}',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                $('#loadingOverlay').removeClass('active');
                console.log('Error:', xhr.responseJSON);
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("Error") }}',
                    text: xhr.responseJSON?.message || '{{ __("Failed to save changes. Please try again.") }}'
                });
            }
        });
    });
    
    // Reset to default
    $('#resetBtn').on('click', function() {
        Swal.fire({
            title: '{{ __("Reset to Default?") }}',
            text: '{{ __("This will restore all menu items to their default visibility. This action cannot be undone.") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E74C3C',
            cancelButtonColor: '#7F8C8D',
            confirmButtonText: '{{ __("Yes, Reset") }}',
            cancelButtonText: '{{ __("Cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingOverlay').addClass('active');
                
                $.ajax({
                    url: "{{ route('landlord.admin.tenant.menu.reset', $tenant->id) }}",
                    method: 'POST',
                    data: {
                        _token: csrfToken
                    },
                    success: function(response) {
                        $('#loadingOverlay').removeClass('active');
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '{{ __("Reset Complete") }}',
                                text: response.message
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#loadingOverlay').removeClass('active');
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("Error") }}',
                            text: '{{ __("Failed to reset. Please try again.") }}'
                        });
                    }
                });
            }
        });
    });
    
    // Warn before leaving with unsaved changes
    $(window).on('beforeunload', function() {
        if (hasChanges) {
            return '{{ __("You have unsaved changes. Are you sure you want to leave?") }}';
        }
    });
    
})(jQuery);
</script>
@endsection
