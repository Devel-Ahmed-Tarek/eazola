<?php

namespace Modules\Shipping\Http\Services\Sideup;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SideupClient
 *
 * طبقة مسؤولة عن التواصل مع SideUp API فقط
 * - مسئولة عن الـ HTTP Requests
 * - لا تفهم تفاصيل الـ Order في مشروعك
 */
class SideupClient
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    /**
     * HTTP client مشترك مع هيدر الـ Auth
     */
    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept'        => 'application/json',
        ])->baseUrl($this->baseUrl)->timeout(15);
    }

    /**
     * مثال: إنشاء شحنة جديدة عند SideUp
     *
     * $payload لازم يكون مطابق للـ JSON Schema بتاعهم (من ملف الـ OpenAPI)
     */
    public function createShipment(array $payload): array
    {
        try {
            $response = $this->client()->post('/shipments', $payload);

            if ($response->failed()) {
                Log::warning('SideUp createShipment failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                $response->throw();
            }

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('SideUp createShipment exception', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * مثال: جلب حالة الشحنة من SideUp
     */
    public function getShipment(string $shipmentId): array
    {
        try {
            $response = $this->client()->get("/shipments/{$shipmentId}");

            if ($response->failed()) {
                Log::warning('SideUp getShipment failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                $response->throw();
            }

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('SideUp getShipment exception', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

