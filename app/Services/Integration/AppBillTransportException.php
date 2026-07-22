<?php

namespace App\Services\Integration;

use RuntimeException;

final class AppBillTransportException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $httpStatus = null,
        public readonly ?int $retryAfterSeconds = null,
        public readonly ?string $requestId = null,
    ) {
        parent::__construct($message, $httpStatus ?? 0);
    }
}
