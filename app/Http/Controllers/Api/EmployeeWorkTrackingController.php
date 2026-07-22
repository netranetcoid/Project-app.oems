<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EmployeeWorkLocationTrack;
use App\Models\OvertimeAttendance;
use App\Services\Attendance\AttendanceProofService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Menerima titik perjalanan hanya dari sesi kerja aktif.
 *
 * Server adalah sumber kebenaran: akun Sanctum, email snapshot, timestamp,
 * konsistensi kecepatan, dan status mock GPS disimpan untuk audit HR. Data
 * dari aplikasi tidak pernah langsung dianggap sebagai kilometer yang sah.
 */
class EmployeeWorkTrackingController extends Controller
{
    public function __construct(private readonly AttendanceProofService $proofs) {}

    public function store(Request $request)
    {
        $user = $request->user();
        $employee = $this->proofs->employeeFor($user);
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'is_mocked' => ['nullable', 'boolean'],
            'installation_id' => ['nullable', 'string', 'max:120'],
            'captured_at' => ['required', 'date'],
        ]);

        $capturedAt = Carbon::parse($data['captured_at'])->utc();
        // Mencegah replay payload lama maupun jam perangkat yang terlalu jauh.
        if (abs(now('UTC')->diffInSeconds($capturedAt, false)) > 600) {
            throw ValidationException::withMessages([
                'captured_at' => ['Titik lokasi harus dikirim maksimal 10 menit dari waktu pengambilan.'],
            ]);
        }

        [$regular, $overtime, $mode, $parentId] = $this->activeSession($employee);
        if (! $regular && ! $overtime) {
            throw ValidationException::withMessages([
                'tracking' => ['Tracking hanya aktif setelah presensi masuk atau masuk lembur.'],
            ]);
        }

        // Hanya titik normal sebelumnya yang dipakai menghitung kilometer.
        // Titik palsu/berisiko tidak boleh menggeser basis perhitungan berikutnya.
        $recent = EmployeeWorkLocationTrack::query()
            ->where('employee_id', $employee->id)
            ->where('work_mode', $mode)
            ->where('integrity_status', 'accepted')
            ->where($mode === 'overtime' ? 'overtime_attendance_id' : 'attendance_id', $parentId)
            ->latest('captured_at')
            ->first();

        if ($recent && $recent->captured_at->diffInSeconds($capturedAt) < 60) {
            return response()->json(['data' => ['accepted' => false, 'reason' => 'too_soon']]);
        }

        $assignment = $this->proofs->assignment(
            $employee,
            now($employee->company?->timezone ?: 'Asia/Jakarta'),
        );
        $policy = $this->proofs->policy($employee, $assignment);
        [$integrityStatus, $riskScore, $riskFlags, $distance] = $this->integrityResult(
            $data,
            $recent,
            $capturedAt,
        );

        $track = EmployeeWorkLocationTrack::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'user_id' => $user->id,
            'account_email' => $user->email,
            'installation_id' => $data['installation_id'] ?? null,
            'attendance_id' => $mode === 'regular' ? $regular->id : $overtime->attendance_id,
            'overtime_attendance_id' => $mode === 'overtime' ? $overtime->id : null,
            'work_mode' => $mode,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'accuracy_meters' => $data['accuracy'] ?? null,
            'distance_from_previous_meters' => $distance,
            'is_mock_location' => (bool) ($data['is_mocked'] ?? false),
            'integrity_status' => $integrityStatus,
            'risk_score' => $riskScore,
            'risk_flags' => $riskFlags,
            'captured_at' => $capturedAt,
            'retention_until' => $this->proofs->retentionUntil($policy),
        ]);

        return response()->json(['data' => [
            'accepted' => $integrityStatus === 'accepted',
            'id' => $track->id,
            'mode' => $mode,
            'integrity_status' => $integrityStatus,
            'risk_flags' => $riskFlags,
        ]], $integrityStatus === 'blocked' ? 202 : 201);
    }

    /** API pegawai: hanya riwayat dirinya, tidak ada data atau peta rekan kerja. */
    public function history(Request $request)
    {
        $employee = $this->proofs->employeeFor($request->user());
        $timezone = $employee->company?->timezone ?: 'Asia/Jakarta';
        $tracks = EmployeeWorkLocationTrack::query()
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->where(fn ($query) => $query->whereNull('retention_until')->orWhere('retention_until', '>=', today()))
            ->latest('captured_at')
            ->limit(500)
            ->get();

        return response()->json([
            'items' => $tracks->map(fn (EmployeeWorkLocationTrack $point) => $this->mobilePoint($point)),
            'journeys' => $this->journeys($tracks, $timezone),
            'total_distance_km' => round($tracks->sum('distance_from_previous_meters') / 1000, 2),
        ]);
    }

    /** @return array{0:?Attendance,1:?OvertimeAttendance,2:string,3:int|null} */
    private function activeSession($employee): array
    {
        $regular = Attendance::query()
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->latest('id')
            ->first();
        $overtime = OvertimeAttendance::query()
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->latest('id')
            ->first();

        $mode = $overtime ? 'overtime' : 'regular';
        $parentId = $overtime?->id ?: $regular?->id;

        return [$regular, $overtime, $mode, $parentId];
    }

    /**
     * Memberi nilai risiko dari fakta yang dapat diperiksa server.
     * Fake GPS normal Android akan mengisi is_mocked; modifikasi aplikasi tetap
     * dapat dicurigai oleh replay, akurasi buruk, dan kecepatan tak realistis.
     * Play Integrity adalah lapisan lanjutan yang memerlukan konfigurasi Google.
     *
     * @return array{0:string,1:int,2:array<int,string>,3:float}
     */
    private function integrityResult(array $data, ?EmployeeWorkLocationTrack $recent, Carbon $capturedAt): array
    {
        $flags = [];
        $riskScore = 0;
        $distance = 0.0;

        if ((bool) ($data['is_mocked'] ?? false)) {
            return ['blocked', 100, ['mock_location_detected'], 0.0];
        }

        if (($data['accuracy'] ?? 0) > 150) {
            $flags[] = 'low_gps_accuracy';
            $riskScore += 30;
        }

        if ($recent) {
            $distance = $this->distanceMeters(
                (float) $recent->latitude,
                (float) $recent->longitude,
                (float) $data['latitude'],
                (float) $data['longitude'],
            );
            $seconds = max(1, $recent->captured_at->diffInSeconds($capturedAt));
            if (($distance / $seconds) > 50) { // > 180 km/jam
                $flags[] = 'impossible_speed';
                $riskScore += 70;
                $distance = 0.0;
            }
        }

        return [$riskScore > 0 ? 'review' : 'accepted', min(100, $riskScore), $flags, $distance];
    }

    private function mobilePoint(EmployeeWorkLocationTrack $point): array
    {
        return [
            'latitude' => (float) $point->latitude,
            'longitude' => (float) $point->longitude,
            'accuracy' => (float) $point->accuracy_meters,
            'captured_at' => $point->captured_at->toIso8601String(),
            'mode' => $point->work_mode,
            'distance_from_previous_meters' => (float) $point->distance_from_previous_meters,
            'integrity_status' => $point->integrity_status,
        ];
    }

    /** Ringkasan sesi untuk model riwayat perjalanan seperti referensi EV. */
    private function journeys(Collection $tracks, string $timezone): Collection
    {
        return $tracks->sortBy('captured_at')->groupBy(function (EmployeeWorkLocationTrack $track): string {
            return implode(':', [$track->work_mode, $track->attendance_id ?: 0, $track->overtime_attendance_id ?: 0]);
        })->map(function (Collection $session) use ($timezone): array {
            $first = $session->first();
            $last = $session->last();
            $started = $first->captured_at->copy()->setTimezone($timezone);
            $ended = $last->captured_at->copy()->setTimezone($timezone);

            return [
                'date' => $started->toDateString(),
                'mode' => $first->work_mode,
                'started_at' => $started->toIso8601String(),
                'ended_at' => $ended->toIso8601String(),
                'duration_seconds' => max(0, $first->captured_at->diffInSeconds($last->captured_at)),
                'distance_km' => round($session->sum('distance_from_previous_meters') / 1000, 2),
                'point_count' => $session->count(),
                'integrity_status' => $session->contains('integrity_status', 'blocked')
                    ? 'blocked'
                    : ($session->contains('integrity_status', 'review') ? 'review' : 'accepted'),
            ];
        })->sortByDesc('started_at')->values();
    }

    private function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6_371_000;
        $latDifference = deg2rad($lat2 - $lat1);
        $lonDifference = deg2rad($lon2 - $lon1);
        $a = sin($latDifference / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDifference / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
