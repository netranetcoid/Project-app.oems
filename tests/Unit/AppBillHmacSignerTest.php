<?php

namespace Tests\Unit;

use App\Services\Integration\AppBillHmacSigner;
use PHPUnit\Framework\TestCase;

class AppBillHmacSignerTest extends TestCase
{
    public function test_it_builds_the_exact_v2_canonical_signature(): void
    {
        $headers = (new AppBillHmacSigner)->sign(
            'post', '/api/test?ignored=yes', '{"value":"unchanged"}', 'secret',
            '2026-07-22T00:00:00+00:00', '12345678-1234-4234-8234-123456789012',
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        );
        $canonical = "2026-07-22T00:00:00+00:00\n12345678-1234-4234-8234-123456789012\naaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa\nPOST\n/api/test\n{\"value\":\"unchanged\"}";

        self::assertSame('2', $headers['X-Signature-Version']);
        self::assertSame('sha256='.hash_hmac('sha256', $canonical, 'secret'), $headers['X-Signature']);
        self::assertMatchesRegularExpression('/^sha256=[0-9a-f]{64}$/', $headers['X-Signature']);
    }

    public function test_each_attempt_gets_new_security_identity(): void
    {
        $signer = new AppBillHmacSigner;
        $first = $signer->sign('POST', '/api/test', '{}', 'secret');
        $second = $signer->sign('POST', '/api/test', '{}', 'secret');

        self::assertNotSame($first['X-Nonce'], $second['X-Nonce']);
        self::assertNotSame($first['X-Request-Id'], $second['X-Request-Id']);
    }
}
