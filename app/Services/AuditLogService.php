<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public static function log(string $action, ?string $modelType = null, ?int $modelId = null, $user = null, ?array $meta = null): void
    {
        AuditLog::create([
            'user_id' => $user?->id ?? (Auth::check() ? Auth::id() : null),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'meta' => $meta,
        ]);
    }
}
