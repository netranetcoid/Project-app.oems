<?php

namespace App\Services\Attendance;

use App\Models\EmployeeWorkLocationTrack;
use Illuminate\Support\Collection;

/**
 * Menyaring drift GPS tanpa menghilangkan perjalanan nyata.
 * Data mentah tidak pernah diubah atau dihapus.
 */
class WorkTrackingRouteFilter
{
    private const MAX_ACCURACY_METERS = 80.0;
    private const WINDOW_SECONDS = 30;
    private const MIN_CONFIRMED_MOVE_METERS = 50.0;
    private const MIN_ROUTE_STEP_METERS = 15.0;
    private const MAX_MOVE_SPEED_MPS = 55.0;

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
                    if ($previous) $distance += $this->distanceMeters($previous, $point);
                    $previous = $point;
                }
                return $distance;
            });
    }

    private function stableSession(Collection $session): Collection
    {
        $usable = $session->sortBy('captured_at')->filter(function (EmployeeWorkLocationTrack $point): bool {
            $accuracy = (float) $point->accuracy_meters;
            return $point->integrity_status !== 'blocked'
                && $accuracy > 0
                && $accuracy <= self::MAX_ACCURACY_METERS;
        })->values();

        if ($usable->isEmpty()) return collect();

        $origin = $usable->first()->captured_at->copy();
        $representatives = $usable->groupBy(function (EmployeeWorkLocationTrack $point) use ($origin): int {
            return intdiv(max(0, $origin->diffInSeconds($point->captured_at)), self::WINDOW_SECONDS);
        })->map(fn (Collection $window): EmployeeWorkLocationTrack => $this->medoid($window))->values();

        if ($representatives->count() < 2) return $representatives;

        $route = collect([$representatives->first()]);
        $anchor = $representatives->first();
        $previousSample = $representatives->first();
        $pending = collect();

        foreach ($representatives->slice(1) as $candidate) {
            // Kecepatan harus dibandingkan dengan sampel sebelumnya. Jika
            // dibandingkan dengan anchor lama setelah berhenti berjam-jam,
            // perjalanan nyata akan keliru dianggap terlalu lambat.
            $seconds = max(1, $previousSample->captured_at->diffInSeconds($candidate->captured_at));
            $stepDistance = $this->distanceMeters($previousSample, $candidate);
            $stepSpeed = $stepDistance / $seconds;
            $uncertainty = min(45.0, max(
                self::MIN_ROUTE_STEP_METERS,
                (((float) $previousSample->accuracy_meters + (float) $candidate->accuracy_meters) / 2) * 0.8,
            ));

            if ($stepSpeed > self::MAX_MOVE_SPEED_MPS) {
                $pending = collect();
                $previousSample = $candidate;
                continue;
            }

            if ($stepDistance >= $uncertainty) {
                $pending->push($candidate);
                $confirmedDistance = $this->distanceMeters($anchor, $candidate);

                // Dua jendela bergerak dan sedikitnya 50 meter diperlukan
                // agar drift acak saat perangkat diam tidak menjadi rute.
                if ($pending->count() >= 2 && $confirmedDistance >= self::MIN_CONFIRMED_MOVE_METERS) {
                    foreach ($pending as $confirmed) {
                        $last = $route->last();
                        if ($this->distanceMeters($last, $confirmed) >= self::MIN_ROUTE_STEP_METERS) {
                            $route->push($confirmed);
                        }
                    }
                    $anchor = $route->last();
                    $pending = collect();
                }
            } else {
                $pending = collect();
            }

            $previousSample = $candidate;
        }

        return $this->removeTriangleNoise($route)->values();
    }

    private function medoid(Collection $window): EmployeeWorkLocationTrack
    {
        $latitude = $this->median($window->pluck('latitude')->map(fn ($v) => (float) $v)->all());
        $longitude = $this->median($window->pluck('longitude')->map(fn ($v) => (float) $v)->all());
        return $window->sortBy(fn (EmployeeWorkLocationTrack $point): float =>
            $this->distanceCoordinates((float) $point->latitude, (float) $point->longitude, $latitude, $longitude)
        )->first();
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
            if ($this->distanceMeters($before, $current) > 150
                && $this->distanceMeters($current, $after) > 150
                && $this->distanceMeters($before, $after) < 80) continue;
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
