<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuditMutation
{
    public function handle(Request $request, Closure $next): Response
    {
        $incomingId = (string) $request->header('X-Request-ID');
        $requestId = Str::isUuid($incomingId) ? $incomingId : (string) Str::uuid();
        $request->attributes->set('request_id', $requestId);

        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $response;
        }

        try {
            if (! Schema::hasTable('audit_logs')) {
                return $response;
            }

            $user = $request->user();
            $companyId = $request->hasSession()
                ? $request->session()->get('company_id')
                : ($user?->company_id ?? null);

            $blockedFragments = [
                'password', 'token', 'secret', 'credential', 'photo', 'selfie',
                'proof', 'document', 'file', 'latitude', 'longitude',
                'bank_account',
            ];

            // Audit menyimpan nama field saja. Nilai request, foto, GPS,
            // rekening, password dan token tidak pernah disalin ke log.
            $changedFields = collect(array_keys($request->all()))
                ->reject(function (string $key) use ($blockedFragments): bool {
                    $normalized = strtolower($key);
                    return collect($blockedFragments)->contains(
                        fn (string $fragment): bool => str_contains($normalized, $fragment)
                    );
                })
                ->values()
                ->all();

            $ip = $request->ip();
            AuditLog::create([
                'company_id' => $companyId ?: null,
                'user_id' => $user?->id,
                'request_id' => $requestId,
                'action' => $request->route()?->getName() ?: strtolower($request->method()) . ':' . $request->path(),
                'route_name' => $request->route()?->getName(),
                'method' => $request->method(),
                'path' => '/' . ltrim($request->path(), '/'),
                'response_status' => $response->getStatusCode(),
                'changed_fields' => $changedFields,
                'metadata' => ['source' => $request->is('api/*') ? 'api' : 'web'],
                'ip_hash' => $ip ? hash_hmac('sha256', $ip, (string) config('app.key')) : null,
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'occurred_at' => now(),
            ]);
        } catch (Throwable $exception) {
            // Kegagalan audit tidak boleh membuat transaksi bisnis pengguna
            // terlihat gagal setelah respons utamanya sudah berhasil.
            report($exception);
        }

        return $response;
    }
}

