<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityLogService
{
    /**
     * Keys that should always be masked in logged request context.
     * We intentionally include common password/token/code keys.
     */
    private const SENSITIVE_KEY_FRAGMENTS = [
        'password',
        'token',
        'secret',
        'authorization',
        'code',
        'otp',
        'pin',
        'remember',
    ];

    private const MAX_STRING_LENGTH = 300;
    private const MAX_ARRAY_ITEMS = 40;

    /**
     * Record an activity log entry.
     *
     * @param string      $action      Short verb: login, created, updated, deleted …
     * @param string      $description Human-readable sentence
     * @param Model|null  $subject     The affected Eloquent model (optional)
     * @param array|null  $properties  Extra context data (optional)
     */
    public static function log(
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $properties = null
    ): ActivityLog {
        $request = app()->bound('request') ? request() : null;
        $requestContext = self::buildDefaultRequestContext($request instanceof Request ? $request : null);

        $customProperties = self::sanitizeContext($properties ?? []);
        $mergedProperties = $requestContext;
        if (! empty($customProperties)) {
            $mergedProperties = array_replace_recursive($requestContext, $customProperties);
        }

        return ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'description'  => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'properties'   => empty($mergedProperties) ? null : $mergedProperties,
            'ip_address'   => $request instanceof Request ? $request->ip() : null,
            'created_at'   => now(),
        ]);
    }

    private static function buildDefaultRequestContext(?Request $request): array
    {
        if (! $request) {
            return [];
        }

        $route = $request->route();

        return self::sanitizeContext([
            'method' => strtoupper($request->method()),
            'url' => $request->fullUrl(),
            'path' => '/'.ltrim($request->path(), '/'),
            'route_name' => $route?->getName(),
            'route_action' => $route?->getActionName(),
            'route_parameters' => $route ? $route->parameters() : [],
            'query' => $request->query(),
            'payload' => $request->except(['_token', '_method']),
            'user_agent' => (string) $request->userAgent(),
            'referer' => (string) $request->headers->get('referer'),
        ]);
    }

    private static function sanitizeContext(array $context): array
    {
        $clean = [];

        foreach ($context as $key => $value) {
            if (self::isSensitiveKey((string) $key)) {
                $clean[$key] = '[REDACTED]';
                continue;
            }

            $clean[$key] = self::sanitizeValue($value, 0);
        }

        return $clean;
    }

    private static function sanitizeValue(mixed $value, int $depth): mixed
    {
        if ($depth > 4) {
            return '[MAX_DEPTH_REACHED]';
        }

        if ($value instanceof UploadedFile) {
            return [
                'file_name' => $value->getClientOriginalName(),
                'mime_type' => $value->getClientMimeType(),
                'size' => $value->getSize(),
            ];
        }

        if ($value instanceof Model) {
            return [
                'model' => class_basename($value),
                'id' => $value->getKey(),
            ];
        }

        if (is_array($value)) {
            $items = [];
            $index = 0;

            foreach ($value as $k => $v) {
                if ($index >= self::MAX_ARRAY_ITEMS) {
                    $items['__truncated__'] = 'Additional items omitted for brevity.';
                    break;
                }

                if (self::isSensitiveKey((string) $k)) {
                    $items[$k] = '[REDACTED]';
                } else {
                    $items[$k] = self::sanitizeValue($v, $depth + 1);
                }

                $index++;
            }

            return $items;
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = (string) $value;
            } else {
                return '[OBJECT:'.class_basename($value).']';
            }
        }

        if (is_string($value)) {
            $value = trim($value);

            if (mb_strlen($value) > self::MAX_STRING_LENGTH) {
                return mb_substr($value, 0, self::MAX_STRING_LENGTH)
                    .'...[TRUNCATED '.mb_strlen($value).' CHARS]';
            }

            return $value;
        }

        return $value;
    }

    private static function isSensitiveKey(string $key): bool
    {
        $normalized = Str::lower($key);

        foreach (self::SENSITIVE_KEY_FRAGMENTS as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return true;
            }
        }

        return false;
    }
}
