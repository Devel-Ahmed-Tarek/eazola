<?php

namespace Modules\DomainReseller\Http\Services;

use App\Helpers\SanitizeInput;
use Modules\DomainReseller\Http\Services\Providers\GoDaddy;

class DomainService
{
    public static function providers()
    {
        $providers = [
            "godaddy" => [
                "name" => __("GoDaddy"),
                "logo" => "godaddy.webp",
                "reference" => "https://developer.godaddy.com/getstarted",
                "configuration" => true,
                "service_class" => GoDaddy::class,
                "nameservers" => ["ns07.domaincontrol.com", "ns08.domaincontrol.com"],
                "currency_micro_unit" => 1000000
            ]
        ];

        foreach ($providers as $key => $value) {
            $providers[$key]['slug'] = $key;
        }

        return $providers;
    }

    public function currentProvider()
    {
        $active_provider = get_static_option_central('active_domain_provider');
        $service_class = self::providers();
        return $service_class[$active_provider];
    }

    private function currentServiceClass()
    {
        $current_provider = $this->currentProvider();
        return (new $current_provider['service_class']);
    }

    public function isTestMode(): bool
    {
        $currentService = $this->currentServiceClass();
        return (bool) $currentService->testMode;
    }

    public function search($domain_name)
    {
        if (!$this->isActive()) {
            return $this->activeValidation();
        }

        $response_data = [
            "provider" => $this->currentProvider()['slug'],
            "exception" => false
        ];

        try {
            $domain_name = trim(SanitizeInput::esc_html($domain_name));
            $result = $this->currentServiceClass()->search($domain_name);

            if (!$result->ok()) {
                throw new \Exception($result->body(), $result->getStatusCode());
            } else {
                $json_converted = json_decode($result->body());
                $response_data['result'] = $json_converted;
                $response_data['status'] = true;

                $response_data['result']->price /= $this->currentProvider()['currency_micro_unit'] ?? 1;
            }
        } catch (\Exception $exception) {
            $full_message = json_decode($exception->getMessage());
            $response_data = $response_data + [
                    'status' => false,
                    'code' => $exception->getCode(),
                    'reason' => $exception->getCode(),
                    'message' => $exception->getCode() === 422 ? __("Request body doesn't fulfill schema.") : (empty($full_message) ?: $full_message->message),
                    'result' => $full_message,
                ];

            if ($response_data['code'] === 422) {
                $suggestion = $this->suggest($domain_name);
                $response_data['suggestion'] = $suggestion['result'];
                $response_data['exception'] = true;
            }
        }

        return $response_data;
    }

    public function suggest($domain_name)
    {
        if (!$this->isActive()) {
            return $this->activeValidation();
        }

        $response_data = [
            "provider" => $this->currentProvider()['slug']
        ];

        try {
            $domain_name = trim(SanitizeInput::esc_html($domain_name));
            $suggest_result = $this->currentServiceClass()->suggest($domain_name);

            if (!$suggest_result->ok()) {
                throw new \Exception($suggest_result->body(), $suggest_result->getStatusCode());
            } else {
                $json_converted = json_decode($suggest_result->body());
                $response_data['result'] = $json_converted;
                $response_data['status'] = true;
            }
        } catch (\Exception $exception) {
            $full_message = json_decode($exception->getMessage());
            $response_data = $response_data + [
                    'status' => false,
                    'code' => $exception->getCode(),
                    'message' => $full_message->message ?? ''
                ];
        }

        return $response_data;
    }

    public function showAgreements()
    {
        try {
            $session_data = session('cart_domain');

            $domain_name = !empty($session_data) ? $session_data['domain_name'] : '';
            $privacy = !empty($session_data) ? $session_data['privacy_request'] : '';

            $result = $this->currentServiceClass()->agreements($domain_name, $privacy);

            $response_data = [];
            if (!$result->ok()) {
                throw new \Exception($result->body(), $result->getStatusCode());
            } else {
                $json_converted = json_decode($result->body());
                $response_data = [
                    'result' => $json_converted,
                    'status' => true
                ];
            }
        } catch (\Exception $exception) {
            $full_message = json_decode($exception->getMessage());
            $response_data = [
                    'status' => false,
                    'code' => $exception->getCode(),
                    'message' => $full_message->message ?? ''
                ];
        }

        return $response_data;
    }

