<?php

return [
    /*
    | Audit "critical" menyimpan tindakan yang mempunyai dampak legal,
    | finansial, akses, dan integrasi. "all" hanya untuk investigasi
    | singkat. "off" tersedia untuk keadaan darurat, tetapi tidak dianjurkan.
    */
    'audit_mode' => env('AUDIT_MODE', 'critical'),

    // Audit minimum disimpan 90 hari dan dapat disesuaikan dari .env VPS.
    'audit_retention_days' => max(30, (int) env('AUDIT_RETENTION_DAYS', 90)),

    /*
    | Event integrasi yang SUKSES dan bersifat operasional dapat dipangkas.
    | Event gagal/dead dan event payroll tidak dihapus otomatis supaya jejak
    | rekonsiliasi keuangan tetap tersedia.
    */
    'integration_retention_days' => max(30, (int) env('INTEGRATION_LOG_RETENTION_DAYS', 90)),
];
