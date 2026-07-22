<?php

namespace App\Enums;

enum AttendanceType: string
{
    case Self = 'self';
    case Session = 'session';

    public function label(): string
    {
        return match ($this) {
            self::Self => 'Absensi Mandiri',
            self::Session => 'Absensi Sesi',
        };
    }
}
