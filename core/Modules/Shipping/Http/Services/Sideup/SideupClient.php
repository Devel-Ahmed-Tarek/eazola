<?php

namespace Modules\Shipping\Http\Services\Sideup;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Shipping\Entities\ShippingAccount;

/**
 * SideupClient
 *
 * طبقة مسؤولة عن التواصل مع SideUp API فقط
 * - تدعم الربط بـ API Key أو بـ Email + Password (Legacy، ويمكن تحميلهم من ملف JSON)
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
     * إنشاء العميل من حساب مخزّن: إما API Key أو Email/Password (يتم استلام توكن من SideUp)
     */
    public static function fromAccount(ShippingAccount $account): ?self
    {
        $baseUrl = $account->base_url ?? '';
        if (empty($baseUrl)) {
            return null;
        }

        $meta = $account->meta ?? [];
        $authType = $meta['auth_type'] ?? 'api_key';

        if ($authType === 'email_password') {
            $email = $meta['email'] ?? '';
            $encPassword = $meta['password'] ?? '';
            if (empty($email) || empty($encPassword)) {
                return null;
            }
            try {
                $password = Crypt::decryptString($encPassword);
            } catch (\Throwable $e) {
                Log::warning('SideUp decrypt password failed', ['error' => $e->getMessage()]);
                return null;
            }
            $token = self::loginAndGetToken(rtrim($baseUrl, '/'), $email, $password);
            if (empty($token)) {
                return null;
            }
            return new self($baseUrl, $token);
        }

        if (!empty($account->api_key)) {
            return new self($baseUrl, $account->api_key);
        }

        return null;
    }

    /**
     * تسجيل الدخول بـ Email + Password واستلام التوكن (Legacy)
     * يفترض SideUp يوفر endpoint مثل POST /auth/login يرجع { "token": "..." } أو { "access_token": "..." }
     */
    protected static function loginAndGetToken(string $baseUrl, string $email, string $password): ?string
    {
        try {
            $response = Http::acceptJson()
                ->baseUrl($baseUrl)
                ->timeout(15)
                ->post('/auth/login', [
                    'email'    => $email,
                    'password' => $password,
                ]);

            if ($response->failed()) {
                Log::warning('SideUp login failed', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $body = $response->json();
            return $body['token'] ?? $body['access_token'] ?? null;
        } catch (\Throwable $e) {
            Log::error('SideUp login exception', ['message' => $e->getMessage()]);
            return null;
        }
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

