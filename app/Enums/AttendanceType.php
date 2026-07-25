<?php

namespace App\Enums;

enum AttendanceType: string
{
    case SelfIn = 'self_in';
    case SelfOut = 'self_out';
    case Session = 'session';

    public function label(): string
    {
        return match ($this) {
            self::SelfIn => 'Absensi Masuk',
            self::SelfOut => 'Absensi Pulang',
            self::Session => 'Absensi Sesi',
        };
    }
}
