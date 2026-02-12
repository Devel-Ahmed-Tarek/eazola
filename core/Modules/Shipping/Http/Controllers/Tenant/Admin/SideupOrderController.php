<?php

namespace Modules\Shipping\Http\Controllers\Tenant\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\ProductOrder;
use Modules\Shipping\Entities\ShippingAccount;
use Modules\Shipping\Http\Services\Sideup\SideupClient;
use Modules\Shipping\Http\Services\Sideup\SideupOrderService;
use App\Helpers\FlashMsg;

class SideupOrderController extends Controller
{
    public function createShipment(int $orderId, Request $request)
    {
        $order = ProductOrder::with(['shipping'])->findOrFail($orderId);

        if ($order->shipment) {
            return back()->with(FlashMsg::explain('warning', __('Shipment already exists for this order.')));
        }

        $account = ShippingAccount::where('provider', 'sideup')->first();

        if (empty($account?->api_key) || empty($account?->base_url) || !$account->enabled) {
            return back()->with(FlashMsg::explain('danger', __('SideUp integration is not configured or disabled.')));
        }

        // Build minimal order payload – نطوره لاحقاً حسب JSON الرسمي
        $shippingAddress = $order->shipping;

        $orderData = [
            'id'      => $order->id,
            'amount'  => $order->total_amount,
            'reference' => 'order_' . $order->id,
            'from'    => [
                // هنا لاحقاً نجيب عنوان التاجر من إعدادات التيننت
            ],
            'to'      => [
                'name'    => $order->name,
                'phone'   => $order->phone,
                'email'   => $order->email,
                'address' => $shippingAddress?->address ?? $order->address,
                'city'    => $shippingAddress?->city ?? $order->city,
                'state'   => $shippingAddress?->state ?? $order->state,
                'country' => $shippingAddress?->country ?? $order->country,
                'zip'     => $shippingAddress?->zip ?? $order->zipcode,
            ],
            'items'   => json_decode($order->order_details, true) ?? [],
            'cod'     => $order->payment_gateway === 'cod',
        ];

        try {
            $client = new SideupClient($account->base_url, $account->api_key);
            $service = new SideupOrderService($client);

            $service->createShipmentForOrder($orderData);
        } catch (\Throwable $e) {
            return back()->with(FlashMsg::explain('danger', __('Failed to create shipment: ') . $e->getMessage()));
        }

        return back()->with(FlashMsg::create_succeed(__('SideUp shipment created successfully.')));
    }
}

