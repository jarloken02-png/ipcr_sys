<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogPageNavigation
{
    protected array $ignoredPathPrefixes = [
        'build/',
        'images/',
        'storage/',
        'vendor/',
        '_debugbar/',
        '_ignition/',
        'livewire/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $response = $next($request);

        if (! $this->shouldLogRequest($request)) {
            return $response;
        }

        $method = strtoupper($request->method());
        $statusCode = $response->getStatusCode();
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $user = auth()->user();
        $userRole = $user?->getPrimaryRole() ?? 'unknown';
        $targetLabel = $this->describeRequestTarget($request);
        $verb = $this->verbForMethod($method);

        $description = sprintf('%s (%s) %s %s', ucfirst($userRole), $user?->name ?? 'Unknown User', $verb, $targetLabel);

        ActivityLogService::log(
            $this->actionForMethod($method),
            $description,
            null,
            [
                'request_status' => $statusCode,
                'request_duration_ms' => $durationMs,
                'request_channel' => $request->expectsJson() || $request->ajax() ? 'ajax' : 'page',
            ]
        );

        return $response;
    }

    private function shouldLogRequest(Request $request): bool
    {
        if (! auth()->check()) {
            return false;
        }

        if (in_array(strtoupper($request->method()), ['HEAD', 'OPTIONS'], true)) {
            return false;
        }

        $path = ltrim($request->path(), '/');
        foreach ($this->ignoredPathPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return false;
            }
        }

        return true;
    }

    private function actionForMethod(string $method): string
    {
        return match (strtoupper($method)) {
            'GET' => 'viewed',
            'POST' => 'submitted',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default => 'activity',
        };
    }

    private function verbForMethod(string $method): string
    {
        return match (strtoupper($method)) {
            'GET' => 'viewed',
            'POST' => 'submitted changes on',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted from',
            default => 'accessed',
        };
    }

    private function describeRequestTarget(Request $request): string
    {
        $routeName = (string) ($request->route()?->getName() ?? '');
        if ($routeName !== '') {
            $segments = explode('.', $routeName);
            $ignoredSegments = ['admin', 'faculty', 'dean', 'director', 'panel', 'index', 'show', 'store', 'update', 'destroy'];
            $segments = array_values(array_filter($segments, function ($segment) use ($ignoredSegments) {
                return ! in_array($segment, $ignoredSegments, true);
            }));

            if (! empty($segments)) {
                $text = implode(' ', array_map(function ($segment) {
                    return str_replace(['-', '_'], ' ', $segment);
                }, $segments));

                $text = preg_replace('/\s+/', ' ', trim($text));
                if ($text !== null && $text !== '') {
                    return ucfirst($text).' page';
                }
            }
        }

        $pathSegments = array_values(array_filter($request->segments(), function ($segment) {
            return ! in_array($segment, ['admin', 'panel', 'faculty', 'dean', 'director'], true);
        }));

        if (! empty($pathSegments)) {
            $text = implode(' ', array_map(function ($segment) {
                return str_replace(['-', '_'], ' ', $segment);
            }, $pathSegments));

            return ucfirst(trim($text));
        }

        return 'the system';
    }
}
