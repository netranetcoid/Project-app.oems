<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\IntegrationConnection;
use App\Services\Integration\AppBillAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppBillAttendanceController extends Controller
{
    public function __construct(private AppBillAttendanceService $service)
    {
    }

    public function employees(Request $request): JsonResponse
    {
        $company = $this->company($request);
        $items = $this->service->employees($company, (int) $request->integer('per_page', 100));
        return $this->paginated($items, fn ($employee) => $this->service->employeePayload($employee));
    }

    public function shifts(Request $request): JsonResponse
    {
        $company = $this->company($request);
        $items = $this->service->shifts($company, (int) $request->integer('per_page', 100));
        return $this->paginated($items, fn ($shift) => $this->service->shiftPayload($shift, $company->timezone ?: 'Asia/Jakarta'));
    }

    public function attendance(Request $request): JsonResponse
    {
        $company = $this->company($request);
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $items = $this->service->attendances(
            $company,
            $validated['start_date'],
            $validated['end_date'],
            (int) ($validated['per_page'] ?? 100)
        );
        return $this->paginated($items, fn ($attendance) => $this->service->attendancePayload($attendance));
    }

    public function showAttendance(Request $request, string $sourceRecordId): JsonResponse
    {
        $attendance = Attendance::query()
            ->with(['employee.division', 'employee.company', 'shift'])
            ->where('company_id', $this->company($request)->id)
            ->where('source_record_id', $sourceRecordId)
            ->firstOrFail();
        return response()->json(['success' => true, 'data' => $this->service->attendancePayload($attendance)]);
    }

    public function storeAttendance(Request $request): JsonResponse
    {
        return $this->upsert($request);
    }

    public function updateAttendance(Request $request, string $sourceRecordId): JsonResponse
    {
        $data = $request->all();
        $payload = $data['data'] ?? $data;
        if (($payload['source_record_id'] ?? $sourceRecordId) !== $sourceRecordId) {
            throw ValidationException::withMessages(['source_record_id' => ['Tidak sama dengan source_record_id pada URL.']]);
        }
        data_set($data, 'data.source_record_id', $sourceRecordId);
        $request->replace($data);
        return $this->upsert($request);
    }

    public function destroyAttendance(Request $request, string $sourceRecordId): JsonResponse
    {
        $result = $this->service->cancelInbound(
            $this->company($request),
            $this->connection($request),
            $sourceRecordId,
            $request->all(),
            (string) $request->attributes->get('appbill.request_id')
        );
        return response()->json(['success' => true, 'message' => 'Data absensi dibatalkan secara soft cancel.', 'data' => $result], 202);
    }

    private function upsert(Request $request): JsonResponse
    {
        $result = $this->service->upsertInbound(
            $this->company($request),
            $this->connection($request),
            $request->all(),
            (string) $request->attributes->get('appbill.request_id')
        );
        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil diterima.',
            'data' => [
                'sync_id' => $result['sync_id'],
                'status' => $result['status'],
                'duplicate' => $result['duplicate'] ?? false,
            ],
        ], 202);
    }

    private function company(Request $request): Company
    {
        return $request->attributes->get('appbill.company');
    }

    private function connection(Request $request): IntegrationConnection
    {
        return $request->attributes->get('appbill.connection');
    }

    private function paginated($paginator, callable $map): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => collect($paginator->items())->map($map)->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
