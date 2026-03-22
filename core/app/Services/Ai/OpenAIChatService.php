<?php

namespace App\Services\Ai;

use App\Services\Ai\DTOs\OpenAIChatResult;
use App\Services\Ai\Exceptions\OpenAIServiceException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * خدمة ربط موحّدة مع OpenAI Chat Completions (ChatGPT).
 *
 * استخدام لاحقًا: إنشاء مقالات، منشورات، إجابات مساعدة، إلخ — بدون تكرار كود HTTP.
 *
 * مثال:
 *   $result = app(OpenAIChatService::class)->chatWithSystem(
 *       userMessage: 'اكتب فقرة تعريفية عن متجرنا',
 *       systemMessage: 'أنت مساعد كتابة عربي. المجال: تجارة إلكترونية.'
 *   );
 *   $text = $result->content;
 */
class OpenAIChatService
{
    public function __construct(
        protected ?string $apiKey = null,
        protected ?string $organization = null,
        protected ?string $baseUrl = null,
        protected ?string $defaultModel = null,
        protected ?int $timeout = null,
    ) {
        $this->apiKey = $apiKey ?? config('openai.api_key');
        $this->organization = $organization ?? config('openai.organization');
        $this->baseUrl = $this->baseUrl ?? config('openai.base_url');
        $this->defaultModel = $defaultModel ?? config('openai.default_model');
        $this->timeout = $timeout ?? (int) config('openai.timeout', 60);
    }

    /**
     * نسخة مخصّصة (مثلاً مفتاح مختلف لكل tenant لاحقًا).
     *
     * @param  array<string, mixed>  $overrides  api_key, organization, base_url, default_model, timeout
     */
    public static function make(array $overrides = []): self
    {
        return new self(
            apiKey: $overrides['api_key'] ?? null,
            organization: $overrides['organization'] ?? null,
            baseUrl: $overrides['base_url'] ?? null,
            defaultModel: $overrides['default_model'] ?? null,
            timeout: isset($overrides['timeout']) ? (int) $overrides['timeout'] : null,
        );
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey) && is_string($this->apiKey);
    }

    /**
     * طلب chat completions كامل المرونة.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options  model, temperature, max_tokens, response_format, ...
     */
    public function chat(array $messages, ?string $model = null, array $options = []): OpenAIChatResult
    {
        if (! $this->isConfigured()) {
            throw new OpenAIServiceException(__('OpenAI API key is not configured. Set OPENAI_API_KEY in .env'));
        }

        $model = $model ?? $options['model'] ?? $this->defaultModel;
        $payload = array_merge([
            'model'    => $model,
            'messages' => $messages,
        ], $this->buildOptionalPayload($options));

        $response = $this->request('POST', '/chat/completions', $payload);

        if ($response->failed()) {
            $this->throwFromFailedResponse($response);
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new OpenAIServiceException(__('Invalid JSON response from OpenAI.'));
        }

        return OpenAIChatResult::fromApiResponse($json);
    }

    /**
     * مساعد سريع: رسالة مستخدم + اختياري system لضبط السياق (مجال الموقع، النبرة، اللغة).
     */
    public function chatWithSystem(
        string $userMessage,
        ?string $systemMessage = null,
        ?string $model = null,
        array $options = [],
    ): OpenAIChatResult {
        $messages = [];
        if ($systemMessage !== null && $systemMessage !== '') {
            $messages[] = ['role' => 'system', 'content' => $systemMessage];
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $this->chat($messages, $model, $options);
    }

    /**
     * إرجاع النص فقط (بدون DTO) — للاستخدام البسيط في واجهات أو أوامر.
     */
    public function textWithSystem(
        string $userMessage,
        ?string $systemMessage = null,
        ?string $model = null,
        array $options = [],
    ): string {
        return $this->chatWithSystem($userMessage, $systemMessage, $model, $options)->content;
    }

    /**
     * يدمج تلقائياً مرجع الموقع المحفوظ (لوحة التحكم → AI site reference) مع تعليمات اختيارية إضافية.
     */
    public function chatWithSiteReference(
        string $userMessage,
        ?string $additionalSystem = null,
        ?string $model = null,
        array $options = [],
    ): OpenAIChatResult {
        $system = app(AiSiteContextService::class)->composeSystemMessage($additionalSystem);

        return $this->chatWithSystem($userMessage, $system, $model, $options);
    }

    /**
     * نفس chatWithSiteReference لكن يعيد النص فقط.
     */
    public function textWithSiteReference(
        string $userMessage,
        ?string $additionalSystem = null,
        ?string $model = null,
        array $options = [],
    ): string {
        return $this->chatWithSiteReference($userMessage, $additionalSystem, $model, $options)->content;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    protected function buildOptionalPayload(array $options): array
    {
        $defaults = config('openai.defaults', []);
        $out = [];

        if (array_key_exists('temperature', $options)) {
            $out['temperature'] = (float) $options['temperature'];
        } elseif (isset($defaults['temperature'])) {
            $out['temperature'] = (float) $defaults['temperature'];
        }

        if (array_key_exists('max_tokens', $options) && $options['max_tokens'] !== null) {
            $out['max_tokens'] = (int) $options['max_tokens'];
        } elseif (! empty($defaults['max_tokens'])) {
            $out['max_tokens'] = (int) $defaults['max_tokens'];
        }

        if (! empty($options['response_format'])) {
            $out['response_format'] = $options['response_format'];
        }

        if (! empty($options['top_p'])) {
            $out['top_p'] = (float) $options['top_p'];
        }

        if (! empty($options['frequency_penalty'])) {
            $out['frequency_penalty'] = (float) $options['frequency_penalty'];
        }

        if (! empty($options['presence_penalty'])) {
            $out['presence_penalty'] = (float) $options['presence_penalty'];
        }

        if (! empty($options['user'])) {
            $out['user'] = (string) $options['user'];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $json
     */
    protected function request(string $method, string $path, array $json): Response
    {
        $url = rtrim((string) $this->baseUrl, '/') . $path;

        $pending = Http::withHeaders(array_filter([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'OpenAI-Organization' => $this->organization ?: null,
        ]))
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout);

        if (strtoupper($method) !== 'POST') {
            throw new OpenAIServiceException('Only POST is supported for OpenAI chat integration.');
        }

        return $pending->post($url, $json);
    }

    protected function throwFromFailedResponse(Response $response): void
    {
        $body = $response->body();
        Log::warning('OpenAI API request failed', [
            'status' => $response->status(),
            'body'   => mb_substr($body, 0, 2000),
        ]);

        $json = $response->json();
        $message = is_array($json) && isset($json['error']['message'])
            ? (string) $json['error']['message']
            : __('OpenAI request failed with HTTP :status', ['status' => $response->status()]);

        throw new OpenAIServiceException(
            message: $message,
            httpStatus: $response->status(),
            responseBody: $body,
        );
    }
}
