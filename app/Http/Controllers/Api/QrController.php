<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;

class QrController extends Controller
{
    public function __construct(private QrCodeService $qrService) {}

    public function regenerate(int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $token = $this->qrService->regenerate($user);

        return response()->json([
            'success' => true,
            'message' => 'QR Code berhasil diregenerasi.',
            'data' => [
                'qr_token' => $token,
                'qr_image' => $this->qrService->renderQrImage($token),
            ],
        ]);
    }
}
