<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\IntegrationConnection;
use App\Models\IntegrationReplayNonce;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gerbang inbound AppBill versi 2.
 *
 * HMAC mengikat method + path + body + timestamp + nonce. Request yang sama
 * tidak dapat dipakai ulang dan request lama ditolak sebelum menyentuh data HR.
 */
class EnsureAppBillIntegrationRequest
{
    private const MAX_CLOCK_SKEW_SECONDS = 300;
    private const NONCE_TTL_SECONDS = 600;

    public function handle(Request $request, Closure $next): Response
    {
        $companyCode = trim((string) $request->header('X-Company-Code'));
        $requestId = trim((string) $request->header('X-Request-Id'));
        $timestampHeader = trim((string) $request->header('X-Timestamp'));
        $nonce = trim((string) $request->header('X-Nonce'));
        if ($companyCode === '' || $requestId === '' || $timestampHeader === '' || $nonce === '') {
            return $this->reject('Header keamanan integrasi tidak lengkap.', 401);
        }
        if (! preg_match('/^[A-Za-z0-9._:-]{16,191}$/', $nonce) || strlen($requestId) > 100) {
            return $this->reject('Format request integrasi tidak valid.', 422);
        }

        try { $timestamp = CarbonImmutable::parse($timestampHeader)->utc(); }
        catch (\Throwable) { return $this->reject('Timestamp integrasi tidak valid.', 422); }
        if (abs(now('UTC')->diffInSeconds($timestamp, false)) > self::MAX_CLOCK_SKEW_SECONDS) {
            return $this->reject('Timestamp integrasi sudah kedaluwarsa.', 401);
        }

        $company = Company::query()->where('code', $companyCode)->active()->first();
        $connection = $company
            ? IntegrationConnection::forCompany((int) $company->id)->where('provider', 'appbill')->first()
            : null;
        if (! $connection || ! $connection->is_enabled || ! $connection->allow_inbound || $connection->mode !== 'live') {
            return $this->reject('Koneksi integrasi tidak aktif.', 401);
        }

        $credentials = $connection->credentials ?? [];
        $token = (string) ($credentials['api_token'] ?? '');
        $secret = (string) ($credentials['hmac_secret'] ?? '');
        $bearer = preg_replace('/^Bearer\s+/i', '', (string) $request->header('Authorization'));
        $givenSignature = preg_replace('/^sha256=/i', '', (string) $request->header('X-Signature'));
        $canonical = implode("\n", [$timestamp->toIso8601String(), $nonce, $requestId, strtoupper($request->method()), '/'.ltrim($request->path(), '/'), $request->getContent()]);
        $expectedSignature = $secret === '' ? '' : hash_hmac('sha256', $canonical, $secret);

        if ($token === '' || $secret === '' || ! hash_equals($token, $bearer) || ! hash_equals($expectedSignature, $givenSignature)) {
            return $this->reject('Autentikasi integrasi tidak valid.', 401);
        }

        try {
            DB::transaction(function () use ($company, $connection, $nonce, $requestId, $timestamp): void {
                IntegrationReplayNonce::query()->where('expires_at', '<', now())->delete();
                IntegrationReplayNonce::create([
                    'company_id' => $company->id, 'integration_connection_id' => $connection->id,
                    'provider' => 'appbill', 'nonce' => $nonce, 'request_id' => $requestId,
                    'request_timestamp' => $timestamp, 'expires_at' => now()->addSeconds(self::NONCE_TTL_SECONDS),
                ]);
            });
        } catch (QueryException) {
            // Unique index adalah sumber kebenaran, bukan cache yang bisa terlewat.
            return $this->reject('Request integrasi terdeteksi berulang.', 409);
        }

        $request->attributes->set('appbill.company', $company);
        $request->attributes->set('appbill.connection', $connection);
        $request->attributes->set('appbill.request_id', $requestId);
        return $next($request);
    }

    private function reject(string $message, int $status): Response
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
