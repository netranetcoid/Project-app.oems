<?php

namespace App\Support;

/**
 * Mengubah format nominal Indonesia maupun format database menjadi bilangan
 * rupiah utuh. Tidak memakai float agar nilai gaji besar tidak berubah.
 */
final class RupiahInput
{
    public static function integer(mixed $value): ?string
    {
        $text = trim((string) $value);
        if ($text === '') return null;

        // 3,000,000.00 -> 3000000
        if (preg_match('/^\d{1,3}(?:,\d{3})+(?:\.\d{1,2})?$/', $text)) {
            return str_replace(',', '', preg_replace('/\.\d{1,2}$/', '', $text));
        }
        // 3.000.000,00 -> 3000000
        if (preg_match('/^\d{1,3}(?:\.\d{3})+(?:,\d{1,2})?$/', $text)) {
            return str_replace('.', '', preg_replace('/,\d{1,2}$/', '', $text));
        }
        // 3000000.00 atau 3000000,00 -> 3000000
        if (preg_match('/^(\d+)[.,]\d{1,2}$/', $text, $matches)) return $matches[1];

        $digits = preg_replace('/\D/', '', $text);
        return $digits === '' ? null : $digits;
    }
}