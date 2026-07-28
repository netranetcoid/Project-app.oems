<?php

namespace App\Support;

/** Normalisasi nilai uang dari UI Indonesia tanpa menggandakan nol desimal DB. */
final class RupiahInput
{
    public static function integer(mixed $value): ?string
    {
        $text = trim((string) $value);
        if ($text === '') return null;

        // Nilai dari database/input hidden: 1500000.00 -> 1500000.
        if (preg_match('/^\\d+\\.\\d{1,2}$/', $text)) return explode('.', $text, 2)[0];

        // Nilai Indonesia: 1.500.000 / Rp 1.500.000 -> 1500000.
        $digits = preg_replace('/\\D/', '', $text);
        return $digits === '' ? null : $digits;
    }
}