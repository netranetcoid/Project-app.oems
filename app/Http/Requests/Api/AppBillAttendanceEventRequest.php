<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppBillAttendanceEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->has('appbill.company');
    }

    public function rules(): array
    {
        $offsetTimestamp = 'regex:/^(?:\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?)(?:Z|[+-]\d{2}:\d{2})$/';

        return [
            'schema_version' => ['required', 'in:1.0'],
            'event' => ['required', Rule::in(['attendance.corrected', 'attendance.approved', 'attendance.cancelled'])],
            'event_id' => ['required', 'uuid'],
            'idempotency_key' => ['required', 'string', 'max:191'],
            'company_code' => ['required', 'string', 'max:100'],
            'occurred_at' => ['required', 'date', $offsetTimestamp],
            'source' => ['required', 'in:appbill'],
            'data' => ['required', 'array'],
            'data.source_record_id' => ['required', 'string', 'max:120'],
            'data.version' => ['required', 'integer', 'min:1'],
            'data.check_in' => ['nullable', 'date', $offsetTimestamp],
            'data.check_out' => ['nullable', 'date', 'after_or_equal:data.check_in', $offsetTimestamp],
            'data.status' => ['required', Rule::in(['present', 'late', 'absent', 'leave', 'sick', 'permission', 'holiday', 'off', 'incomplete'])],
            'data.approval_status' => ['required', Rule::in(['draft', 'submitted', 'approved', 'rejected', 'corrected'])],
            'data.shift_code' => ['nullable', 'string', 'max:50'],
            'data.is_cancelled' => ['required', 'boolean'],
            'data.change_reason' => ['nullable', 'string', 'max:2000'],
            'data.changed_by' => ['required', 'string', 'max:191'],
            'data.updated_at' => ['required', 'date', $offsetTimestamp],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            $checks = [
                'event_id' => trim((string) $this->header('X-Event-ID')),
                'idempotency_key' => trim((string) $this->header('Idempotency-Key')),
                'company_code' => trim((string) $this->header('X-Company-Code')),
            ];
            foreach ($checks as $field => $headerValue) {
                if ($headerValue === '' || ! hash_equals($headerValue, (string) $this->input($field))) {
                    $validator->errors()->add($field, "{$field} tidak sama dengan header integrasi.");
                }
            }
        }];
    }
}
