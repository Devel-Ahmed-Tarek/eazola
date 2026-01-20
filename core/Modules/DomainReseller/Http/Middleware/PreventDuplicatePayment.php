<?php

namespace Modules\DomainReseller\Http\Middleware;

use App\Helpers\FlashMsg;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\DomainReseller\Entities\DomainPaymentLog;

class PreventDuplicatePayment
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user_id = tenant()->user_id;
        $tenant_id = tenant()->id;
        $domain_data = session('cart_domain_data');
        $domain = $domain_data['data']['domain'];

        $lastTransaction = DomainPaymentLog::where([
            'user_id' => $user_id,
            'tenant_id' =>$tenant_id,
            'domain' => $domain
        ])->latest()->select('id', 'created_at')->first();

        if (!empty($lastTransaction) && Carbon::parse($lastTransaction->created_at)->addMinute() > now())
        {
            return back()->with(FlashMsg::explain('danger', __('Can not proceed duplicate payment, wait for next 1 minutes')))->withInput();
        }

        return $next($request);
    }
}
