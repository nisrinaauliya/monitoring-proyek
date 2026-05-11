<?php

namespace App\Exports;

class ReportColors
{
    // Warna header matrix & section label di Excel (sesuai standar PPT)
    const STATUS = [
        'Verifikasi CMD/Dinov'                => 'F97316', // oranye
        'Edit by User - Verifikasi CMD/Dinov' => 'FFC000', // kuning
        'Revisi User'                         => '2E74B5', // biru
        'Antrian Analisa BA IT'               => 'C9A227', // emas kecoklatan
        'Analisa BA IT'                       => 'C9A227',
        'Antrian Development'                 => 'C9A227',
        'Proses Development'                  => 'C9A227',
        'UAT'                                 => '2E74B5', // biru
        'Done (Live)'                         => '2A8000', // hijau
        'Rejected'                            => 'C00000', // merah
    ];

    const SECTION = [
        'Done (Live)'          => '375623',
        'UAT'                  => '2E74B5',
        'Proses IT'            => 'C9A227',
        'Revisi User'          => '2E74B5',
        'Verifikasi CMD/Dinov' => 'F97316',
        'Rejected'             => 'C00000',
    ];

    const HEADER = '1F4E79'; // biru tua untuk header utama

    public static function status(string $status): string
    {
        return self::STATUS[$status] ?? '6C757D';
    }

    public static function section(string $section): string
    {
        return self::SECTION[$section] ?? '6C757D';
    }
}