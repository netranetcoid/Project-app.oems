<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Kontrak shift AppBill v1.0. Payload langsung legacy dinormalisasi ke
 * envelope yang sama; keduanya tetap wajib melalui HMAC yang sama.
 */
final class AppBillShiftUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->has('appbill.company');
    }

    protected function prepareForValidation(): void
    {
        if (! is_array($this->input('data'))) {
            $body = $this->all();
            unset($body['schema_version'], $body['event'], $body['event_id'], $body['idempotency_key'], $body['company_code'], $body['occurred_at'], $body['source']);
            $this->merge(['data' => $body]);
        }

        $this->merge([
            'schema_version' => $this->input('schema_version', '1.0'),
            'event' => $this->input('event', 'shift.upserted'),
            'event_id' => $this->input('event_id', $this->header('X-Event-ID')),
            'idempotency_key' => $this->input('idempotency_key', $this->header('Idempotency-Key')),
            'company_code' => $this->input('company_code', $this->header('X-Company-Code')),
            'occurred_at' => $this->input('occurred_at', $this->header('X-Timestamp')),
            'source' => $this->input('source', 'appbill'),
        ]);
    }

    public function rules(): array
    {
        $rfc3339 = 'regex:/^(?:\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?)(?:Z|[+-]\d{2}:\d{2})$/';

        return [
            'schema_version' => ['required', 'in:1.0'],
            'event' => ['required', Rule::in(['shift.created', 'shift.updated', 'shift.upserted'])],
            'event_id' => ['required', 'uuid'],
            'idempotency_key' => ['required', 'string', 'max:191'],
            'company_code' => ['required', 'string', 'max:100'],
            'occurred_at' => ['required', 'date', $rfc3339],
            'source' => ['required', 'in:appbill'],
            'data' => ['required', 'array'],
            'data.shift_code' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/'],
            'data.shift_name' => ['required', 'string', 'max:255'],
            'data.start_time' => ['required', 'date_format:H:i'],
            'data.end_time' => ['required', 'date_format:H:i'],
            'data.break_minutes' => ['nullable', 'integer', 'min:0', 'max:720'],
            'data.work_type' => ['nullable', Rule::in(['office', 'shift', 'flexible'])],
            'data.branch_code' => ['nullable', 'string', 'max:100'],
            'data.status' => ['nullable', Rule::in(['active', 'inactive'])],
            'data.grace_in_minutes' => ['nullable', 'integer', 'min:0', 'max:720'],
            'data.grace_out_minutes' => ['nullable', 'integer', 'min:0', 'max:720'],
            'data.late_tolerance_minutes' => ['nullable', 'integer', 'min:0', 'max:720'],
            'data.allow_overtime' => ['nullable', 'boolean'],
            'data.overtime_after_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'data.overtime_max_minutes' => ['nullable', 'integer', 'min:1', 'max:720'],
            'data.gps_required' => ['nullable', 'boolean'],
            'data.selfie_required' => ['nullable', 'boolean'],
            'data.photo_required' => ['nullable', 'boolean'],
            'data.notes' => ['nullable', 'string', 'max:2000'],
            'data.version' => ['required', 'integer', 'min:1'],
            'data.updated_at' => ['required', 'date', $rfc3339],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            foreach ([
                'event_id' => trim((string) $this->header('X-Event-ID')),
                'idempotency_key' => trim((string) $this->header('Idempotency-Key')),
                'company_code' => trim((string) $this->header('X-Company-Code')),
            ] as $field => $headerValue) {
                if ($headerValue === '' || ! hash_equals($headerValue, (string) $this->input($field))) {
                    $validator->errors()->add($field, "{$field} tidak sama dengan header integrasi.");
                }
            }
        }];
    }
}
