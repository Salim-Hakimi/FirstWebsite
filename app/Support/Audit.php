<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class Audit
{
    private const REDACTED_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public static function record(string $action, ?Model $model = null, array $oldValues = [], array $newValues = [], ?Request $request = null): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        $request ??= request();

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'model_type' => $model ? $model::class : null,
            'model_id' => $model?->getKey(),
            'old_values' => self::sanitize($oldValues),
            'new_values' => self::sanitize($newValues),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);
    }

    private static function sanitize(array $values): array
    {
        return Arr::except($values, self::REDACTED_KEYS);
    }
}
