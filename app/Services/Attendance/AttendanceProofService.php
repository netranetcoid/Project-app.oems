<?php

namespace App\Services\Attendance;

use App\Models\AttendanceShiftAssignment;
use App\Models\AttendanceLocationPolicy;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Server-side attendance policy. Mobile coordinates are only evidence; the
 * server decides geofence, shift, duplicate check, and retention deadline.
 */
class AttendanceProofService
{
    public const OFFICE_LATITUDE = -6.612755088971767;
    public const OFFICE_LONGITUDE = 106.75548743646192;
    public const DEFAULT_RADIUS_METERS = 150;
    public const DEFAULT_RETENTION_DAYS = 60;

    public function employeeFor(User $user): Employee
    {
        $employee = $user->employee()->with(['branch', 'company', 'division'])->first();
        if (!$employee || (int) $employee->company_id !== (int) $user->company_id) {
            throw ValidationException::withMessages([
                'employee' => ['Akun belum ditautkan ke master karyawan aktif.'],
            ]);
        }

        if ($employee->work_status !== 'active') {
            throw ValidationException::withMessages([
                'employee' => ['Status karyawan tidak aktif untuk presensi.'],
            ]);
        }

        return $employee;
    }

    public function assignment(Employee $employee, Carbon $date): ?AttendanceShiftAssignment
    {
        return AttendanceShiftAssignment::query()
            ->with(['shift', 'branch'])
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $date->toDateString())
            ->where(function ($query) use ($date): void {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $date->toDateString());
            })
            ->latest('start_date')
            ->first();
    }

    public function policy(Employee $employee, ?AttendanceShiftAssignment $assignment): array
    {
        $company = $employee->company;
        $settings = is_array($company?->settings) ? $company->settings : [];
        $branch = $assignment?->branch ?: $employee->branch;

        /*
         * Location priority is deliberate: a division exception (for example
         * field technicians) must beat its branch, then the branch/site beats
         * PT OSM's main office. The legacy branch/company values remain a
         * backwards-compatible fallback until a Developer creates policies.
         */
        $location = $this->locationPolicy($employee, $branch);
        $mode = $location?->mode ?? 'geofence';
        $latitude = $location?->latitude ?? $branch?->latitude ?? ($settings['office_latitude'] ?? self::OFFICE_LATITUDE);
        $longitude = $location?->longitude ?? $branch?->longitude ?? ($settings['office_longitude'] ?? self::OFFICE_LONGITUDE);
        $radius = $location?->radius_meter ?? $branch?->attendance_radius_meter
            ?? $company?->attendance_radius_meter ?? self::DEFAULT_RADIUS_METERS;

        return [
            'latitude' => (float) $latitude,
            'longitude' => (float) $longitude,
            'radius' => max(1, (int) $radius),
            'geofence_required' => $mode === 'geofence',
            'location_mode' => $mode,
            'location_name' => $location?->name ?? $branch?->name ?? $company?->name ?? 'Kantor PT OSM',
            'gps_required' => (bool) ($assignment?->shift?->gps_required ?? $company?->attendance_gps_required ?? true),
            'selfie_required' => (bool) ($assignment?->shift?->selfie_required ?? $settings['attendance_selfie_required'] ?? true),
            'retention_days' => max(1, (int) ($settings['attendance_retention_days'] ?? self::DEFAULT_RETENTION_DAYS)),
        ];
    }

    public function distanceMeters(float $latitude, float $longitude, float $targetLatitude, float $targetLongitude): float
    {
        $earthRadius = 6371000.0;
        $latDelta = deg2rad($targetLatitude - $latitude);
        $lonDelta = deg2rad($targetLongitude - $longitude);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad($targetLatitude)) * sin($lonDelta / 2) ** 2;

        return 2 * $earthRadius * asin(min(1, sqrt($a)));
    }

    public function assertGeofence(array $input, array $policy): float
    {
        $latitude = (float) $input['latitude'];
        $longitude = (float) $input['longitude'];
        $accuracy = (float) ($input['accuracy'] ?? 0);

        if ($accuracy > 100) {
            throw ValidationException::withMessages([
                'accuracy' => ['Akurasi GPS terlalu rendah; ulangi di area terbuka.'],
            ]);
        }

        if (!$policy['gps_required'] || !$policy['geofence_required']) {
            return 0.0;
        }

        $distance = $this->distanceMeters(
            $latitude,
            $longitude,
            $policy['latitude'],
            $policy['longitude']
        );

        if ($distance > $policy['radius']) {
            throw ValidationException::withMessages([
                'latitude' => [sprintf('Di luar geofence kantor (%.0f m, batas %d m).', $distance, $policy['radius'])],
            ]);
        }

        return $distance;
    }

    public function retentionUntil(array $policy): string
    {
        return now()->addDays($policy['retention_days'])->toDateString();
    }

    /** Finds the single highest-priority active policy for an employee. */
    private function locationPolicy(Employee $employee, $branch): ?AttendanceLocationPolicy
    {
        $base = AttendanceLocationPolicy::query()->forCompany((int) $employee->company_id)->active();

        if ($employee->division_id) {
            $division = (clone $base)->where('scope_type', 'division')
                ->where('scope_id', $employee->division_id)->first();
            if ($division) {
                return $division;
            }
        }

        if ($branch?->id) {
            $site = (clone $base)->where('scope_type', 'branch')->where('scope_id', $branch->id)->first();
            if ($site) {
                return $site;
            }
        }

        return (clone $base)->where('scope_type', 'company')->whereNull('scope_id')->first();
    }
}
