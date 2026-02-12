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
     * @param  array  $orderData  تمثيل مبسّط للـ Order (هنتوسع فيه بعدين)
     */
    public function createShipmentForOrder(array $orderData): Shipment
    {
        // TODO: هنا بنبني الـ Payload الحقيقي حسب JSON بتاع SideUp
        $payload = [
            'reference' => $orderData['reference'] ?? ('order_' . $orderData['id']),
            'from'      => $orderData['from'] ?? [],
            'to'        => $orderData['to'] ?? [],
            'items'     => $orderData['items'] ?? [],
            'cod'       => $orderData['cod'] ?? false,
            'amount'    => $orderData['amount'] ?? 0,
        ];

        $response = $this->client->createShipment($payload);

        Log::info('SideUp shipment created', ['order_id' => $orderData['id'] ?? null, 'response' => $response]);

        return Shipment::create([
            'order_id'            => $orderData['id'] ?? null,
            'provider'            => 'sideup',
            'external_shipment_id'=> $response['id']     ?? null,
            'tracking_number'     => $response['tracking_number'] ?? null,
            'carrier_name'        => $response['carrier'] ?? null,
            'service_type'        => $response['service'] ?? null,
            'status'              => $response['status']  ?? 'created',
            'label_url'           => $response['label_url'] ?? null,
            'tracking_url'        => $response['tracking_url'] ?? null,
            'shipping_cost'       => $response['price'] ?? null,
            'currency'            => $response['currency'] ?? null,
            'meta'                => $response,
        ]);
    }
}

