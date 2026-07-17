<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\IntegrationConnection;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppBillIntegrationRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyCode = trim((string) $request->header('X-Company-Code'));
        $requestId = trim((string) $request->header('X-Request-Id'));
        if ($companyCode === '' || $requestId === '') {
            return $this->reject('Header integrasi tidak lengkap.');
        }

        $company = Company::query()->where('code', $companyCode)->active()->first();
        $connection = $company
            ? IntegrationConnection::forCompany((int) $company->id)->where('provider', 'appbill')->first()
            : null;
        if (! $connection || ! $connection->is_enabled || ! $connection->allow_inbound || $connection->mode !== 'live') {
            return $this->reject('Koneksi integrasi tidak aktif.');
        }

        // Token dan HMAC tersimpan encrypted pada integration_connections.
        // Keduanya wajib agar endpoint tidak dapat diakses hanya dengan tahu URL.
        $credentials = $connection->credentials ?? [];
        $token = (string) ($credentials['api_token'] ?? '');
        $secret = (string) ($credentials['hmac_secret'] ?? '');
        $bearer = preg_replace('/^Bearer\\s+/i', '', (string) $request->header('Authorization'));
        $givenSignature = preg_replace('/^sha256=/i', '', (string) $request->header('X-Signature'));
        $expectedSignature = $secret === '' ? '' : hash_hmac('sha256', $request->getContent(), $secret);

        if ($token === '' || $secret === '' || ! hash_equals($token, $bearer) || ! hash_equals($expectedSignature, $givenSignature)) {
            return $this->reject('Autentikasi integrasi tidak valid.');
        }

        $request->attributes->set('appbill.company', $company);
        $request->attributes->set('appbill.connection', $connection);
        $request->attributes->set('appbill.request_id', $requestId);

        return $next($request);
    }

    private function reject(string $message): Response
    {
        // Jangan pernah mengembalikan tabel, SQL, token, maupun stack trace.
        return response()->json(['success' => false, 'message' => $message], 401);
    }
}
