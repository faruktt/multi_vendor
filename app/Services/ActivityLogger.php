<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityLogger
{
    /** Attributes to exclude from diff logging */
    protected static array $ignoredAttributes = [
        'password',
        'remember_token',
        'updated_at',
        'created_at',
        'deleted_at',
        'email_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Log an action on an Eloquent model
     */
    public static function log(string $action, Model $model, ?string $description = null, array $extraProperties = []): ?ActivityLog
    {
        try {
            $user     = auth()->user();
            $userId   = $user?->id;
            $vendorId = $user?->vendor_id ?? ($model->vendor_id ?? null);
            $module   = class_basename($model);
            $title    = static::resolveTitle($model);

            $properties = $extraProperties;

            if ($action === 'updated') {
                $changes = static::getChangesDiff($model);
                if (empty($changes['new'])) {
                    // No tracked fields actually changed (e.g. only updated_at changed)
                    return null;
                }
                $properties = array_merge($properties, $changes);
                $fieldNames = implode(', ', array_keys($changes['new']));
                $description = $description ?? "Updated {$module} '{$title}' ({$fieldNames})";
            } elseif ($action === 'created') {
                $description = $description ?? "Created {$module} '{$title}'";
                $properties['attributes'] = static::filterAttributes($model->getAttributes());
            } elseif ($action === 'deleted') {
                $description = $description ?? "Deleted {$module} '{$title}'";
                $properties['old'] = static::filterAttributes($model->getOriginal());
            }

            return ActivityLog::create([
                'user_id'       => $userId,
                'vendor_id'     => $vendorId,
                'action'        => $action,
                'module'        => $module,
                'subject_type'  => get_class($model),
                'subject_id'    => $model->getKey(),
                'subject_title' => Str::limit($title, 190),
                'description'   => $description,
                'properties'    => empty($properties) ? null : $properties,
                'ip_address'    => request()?->ip(),
                'user_agent'    => request()?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to log activity: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log a custom action without a direct Eloquent event
     */
    public static function logCustom(
        string $action,
        string $module,
        string $description,
        ?int $userId = null,
        ?int $vendorId = null,
        array $properties = [],
        ?string $subjectTitle = null,
        ?string $subjectType = null,
        ?int $subjectId = null
    ): ?ActivityLog {
        try {
            $user = auth()->user();
            return ActivityLog::create([
                'user_id'       => $userId ?? $user?->id,
                'vendor_id'     => $vendorId ?? $user?->vendor_id,
                'action'        => $action,
                'module'        => $module,
                'subject_type'  => $subjectType,
                'subject_id'    => $subjectId,
                'subject_title' => $subjectTitle ? Str::limit($subjectTitle, 190) : null,
                'description'   => $description,
                'properties'    => empty($properties) ? null : $properties,
                'ip_address'    => request()?->ip(),
                'user_agent'    => request()?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to log custom activity: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log user authentication event
     */
    public static function logLogin($user, string $description = 'Logged into system'): ?ActivityLog
    {
        return static::logCustom(
            action: 'login',
            module: 'Auth',
            description: $user->name . ' (' . ($user->roles->first()?->name ?? 'User') . ') ' . $description,
            userId: $user->id,
            vendorId: $user->vendor_id,
            subjectTitle: $user->name,
            subjectType: get_class($user),
            subjectId: $user->id
        );
    }

    /**
     * Extract old vs new diff for an updated model
     */
    protected static function getChangesDiff(Model $model): array
    {
        $dirty = $model->getDirty();
        $old   = [];
        $new   = [];

        foreach ($dirty as $key => $value) {
            if (in_array($key, static::$ignoredAttributes, true)) {
                continue;
            }

            $original = $model->getOriginal($key);

            // Skip if both are virtually equivalent
            if ($original == $value) {
                continue;
            }

            $old[$key] = static::formatValue($original);
            $new[$key] = static::formatValue($value);
        }

        return [
            'old' => $old,
            'new' => $new,
        ];
    }

    /**
     * Filter sensitive and ignored attributes from snapshots
     */
    protected static function filterAttributes(array $attributes): array
    {
        $filtered = [];
        foreach ($attributes as $key => $val) {
            if (!in_array($key, static::$ignoredAttributes, true)) {
                $filtered[$key] = static::formatValue($val);
            }
        }
        return $filtered;
    }

    /**
     * Format complex values (arrays, objects) for clean JSON storage
     */
    protected static function formatValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return $value;
    }

    /**
     * Resolve a human-friendly title for the model
     */
    public static function resolveTitle(Model $model): string
    {
        return (string) (
            $model->name
            ?? $model->title
            ?? $model->campaign_name
            ?? $model->invoice_no
            ?? $model->code
            ?? $model->email
            ?? $model->phone
            ?? ('#' . $model->getKey())
        );
    }
}
