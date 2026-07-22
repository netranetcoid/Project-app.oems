<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AppBillAttendanceEventRequest;
use App\Services\Integration\AppBillAttendanceEventService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Throwable;

class AppBillAttendanceEventController extends Controller
{
    public function __construct(private AppBillAttendanceEventService $service) {}

    public function store(AppBillAttendanceEventRequest $request): JsonResponse
    {
        try {
            $result = $this->service->handle(
                $request->attributes->get('appbill.company'),
                $request->attributes->get('appbill.connection'),
                $request->validated(),
                hash('sha256', $request->getContent()),
            );
        } catch (ConflictHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Event atau revision digunakan untuk payload berbeda.',
            ], 409);
        } catch (ModelNotFoundException) {
            return response()->json(['success' => false, 'message' => 'Source attendance tidak ditemukan.'], 404);
        } catch (QueryException) {
            return response()->json(['success' => false, 'message' => 'Integrasi sementara tidak tersedia.'], 503);
        } catch (Throwable) {
            return response()->json(['success' => false, 'message' => 'Terjadi kegagalan internal integrasi.'], 500);
        }

        return response()->json(['success' => true, 'data' => $result], 202);
    }
}
