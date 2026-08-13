<?php

namespace App\Services\Attendance;

use App\Models\EmployeeWorkLocationTrack;
use Illuminate\Support\Collection;

/**
 * Membentuk rute stabil dari sampel GPS mentah.
 *
 * Sampel mentah tidak dihapus. Tampilan HR memakai median per jendela waktu,
 * dead-zone saat diam, serta batas kecepatan agar GPS drift tidak menjadi km.
 */
class WorkTrackingRouteFilter
{
    private const MAX_ACCURACY_METERS = 50.0;
    private const WINDOW_SECONDS = 60;
    private const MIN_MOVE_METERS = 65.0;
    private const MIN_MOVE_SPEED_MPS = 0.75;
    private const MAX_MOVE_SPEED_MPS = 35.0;

    public function filter(Collection $tracks): Collection
    {
        return $tracks->sortBy('captured_at')
            ->groupBy(fn (EmployeeWorkLocationTrack $track): string => $this->sessionKey($track))
            ->flatMap(fn (Collection $session): Collection => $this->stableSession($session))
            ->sortBy('captured_at')->values();
    }

    public function distanceMetersFor(Collection $tracks): float
    {
        return $this->filter($tracks)
            ->groupBy(fn (EmployeeWorkLocationTrack $track): string => $this->sessionKey($track))
            ->sum(function (Collection $session): float {
                $distance = 0.0;
                $previous = null;
                foreach ($session as $point) {
                    if ($previous) {
                        $distance += $this->distanceMeters($previous, $point);
                    }
                    $previous = $point;
                }
                return $distance;
            });
    }

    private function stableSession(Collection $session): Collection
    {
        $usable = $session->sortBy('captured_at')->filter(fn (EmployeeWorkLocationTrack $point): bool =>
            $point->integrity_status !== 'blocked'
            && (float) $point->accuracy_meters > 0
            && (float) $point->accuracy_meters <= self::MAX_ACCURACY_METERS
        )->values();

        if ($usable->isEmpty()) {
            return collect();
        }

        $origin = $usable->first()->captured_at->copy();
        $representatives = $usable->groupBy(function (EmployeeWorkLocationTrack $point) use ($origin): int {
            return intdiv(max(0, $origin->diffInSeconds($point->captured_at)), self::WINDOW_SECONDS);
        })->map(fn (Collection $window): EmployeeWorkLocationTrack => $this->medoid($window))->values();

        $route = collect([$representatives->first()]);
        $anchor = $representatives->first();
        foreach ($representatives->slice(1) as $candidate) {
            $seconds = max(1, $anchor->captured_at->diffInSeconds($candidate->captured_at));
            $distance = $this->distanceMeters($anchor, $candidate);
            $uncertainty = min(100.0, max(
                self::MIN_MOVE_METERS,
                ((float) $anchor->accuracy_meters + (float) $candidate->accuracy_meters) * 1.5,
            ));
            $speed = $distance / $seconds;

            // Hanya perpindahan konsisten di luar lingkar ketidakpastian yang
            // menjadi rute. Drift lambat maupun loncatan cepat tidak dihitung.
            if ($distance < $uncertainty
                || $speed < self::MIN_MOVE_SPEED_MPS
                || $speed > self::MAX_MOVE_SPEED_MPS) {
                continue;
            }

            $route->push($candidate);
            $anchor = $candidate;
        }

        // Posisi akhir hanya ditambahkan bila benar-benar berpindah. Saat HP
        // diam, marker bertahan pada medoid stabil dan tidak membuat garis baru.
        return $this->removeTriangleNoise($route)->values();
    }

    private function medoid(Collection $window): EmployeeWorkLocationTrack
    {
        $latitude = $this->median($window->pluck('latitude')->map(fn ($v) => (float) $v)->all());
        $longitude = $this->median($window->pluck('longitude')->map(fn ($v) => (float) $v)->all());

        return $window->sortBy(function (EmployeeWorkLocationTrack $point) use ($latitude, $longitude): float {
            return $this->distanceCoordinates((float) $point->latitude, (float) $point->longitude, $latitude, $longitude);
        })->first();
    }

    private function removeTriangleNoise(Collection $points): Collection
    {
        if ($points->count() < 3) return $points;
        $items = $points->values()->all();
        $clean = [$items[0]];
        for ($i = 1; $i < count($items) - 1; $i++) {
            $before = $clean[array_key_last($clean)];
            $current = $items[$i];
            $after = $items[$i + 1];
            if ($this->distanceMeters($before, $current) > 100
                && $this->distanceMeters($current, $after) > 100
                && $this->distanceMeters($before, $after) < 80) {
                continue;
            }
            $clean[] = $current;
        }
        $clean[] = $items[array_key_last($items)];
        return collect($clean)->unique('id');
    }

    private function median(array $values): float
    {
        sort($values, SORT_NUMERIC);
        $count = count($values);
        $middle = intdiv($count, 2);
        return $count % 2 ? $values[$middle] : (($values[$middle - 1] + $values[$middle]) / 2);
    }

    private function sessionKey(EmployeeWorkLocationTrack $track): string
    {
        return implode(':', [$track->employee_id, $track->work_mode, $track->attendance_id ?: 0, $track->overtime_attendance_id ?: 0]);
    }

    private function distanceMeters(EmployeeWorkLocationTrack $a, EmployeeWorkLocationTrack $b): float
    {
        return $this->distanceCoordinates((float) $a->latitude, (float) $a->longitude, (float) $b->latitude, (float) $b->longitude);
    }

    private function distanceCoordinates(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6_371_000;
        $latDifference = deg2rad($lat2 - $lat1);
        $lonDifference = deg2rad($lon2 - $lon1);
        $value = sin($latDifference / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDifference / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($value), sqrt(max(0, 1 - $value)));
    }
}