<?php

namespace App\Http\Controllers\Landlord\Admin;

use App\Helpers\FlashMsg;
use App\Helpers\Payment\DatabaseUpdateAndMailSend\LandlordPricePlanAndTenantCreate;
use App\Http\Controllers\Controller;
use App\Models\PaymentLogs;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantApprovalController extends Controller
{
    private const BASE_PATH = 'landlord.admin.tenant-approval.';

    public function __construct()
    {
        $this->middleware('permission:tenant-approval-list|tenant-approval-approve|tenant-approval-reject', ['only' => ['index']]);
        $this->middleware('permission:tenant-approval-approve', ['only' => ['approve', 'bulkApprove']]);
        $this->middleware('permission:tenant-approval-reject', ['only' => ['reject', 'bulkReject']]);
    }

    /**
     * Display list of pending tenant approvals
     */
    public function index()
    {
        $pending_tenants = Tenant::where('approval_status', 'pending')
            ->with(['user', 'payment_log'])
            ->latest()
            ->paginate(20);

        $approved_count = Tenant::where('approval_status', 'approved')->count();
        $pending_count = Tenant::where('approval_status', 'pending')->count();
        $rejected_count = Tenant::where('approval_status', 'rejected')->count();

        return view(self::BASE_PATH . 'index', compact(
            'pending_tenants',
            'approved_count',
            'pending_count',
            'rejected_count'
        ));
    }

    /**
     * Display all tenants with their approval status
     */
    public function all(Request $request)
    {
        $status = $request->get('status', 'all');
        
        $query = Tenant::with(['user', 'payment_log']);
        
        if ($status !== 'all') {
            $query->where('approval_status', $status);
        }
        
        $tenants = $query->latest()->paginate(20);

        $approved_count = Tenant::where('approval_status', 'approved')->count();
        $pending_count = Tenant::where('approval_status', 'pending')->count();
        $rejected_count = Tenant::where('approval_status', 'rejected')->count();

        return view(self::BASE_PATH . 'all', compact(
            'tenants',
            'status',
            'approved_count',
            'pending_count',
            'rejected_count'
        ));
    }

    /**
     * Approve a tenant
     */
    public function approve(Request $request, $tenant_id)
    {
        $tenant = Tenant::findOrFail($tenant_id);

        if ($tenant->approval_status === 'approved') {
            return back()->with(FlashMsg::explain('warning', __('Tenant is already approved.')));
        }

        $tenant->approval_status = 'approved';
        $tenant->approved_at = now();
        $tenant->approved_by = Auth::id();
        $tenant->approval_note = $request->get('note');
        $tenant->save();

        // Send notification to tenant
        LandlordPricePlanAndTenantCreate::notifyTenantApproval($tenant, 'approved');

        return back()->with(FlashMsg::explain('success', __('Tenant approved successfully.')));
    }

    /**
     * Reject a tenant
     */
    public function reject(Request $request, $tenant_id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $tenant = Tenant::findOrFail($tenant_id);

        if ($tenant->approval_status === 'rejected') {
            return back()->with(FlashMsg::explain('warning', __('Tenant is already rejected.')));
        }

        $tenant->approval_status = 'rejected';
        $tenant->approval_note = $request->rejection_reason;
        $tenant->approved_by = Auth::id();
        $tenant->save();

        // Send notification to tenant
        LandlordPricePlanAndTenantCreate::notifyTenantApproval($tenant, 'rejected', $request->rejection_reason);

        return back()->with(FlashMsg::explain('success', __('Tenant rejected successfully.')));
    }

    /**
     * Bulk approve tenants
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'tenant_ids' => 'required|array',
            'tenant_ids.*' => 'string'
        ]);

        $count = 0;
        foreach ($request->tenant_ids as $tenant_id) {
            $tenant = Tenant::find($tenant_id);
            if ($tenant && $tenant->approval_status !== 'approved') {
                $tenant->approval_status = 'approved';
                $tenant->approved_at = now();
                $tenant->approved_by = Auth::id();
                $tenant->save();

                LandlordPricePlanAndTenantCreate::notifyTenantApproval($tenant, 'approved');
                $count++;
            }
        }

        return back()->with(FlashMsg::explain('success', sprintf(__('%d tenants approved successfully.'), $count)));
    }

    /**
     * Bulk reject tenants
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'tenant_ids' => 'required|array',
            'tenant_ids.*' => 'string',
            'rejection_reason' => 'required|string|max:500'
        ]);

        $count = 0;
        foreach ($request->tenant_ids as $tenant_id) {
            $tenant = Tenant::find($tenant_id);
            if ($tenant && $tenant->approval_status !== 'rejected') {
                $tenant->approval_status = 'rejected';
                $tenant->approval_note = $request->rejection_reason;
                $tenant->approved_by = Auth::id();
                $tenant->save();

                LandlordPricePlanAndTenantCreate::notifyTenantApproval($tenant, 'rejected', $request->rejection_reason);
                $count++;
            }
        }

        return back()->with(FlashMsg::explain('success', sprintf(__('%d tenants rejected successfully.'), $count)));
    }

    /**
     * View tenant details
     */
    public function show($tenant_id)
    {
        $tenant = Tenant::with(['user', 'payment_log', 'domain'])->findOrFail($tenant_id);
        $payment_logs = PaymentLogs::where('tenant_id', $tenant_id)->latest()->get();

        return view(self::BASE_PATH . 'show', compact('tenant', 'payment_logs'));
    }

    /**
     * Reset tenant status back to pending
     */
    public function resetToPending($tenant_id)
    {
        $tenant = Tenant::findOrFail($tenant_id);

        $tenant->approval_status = 'pending';
        $tenant->approval_note = null;
        $tenant->approved_at = null;
        $tenant->approved_by = null;
        $tenant->save();

        return back()->with(FlashMsg::explain('success', __('Tenant status reset to pending.')));
    }
}
