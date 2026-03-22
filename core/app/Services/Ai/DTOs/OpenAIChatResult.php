<?php

namespace App\Services\Ai\DTOs;

/**
 * نتيجة موحّدة لطلب chat completions — تُستخدم لاحقًا لإنشاء مقالات، منشورات، إلخ.
 */
final class OpenAIChatResult
{
    public function __construct(
        public readonly string $content,
        public readonly ?string $model = null,
        public readonly ?int $promptTokens = null,
        public readonly ?int $completionTokens = null,
        public readonly ?int $totalTokens = null,
        public readonly ?string $finishReason = null,
        public readonly array $raw = [],
    ) {
    }

    /**
     * نص واحد فقط (أول اختيار من الـ API).
     */
    public static function fromApiResponse(array $json): self
    {
        $choice = $json['choices'][0] ?? [];
        $message = $choice['message'] ?? [];
        $content = is_string($message['content'] ?? null)
            ? $message['content']
            : '';

        $usage = $json['usage'] ?? [];

        return new self(
            content: $content,
            model: $json['model'] ?? null,
            promptTokens: isset($usage['prompt_tokens']) ? (int) $usage['prompt_tokens'] : null,
            completionTokens: isset($usage['completion_tokens']) ? (int) $usage['completion_tokens'] : null,
            totalTokens: isset($usage['total_tokens']) ? (int) $usage['total_tokens'] : null,
            finishReason: $choice['finish_reason'] ?? null,
            raw: $json,
        );
    }
}
