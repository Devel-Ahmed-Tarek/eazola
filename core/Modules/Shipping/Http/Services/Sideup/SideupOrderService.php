<?php

namespace Modules\Shipping\Http\Services\Sideup;

use Modules\Shipping\Entities\Shipment;
use Illuminate\Support\Facades\Log;

/**
 * SideupOrderService
 *
 * مسؤول عن تحويل Order من نظامك → Shipment عند SideUp
 * - ياخد Order Model (أو Array حالياً)
 * - يبني Payload
 * - ينادي SideupClient
 * - يخزن Shipment في DB (جدول shipments لكل Tenant)
 */
class SideupOrderService
{
    public function __construct(protected SideupClient $client)
    {
    }

    /**
     * إنشاء شحنة من طلب المنتج وفق OpenAPI SideUp: POST /merchants/order/store
     *
     * @param  array  $orderData  يجب أن يحتوي: to.name, to.phone, to.address؛ و drop { zone, city, area } (من إعدادات الحساب أو الطلب)
     */
    public function createShipmentForOrder(array $orderData): Shipment
    {
        $to = $orderData['to'] ?? [];
        $drop = $orderData['drop'] ?? ['zone' => 0, 'city' => 0, 'area' => 0];

        $payload = [
            'receiver_name'    => $to['name'] ?? '',
            'receiver_phone'   => $to['phone'] ?? '',
            'receiver_address' => $to['address'] ?? '',
            'drop'             => [
                'zone' => (int) ($drop['zone'] ?? 0),
                'city' => (int) ($drop['city'] ?? 0),
                'area' => (int) ($drop['area'] ?? 0),
            ],
            'item_cost'        => (float) ($orderData['amount'] ?? 0),
            'description'      => $orderData['description'] ?? ('Order #' . ($orderData['id'] ?? '')),
            'notes'            => $orderData['notes'] ?? null,
        ];

        if (! empty($to['extra_phone'] ?? null)) {
            $payload['receiver_extra_phone'] = $to['extra_phone'];
        }
        if (isset($orderData['courier_id'])) {
            $payload['courier_id'] = (int) $orderData['courier_id'];
        }
        if (isset($orderData['pickup_location'])) {
            $payload['pickup_location'] = (int) $orderData['pickup_location'];
        }

        $response = $this->client->createOrder($payload);
        $data = $response['data'] ?? $response;

        Log::info('SideUp order created', ['order_id' => $orderData['id'] ?? null, 'response' => $response]);

        return Shipment::create([
            'order_id'             => $orderData['id'] ?? null,
            'provider'             => 'sideup',
            'external_shipment_id'  => $data['id'] ?? null,
            'tracking_number'      => $data['shipment_code'] ?? null,
            'carrier_name'         => $data['carrier'] ?? null,
            'service_type'         => $data['service'] ?? null,
            'status'               => $data['status'] ?? 'to_be_assigned',
            'label_url'            => $data['label_url'] ?? null,
            'tracking_url'         => $data['tracking_url'] ?? null,
            'shipping_cost'        => $data['merchant_delivery_fees'] ?? null,
            'currency'             => $data['currency'] ?? null,
            'meta'                 => $data,
        ]);
    }
}

