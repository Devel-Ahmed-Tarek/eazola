<?php

namespace Modules\DomainReseller\Http\Services\Providers;

use http\Exception\UnexpectedValueException;
use Illuminate\Support\Facades\Http;

class GoDaddy
{
    private object $response;
    private string $apiMethod = 'GET';
    private array|string $body;
    protected string $apiKey;
    protected string $apiSecret;
    protected string $apiEndpoint;
    public string $testMode;

    public function __construct()
    {
        $this->apiKey = get_static_option_central('godaddy_api_key') ?? '';
        $this->apiSecret = get_static_option_central('godaddy_api_secret') ?? '';
        $this->testMode = empty(get_static_option_central('godaddy_environment'));
        $this->apiEndpoint = $this->testMode ? 'https://api.ote-godaddy.com' : 'https://api.godaddy.com';
        $this->body = [];

        $this->response = Http::withOptions(["retry_on_failure" => 2])->withHeaders([
            'Authorization' => "sso-key {$this->apiKey}:{$this->apiSecret}"
        ])->acceptJson();
    }

    public function search($domain_name)
    {
        $this->apiEndpoint = $this->apiEndpoint."/v1/domains/available?domain={$domain_name}";
        return $this->match();
    }

    public function suggest($domain_name)
    {
        $this->apiEndpoint = $this->apiEndpoint."/v1/domains/suggest?query={$domain_name}";
        return $this->match();
    }

    public function agreements($domain_name, $privacy)
    {
        $domain = $domain_name;
        $urlParts = parse_url('http://' . $domain);
        $hostParts = explode('.', $urlParts['host']);
        $tld = array_pop($hostParts);

        $this->apiEndpoint = $this->apiEndpoint."/v1/domains/agreements?tlds={$tld}&privacy={$privacy}";
        return $this->match();
    }

    public function countries($marketId = 'US')
    {
        $this->apiEndpoint = $this->apiEndpoint."/v1/countries?marketId=$marketId";
        return $this->match();
    }

    public function states($countryKey, $marketId = 'US')
    {
        $this->apiEndpoint = $this->apiEndpoint."/v1/countries/$countryKey?marketId=$marketId";
        return $this->match();
    }

    public function purchaseValidate($body)
    {
        $this->setConfig('POST', 'v1/domains/purchase/validate', $body);
        return $this->match();
    }

    public function purchase($body)
    {
        $this->setConfig('POST', 'v1/domains/purchase', $body);
        return $this->match();
    }

    public function renew($domain_name ,$body)
    {
        $this->setConfig('POST', "v1/domains/{$domain_name}/renew", $body);
        return $this->match();
    }

    public function setARecord($domain_name, $body)
    {
        $this->setConfig('PUT', "v1/domains/{$domain_name}/records/A/@", $body);
        return $this->match();
    }

    private function setConfig($method, $url, $body = []): void
    {
        $this->apiMethod = $method;
        $this->apiEndpoint = $this->apiEndpoint.'/'.$url;
        $this->body = $body;
    }

    private function match()
    {
        if (in_array(strtolower($this->apiMethod), ['get','post', 'put']))
        {
            $method = strtolower($this->apiMethod);

            if (!empty($this->body))
            {
                return $this->response->$method($this->apiEndpoint, $this->body);
            }

            return $this->response->$method($this->apiEndpoint);
        }

        return (new \Exception('Unknown request method type', 403));
    }
}
