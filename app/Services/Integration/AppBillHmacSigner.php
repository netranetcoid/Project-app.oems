<?php

namespace App\Services\Integration;

use Illuminate\Support\Str;

final class AppBillHmacSigner
{
    public function sign(
        string $method,
        string $path,
        string $rawBody,
        string $secret,
        ?string $timestamp = null,
        ?string $nonce = null,
        ?string $requestId = null,
    ): array {
        $timestamp ??= now('UTC')->toIso8601String();
        $nonce ??= (string) Str::uuid();
        $requestId ??= (string) Str::uuid();
        $path = '/'.ltrim((string) parse_url($path, PHP_URL_PATH), '/');
        $canonical = implode("\n", [
            $timestamp, $nonce, $requestId, strtoupper($method), $path, $rawBody,
        ]);

        return [
            'X-Request-Id' => $requestId,
            'X-Timestamp' => $timestamp,
            'X-Nonce' => $nonce,
            'X-Signature-Version' => '2',
            'X-Signature' => 'sha256='.hash_hmac('sha256', $canonical, $secret),
        ];
    }
}
