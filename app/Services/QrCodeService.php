<?php

namespace App\Services;

use App\Models\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Str;

class QrCodeService
{
    public function generate(User $user): string
    {
        $token = Str::uuid()->toString();
        $user->update(['qr_token' => $token]);

        return $token;
    }

    public function regenerate(User $user): string
    {
        return $this->generate($user);
    }

    public function validate(string $token): ?User
    {
        return User::where('qr_token', $token)
            ->where('status', 'active')
            ->first();
    }

    public function renderQrImage(string $token): string
    {
        $result = Builder::create()
            ->writer(new PngWriter)
            ->data($token)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->build();

        return $result->getDataUri();
    }
}