    public function purchaseValidator($body)
    {
        try {
            $result = $this->currentServiceClass()->purchaseValidate($body);

            $response_data = [];
            if (!$result->ok()) {
                throw new \Exception($result->body(), $result->getStatusCode());
            } else {
                $json_converted = json_decode($result->body());
                $response_data = [
                    'result' => $json_converted,
                    'status' => true
                ];
            }
        } catch (\Exception $exception) {
            $full_message = json_decode($exception->getMessage());
            $response_data = [
                'status' => false,
                'code' => $exception->getCode(),
                'message' => $full_message->message ?? '',
                'fields' => $full_message->fields ?? []
            ];
        }

        return $response_data;
    }

    private function isActive(): bool
    {
        return !empty(get_static_option_central('active_domain_provider'));
    }

    private function activeValidation()
    {
        if (!$this->isActive()) {
            return [
                "status" => false,
                "code" => 404,
                "message" => __('Service is unavailable.')
            ];
        }
    }

    public function getCountries(): ?array
    {
        if (!$this->isActive()) {
            return $this->activeValidation();
        }

        $response_data = [];
        try {
            $result = $this->currentServiceClass()->countries();
            if ($result->ok())
            {
                $response_data = [
                    "status" => true,
                    "code" => 200,
                    "countries" => json_decode($result->body()),
                    "message" => __('Country list found')
                ];
            }
        } catch (\Exception $exception)
        {
            $response_data = [
                "status" => false,
                "code" => $exception->getCode(),
                "message" => $exception->getMessage()
            ];
        }

        return $response_data;
    }

    public function getStates($countryKey): ?array
    {
        if (!$this->isActive()) {
            return $this->activeValidation();
        }

        $response_data = [];
        try {
            $result = $this->currentServiceClass()->states($countryKey);
            if ($result->ok())
            {
                $response_data = [
                    "status" => true,
                    "code" => 200,
                    "states" => json_decode($result->body()),
                    "message" => __('state list found')
                ];
            }
        } catch (\Exception $exception)
        {
            $response_data = [
                "status" => false,
                "code" => $exception->getCode(),
                "message" => $exception->getMessage()
            ];
        }

        return $response_data;
    }

    public function purchaseValidation($request_validated)
    {
        $domain_data = session('cart_domain_data');

        $contactAdmin = [
            "addressMailing" => [
                "address1" => $request_validated["address1"],
                "address2" => $request_validated["address2"],
                "city" => $request_validated["city"],
                "country" => $request_validated["country"],
                "postalCode" => $request_validated["postalCode"],
                "state" => $request_validated["state"],
            ],
            "email" => $request_validated["email"],
            "fax" => $request_validated["fax"],
            "jobTitle" => $request_validated["jobTitle"],
            "nameFirst" => $request_validated["nameFirst"],
            "nameLast" => $request_validated["nameLast"],
            "nameMiddle" => $request_validated["nameMiddle"],
            "organization" => $request_validated["organization"],
            "phone" => $request_validated["phone"],
        ];

        $contactBilling = array_key_exists('contact_billing_same_contact_admin', $request_validated) && $request_validated['contact_billing_same_contact_admin'] === 'selected'
            ? $contactAdmin
            : [
                "addressMailing" => [
                    "address1" => $request_validated["contact_billing_address1"],
                    "address2" => $request_validated["contact_billing_address2"],
                    "city" => $request_validated["contact_billing_city"],
                    "country" => $request_validated["contact_billing_country"],
                    "postalCode" => $request_validated["contact_billing_postalCode"],
                    "state" => $request_validated["contact_billing_state"],
                ],
                "email" => $request_validated["contact_billing_email"],
                "fax" => $request_validated["contact_billing_fax"],
                "jobTitle" => $request_validated["contact_billing_jobTitle"],
                "nameFirst" => $request_validated["contact_billing_nameFirst"],
                "nameLast" => $request_validated["contact_billing_nameLast"],
                "nameMiddle" => $request_validated["contact_billing_nameMiddle"],
                "organization" => $request_validated["contact_billing_organization"],
                "phone" => $request_validated["contact_billing_phone"],
            ];

        $contactRegistrant = array_key_exists('contact_registrant_same_contact_admin', $request_validated) && $request_validated['contact_registrant_same_contact_admin'] === 'selected'
            ? $contactAdmin
            : [
                "addressMailing" => [
                    "address1" => $request_validated["contact_registrant_address1"],
                    "address2" => $request_validated["contact_registrant_address2"],
                    "city" => $request_validated["contact_registrant_city"],
                    "country" => $request_validated["contact_registrant_country"],
                    "postalCode" => $request_validated["contact_registrant_postalCode"],
                    "state" => $request_validated["contact_registrant_state"],
                ],
                "email" => $request_validated["contact_registrant_email"],
                "fax" => $request_validated["contact_registrant_fax"],
                "jobTitle" => $request_validated["contact_registrant_jobTitle"],
                "nameFirst" => $request_validated["contact_registrant_nameFirst"],
                "nameLast" => $request_validated["contact_registrant_nameLast"],
                "nameMiddle" => $request_validated["contact_registrant_nameMiddle"],
                "organization" => $request_validated["contact_registrant_organization"],
                "phone" => $request_validated["contact_registrant_phone"],
            ];

        $contactTech = array_key_exists('contact_tech_same_contact_admin', $request_validated) && $request_validated['contact_tech_same_contact_admin'] === 'selected'
            ? $contactAdmin
            : [
                "addressMailing" => [
                    "address1" => $request_validated["contact_tech_address1"],
                    "address2" => $request_validated["contact_tech_address2"],
                    "city" => $request_validated["contact_tech_city"],
                    "country" => $request_validated["contact_tech_country"],
                    "postalCode" => $request_validated["contact_tech_postalCode"],
                    "state" => $request_validated["contact_tech_state"],
                ],
                "email" => $request_validated["contact_tech_email"],
                "fax" => $request_validated["contact_tech_fax"],
                "jobTitle" => $request_validated["contact_tech_jobTitle"],
                "nameFirst" => $request_validated["contact_tech_nameFirst"],
                "nameLast" => $request_validated["contact_tech_nameLast"],
                "nameMiddle" => $request_validated["contact_tech_nameMiddle"],
                "organization" => $request_validated["contact_tech_organization"],
                "phone" => $request_validated["contact_tech_phone"],
            ];

        $prepared_api_body = [
            "consent" => [
                "agreedAt" => now()->format('Y-m-d\TH:i:s\Z'),
                "agreedBy" => $domain_data['ip'],
                "agreementKeys" => $domain_data['agreement_keys'] ? ["DNRA"] : [],
            ],
            "contactAdmin" => $contactAdmin,
            "contactBilling" => $contactBilling,
            "contactRegistrant" => $contactRegistrant,
            "contactTech" => $contactTech,
            "domain" => $domain_data['data']['domain'],
            "nameServers" => $this->currentProvider()['nameservers'],
            "period" => (int)$request_validated["period"],
            "privacy" => false,
            "renewAuto" => $request_validated["auto_renew"],
        ];

        return [
            'validated_result' => (new DomainService())->purchaseValidator($prepared_api_body),
            'validated_data' => $prepared_api_body
        ];
    }

