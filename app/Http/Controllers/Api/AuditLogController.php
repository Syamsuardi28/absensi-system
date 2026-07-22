<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = AuditLog::with('user')
            ->when($request->action, fn ($q) => $q->where('action', $request->action))
            ->when($request->model_type, fn ($q) => $q->where('model_type', $request->model_type))
            ->latest()
            ->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
