<?php

namespace App\Services\Attendance;

use App\Models\EmployeeWorkLocationTrack;
use Illuminate\Support\Collection;

/**
 * Menyaring GPS drift tanpa menghapus bukti mentah.
 *
 * Kilometer dan garis peta hanya memakai titik yang cukup akurat, bergerak
 * melewati lingkar ketidakpastian GPS, dan memiliki kecepatan masuk akal.
 */
class WorkTrackingRouteFilter
{
    private const MAX_ACCURACY_METERS = 60.0;
    private const MAX_SPEED_METERS_PER_SECOND = 35.0;
    private const MIN_NOISE_RADIUS_METERS = 35.0;

    public function filter(Collection $tracks): Collection
    {
        return $tracks
            ->sortBy('captured_at')
            ->groupBy(fn (EmployeeWorkLocationTrack $track): string => $this->sessionKey($track))
            ->flatMap(fn (Collection $session): Collection => $this->filterSession($session))
            ->sortBy('captured_at')
            ->values();
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

    private function filterSession(Collection $session): Collection
    {
        $result = collect();
        $anchor = null;

        foreach ($session->sortBy('captured_at') as $point) {
            if ($point->integrity_status === 'blocked' || ! $this->hasUsableAccuracy($point)) {
                continue;
            }

            if (! $anchor) {
                $result->push($point);
                $anchor = $point;
                continue;
            }

            $seconds = max(1, $anchor->captured_at->diffInSeconds($point->captured_at));
            $distance = $this->distanceMeters($anchor, $point);
            $noiseRadius = max(
                self::MIN_NOISE_RADIUS_METERS,
                min(90.0, (float) $anchor->accuracy_meters + (float) $point->accuracy_meters),
            );

            // Diam di satu tempat: sampel bergeser karena ketidakpastian GPS.
            if ($distance <= $noiseRadius) {
                continue;
            }

            // Lompatan GPS/fake route yang tidak mungkin dicapai pada intervalnya.
            if (($distance / $seconds) > self::MAX_SPEED_METERS_PER_SECOND) {
                continue;
            }

            $result->push($point);
            $anchor = $point;
        }

        return $this->removeReturnSpikes($result);
    }

    private function removeReturnSpikes(Collection $points): Collection
    {
        if ($points->count() < 3) {
            return $points->values();
        }

        $items = $points->values()->all();
        $clean = [$items[0]];
        for ($index = 1; $index < count($items) - 1; $index++) {
            $before = $clean[array_key_last($clean)];
            $current = $items[$index];
            $after = $items[$index + 1];
            $out = $this->distanceMeters($before, $current);
            $back = $this->distanceMeters($current, $after);
            $net = $this->distanceMeters($before, $after);

            // Pola A -> titik jauh -> kembali dekat A adalah spike, bukan perjalanan.
            if ($out > 100 && $back > 100 && $net < 70) {
                continue;
            }
            $clean[] = $current;
        }
        $clean[] = $items[array_key_last($items)];

        return collect($clean)->unique('id')->values();
    }

    private function hasUsableAccuracy(EmployeeWorkLocationTrack $track): bool
    {
        $accuracy = (float) $track->accuracy_meters;

        return $accuracy > 0 && $accuracy <= self::MAX_ACCURACY_METERS;
    }

    private function sessionKey(EmployeeWorkLocationTrack $track): string
    {
        return implode(':', [
            $track->employee_id,
            $track->work_mode,
            $track->attendance_id ?: 0,
            $track->overtime_attendance_id ?: 0,
        ]);
    }

    private function distanceMeters(EmployeeWorkLocationTrack $a, EmployeeWorkLocationTrack $b): float
    {
        $earthRadius = 6_371_000;
        $latDifference = deg2rad((float) $b->latitude - (float) $a->latitude);
        $lonDifference = deg2rad((float) $b->longitude - (float) $a->longitude);
        $value = sin($latDifference / 2) ** 2
            + cos(deg2rad((float) $a->latitude))
            * cos(deg2rad((float) $b->latitude))
            * sin($lonDifference / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($value), sqrt(max(0, 1 - $value)));
    }
}