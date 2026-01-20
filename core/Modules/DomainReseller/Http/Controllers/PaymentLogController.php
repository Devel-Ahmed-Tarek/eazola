<?php

namespace Modules\DomainReseller\Http\Controllers;

use App\Helpers\Payment\PaymentGatewayCredential;
use App\Mail\BasicMail;
use Illuminate\Support\Facades\Mail;
use Modules\DomainReseller\Entities\DomainPaymentLog;
use Modules\DomainReseller\Http\Enums\StatusEnum;
use Modules\DomainReseller\Http\Services\DomainService;

class PaymentLogController
{
    public function order_payment_cancel($id)
    {
        $extracted_id = null;
        if (!empty($id))
        {
            $extracted_id = unwrap_random_number($id);
        }

        $order_details = DomainPaymentLog::findOrFail($extracted_id);
        return view('domainreseller::backend.payment-pages.cancel', compact('order_details'));
    }

    public function order_payment_cancel_static()
    {
        return view('domainreseller::backend.payment-pages.cancel-static');
    }

    public function order_confirm($id)
    {
        $order_details = DomainPaymentLog::select('id','domain','period')->find($id);
        return themeView('tenant.frontend.pages.package.order-page')->with(['order_details' => $order_details]);
    }


    public function order_payment_success($id)
    {
        $order_details = '';
        if (!empty($id)) {
            $extract_id = substr($id, 6);
            $extract_id = substr($extract_id, 0, -6);

            $order_details = DomainPaymentLog::find($extract_id);

            abort_if(empty($order_details), 404);
        }

        return view('domainreseller::backend.payment-pages.success', compact('order_details'));
    }

    public function globalIpn($gateway_name, $data = null)
    {
        if ($gateway_name === 'paystack')
        {
            return $this->common_ipn_data($data);
        }

        $gateway_name = strtolower(trim($gateway_name));

        $function_name = "get_{$gateway_name}_credential";
        $response = PaymentGatewayCredential::$function_name();
        $payment_data = $response->ipn_response();

        return $this->common_ipn_data($payment_data);
    }

    private function common_ipn_data($data)
    {
        session()->forget(['cart_domain_data', 'cart_domain', 'agreementKeys']);
        $new_or_renew = session('purchase-option');

        $domain_payment_log = DomainPaymentLog::find($data['order_id']);

        if (!empty($domain_payment_log))
        {
            try {
                $PAYMENT_STATUS = $data['status'] === 'complete';

                $domain_payment_log->update([
                    'payment_status' => $PAYMENT_STATUS,
                    'track' => $data['transaction_id'],
                    'purchase_count' => $domain_payment_log->purchase_count + 1
                ]);

                if ($domain_payment_log['payment_status'])
                {
                    if ($new_or_renew === 'new')
                    {
                        return $this->domainPurchaseAction($domain_payment_log);
                    }
                    else if($new_or_renew === 'renew')
                    {
                        return $this->domainRenewAction($domain_payment_log);
                    }
                }

                return to_route('tenant.admin.domain-reseller.payment.cancel', wrap_random_number($domain_payment_log->id));
            } catch (\Exception $exception)
            {

            }
        }

        return to_route('tenant.admin.domain-reseller.payment.cancel.static');
    }

    private function domainPurchaseAction($domain_payment_log)
    {
        $purchase_body = session('domain_validated_data');
        $result = (new DomainService())->purchaseDomain($purchase_body);

        if (!$result['status'])
        {
            for ($iteration_count = 1; $iteration_count >= 3; $iteration_count++)
            {
                $result = (new DomainService())->purchaseDomain($purchase_body);;

                if ($result['status'])
                {
                    break;
                } else {
                    sleep(1);
                }
            }
        }

        if ($result['status'])
        {
            $domain_payment_log->update([
                'custom_filed' => $result['result'],
                'status' => StatusEnum::ACTIVE
            ]);

            $url = route('tenant.admin.domain-reseller.list.domain');
            $message = "<p>".__('Your domain is ready')." - {$domain_payment_log->domain} - {$domain_payment_log->period} ".__('Year')."</p>";
            $message .= "<p>".__('Now you can activate your domain as your custom domain')."</p></br>";
            $message .= "<p>".__('See More:')." <a href='{$url}'>{$url}</a></p>";
            $this->sendEmailNotification($domain_payment_log->email, $message, __('Domain Purchase Complete'));

            $url = route('landlord.admin.domain-reseller.list.domain');
            $message = "<p>".__('A new domain is purchased by user')." - {$domain_payment_log->domain} - {$domain_payment_log->period} ".__('Year')."</p></br>";
            $message .= "<p>".__('See More:')." <a href='{$url}'>{$url}</a></p>";
            $this->sendEmailNotification(get_static_option_central('site_global_email'), $message, __('Tenant Domain Purchased'));

            return to_route('tenant.admin.domain-reseller.payment.success', wrap_random_number($domain_payment_log->id));
        }

        return to_route('tenant.admin.domain-reseller.payment.cancel', wrap_random_number($domain_payment_log->id));
    }

    private function domainRenewAction($domain_payment_log)
    {
        $renew_body = ["period" => $domain_payment_log->period];
        $result = (new DomainService())->renewDomain($domain_payment_log->domain, $renew_body);

        if ($result['code'] === 404)
        {
            for ($iteration_count = 1; $iteration_count >= 3; $iteration_count++)
            {
                $result = (new DomainService())->renewDomain($domain_payment_log->domain, $renew_body);

                if ($result['code'] !== 404)
                {
                    break;
                } else {
                    sleep(1);
                }
            }
        }

        if ($result['status'])
        {
            $domain_payment_log->update([
                'custom_filed' => $result['result'],
                'status' => StatusEnum::ACTIVE
            ]);

            $url = route('tenant.admin.domain-reseller.list.domain');
            $message = "<p>".__('Your domain is renewed successfully')." - {$domain_payment_log->domain} - {$domain_payment_log->period} ".__('Year')."</p>";
            $message .= "<p>".__('See More:')." <a href='{$url}'>{$url}</a></p>";

            $this->sendEmailNotification($domain_payment_log->email, $message, __('Domain Renew Complete'));

            $url = route('landlord.admin.domain-reseller.list.domain');
            $message = "<p>".__('A domain is renewed successfully')."</p>";
            $message .= "<p>".__('See More:')." <a href='{$url}'>{$url}</a></p>";
            $this->sendEmailNotification(get_static_option_central('site_global_email'), $message, __('Tenant Domain Renew'));

            return to_route('tenant.admin.domain-reseller.payment.success', wrap_random_number($domain_payment_log->id));
        }
    }

    private function sendEmailNotification($email, $message, $subject)
    {
        return Mail::to($email)->send(new BasicMail($message, $subject));
    }
}
