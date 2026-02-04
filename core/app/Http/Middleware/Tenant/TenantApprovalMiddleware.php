<?php

namespace App\Http\Middleware\Tenant;

use Closure;
use Illuminate\Http\Request;

class TenantApprovalMiddleware
{
    /**
     * Handle an incoming request.
     * Check if tenant is approved before allowing access.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenant();
        
        // If no tenant context, continue
        if (is_null($tenant)) {
            return $next($request);
        }

        // Get approval status (default to 'approved' for backward compatibility)
        $approval_status = $tenant->approval_status ?? 'approved';

        // If approved, continue normally
        if ($approval_status === 'approved') {
            return $next($request);
        }

        // Allow access to the pending/rejected status page itself
        if ($request->routeIs('tenant.frontend.approval.status')) {
            return $next($request);
        }
 
        // If pending or rejected, redirect to status page
        return redirect()->route('tenant.frontend.approval.status');
    }
}
