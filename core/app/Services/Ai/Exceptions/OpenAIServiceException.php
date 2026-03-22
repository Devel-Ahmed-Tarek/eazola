<?php

namespace App\Services\Ai\Exceptions;

use RuntimeException;
use Throwable;

/**
 * خطأ من طبقة OpenAI (مفتاح ناقص، رد HTTP غير متوقع، إلخ).
 */
class OpenAIServiceException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        protected readonly ?int $httpStatus = null,
        protected readonly ?string $responseBody = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }
}
