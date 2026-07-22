<?php

namespace Tests\Unit;

use App\Http\Requests\Api\AppBillAttendanceEventRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AppBillAttendanceEventRequestTest extends TestCase
{
    public function test_valid_contract_is_accepted(): void
    {
        $request = $this->request($this->payload());
        $validator = Validator::make($request->all(), $request->rules());
        foreach ($request->after() as $after) {
            $validator->after($after);
        }

        self::assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
    }

    public function test_unknown_event_and_header_identity_mismatch_are_rejected(): void
    {
        $payload = $this->payload();
        $payload['event'] = 'attendance.deleted';
        $request = $this->request($payload, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb');
        $validator = Validator::make($request->all(), $request->rules());
        foreach ($request->after() as $after) {
            $validator->after($after);
        }

        self::assertTrue($validator->fails());
        self::assertArrayHasKey('event', $validator->errors()->toArray());
        self::assertArrayHasKey('event_id', $validator->errors()->toArray());
    }

    private function request(array $payload, ?string $headerEventId = null): AppBillAttendanceEventRequest
    {
        return AppBillAttendanceEventRequest::create(
            '/api/v1/integrations/appbill/attendance-events',
            'POST',
            $payload,
            [],
            [],
            [
                'HTTP_X_EVENT_ID' => $headerEventId ?? $payload['event_id'],
                'HTTP_IDEMPOTENCY_KEY' => $payload['idempotency_key'],
                'HTTP_X_COMPANY_CODE' => $payload['company_code'],
            ],
            json_encode($payload, JSON_THROW_ON_ERROR),
        );
    }

    private function payload(): array
    {
        return [
            'schema_version' => '1.0',
            'event' => 'attendance.corrected',
            'event_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'idempotency_key' => 'appoems:attendance:ATT-123:v3',
            'company_code' => 'OEMS',
            'occurred_at' => '2026-07-22T05:00:00+00:00',
            'source' => 'appbill',
            'data' => [
                'source_record_id' => 'ATT-123',
                'version' => 3,
                'check_in' => '2026-07-22T08:00:00+07:00',
                'check_out' => '2026-07-22T17:00:00+07:00',
                'status' => 'present',
                'approval_status' => 'approved',
                'shift_code' => 'REG',
                'is_cancelled' => false,
                'change_reason' => 'Test correction',
                'changed_by' => 'appbill-test',
                'updated_at' => '2026-07-22T17:10:00+07:00',
            ],
        ];
    }
}
