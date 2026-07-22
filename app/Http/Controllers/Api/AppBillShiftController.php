<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AppBillShiftUpsertRequest;
use App\Services\Integration\AppBillShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Throwable;

/**
 * Penerima master shift dari AppBill. Keamanan HMAC telah lolos middleware
 * appbill.integration; controller hanya meneruskan upsert yang idempoten.
 */
final class AppBillShiftController extends Controller
{
    public function __construct(private readonly AppBillShiftService $service) {}

    public function store(AppBillShiftUpsertRequest $request): JsonResponse
    {
        try {
            $result = $this->service->upsert(
                $request->attributes->get('appbill.company'),
                $request->attributes->get('appbill.connection'),
                $request->validated(),
                hash('sha256', $request->getContent()),
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (ConflictHttpException) {
            return response()->json(['success' => false, 'message' => 'Event shift atau versi sudah digunakan untuk payload berbeda.'], 409);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['success' => false, 'message' => 'Sinkronisasi master shift sementara tidak tersedia.'], 503);
        }

        return response()->json(['success' => true, 'data' => $result], 202);
    }
}
