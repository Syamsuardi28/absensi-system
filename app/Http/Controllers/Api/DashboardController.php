<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request, DashboardService $dashboardService): JsonResponse
    {
        $data = $dashboardService->getSummary();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