    public function purchaseDomain($body)
    {
        try {
            $result = $this->currentServiceClass()->purchase($body);

            $response_data = [];
            if (!$result->ok()) {
                throw new \Exception($result->body(), $result->getStatusCode());
            } else {
                $json_converted = json_decode($result->body());
                $response_data = [
                    'result' => $json_converted,
                    'status' => true
                ];
            }
        } catch (\Exception $exception) {
            $full_message = json_decode($exception->getMessage());
            $response_data = [
                'status' => false,
                'code' => $exception->getCode(),
                'message' => $full_message->message ?? '',
                'fields' => $full_message->fields ?? []
            ];
        }

        return $response_data;
    }

    public function renewDomain($domain_name, $body)
    {
        try {
            $result = $this->currentServiceClass()->renew($domain_name ,$body);

            $response_data = [];
            if (!$result->ok()) {
                throw new \Exception($result->body(), $result->getStatusCode());
            } else {
                $json_converted = json_decode($result->body());
                $response_data = [
                    'result' => $json_converted,
                    'status' => true,
                    'code' => 200
                ];
            }
        } catch (\Exception $exception) {
            $full_message = json_decode($exception->getMessage());
            $response_data = [
                'status' => false,
                'code' => $exception->getCode(),
                'message' => $full_message->message ?? '',
                'fields' => $full_message->fields ?? []
            ];
        }

        return $response_data;
    }

    public function setDNSRecord($domain_name, $body)
    {
        try {
            $result = $this->currentServiceClass()->setARecord($domain_name, $body);

            $response_data = [];
            if (!$result->ok()) {
                throw new \Exception($result->body(), $result->getStatusCode());
            } else {
                $json_converted = json_decode($result->body());
                $response_data = [
                    'result' => $json_converted,
                    'status' => true
                ];
            }
        } catch (\Exception $exception) {
            $full_message = json_decode($exception->getMessage());
            $response_data = [
                'status' => false,
                'code' => $exception->getCode(),
                'message' => $full_message->message ?? '',
                'fields' => $full_message->fields ?? []
            ];
        }

        return $response_data;
    }
}
