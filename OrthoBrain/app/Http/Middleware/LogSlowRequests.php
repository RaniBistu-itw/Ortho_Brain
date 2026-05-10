<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Local-only slow-request logger. Complements Telescope by writing a
 * grep-able single-line entry to storage/logs/slow-requests.log whenever
 * a request crosses any of the QUERY_*, TIME_* thresholds below.
 */
class LogSlowRequests
{
    private const THRESHOLD_QUERY_COUNT = 20;
    private const THRESHOLD_QUERY_MS    = 500;
    private const THRESHOLD_REQUEST_MS  = 1500;

    public function handle(Request $request, Closure $next): Response
    {
        if (! App::environment('local')) {
            return $next($request);
        }

        $queryCount   = 0;
        $queryTimeMs  = 0.0;
        $startTime    = microtime(true);

        // Inline counters captured by reference — must live inside handle()
        // because Laravel resolves a NEW middleware instance for terminate(),
        // which would discard any $this->* state set here.
        DB::listen(function (QueryExecuted $event) use (&$queryCount, &$queryTimeMs): void {
            $queryCount++;
            $queryTimeMs += $event->time;
        });

        $response = $next($request);

        $requestMs = (microtime(true) - $startTime) * 1000;

        $exceeds = $queryCount  > self::THRESHOLD_QUERY_COUNT
                || $queryTimeMs > self::THRESHOLD_QUERY_MS
                || $requestMs   > self::THRESHOLD_REQUEST_MS;

        if ($exceeds) {
            $this->log($request, $response, $queryCount, $queryTimeMs, $requestMs);
        }

        return $response;
    }

    private function log(
        Request $request,
        Response $response,
        int $queryCount,
        float $queryTimeMs,
        float $requestMs,
    ): void {
        $line = sprintf(
            "%s | %s %s | queries=%d | query_time=%.2fms | request_time=%.2fms\n",
            now()->toDateTimeString(),
            $request->method(),
            $request->fullUrl(),
            $queryCount,
            $queryTimeMs,
            $requestMs,
        );

        @file_put_contents(
            storage_path('logs/slow-requests.log'),
            $line,
            FILE_APPEND | LOCK_EX,
        );

        // Mirrored into Telescope's Logs tab via the LogWatcher.
        Log::warning('Slow request', [
            'url'         => $request->fullUrl(),
            'method'      => $request->method(),
            'status'      => $response->getStatusCode(),
            'query_count' => $queryCount,
            'query_ms'    => round($queryTimeMs, 2),
            'request_ms'  => round($requestMs, 2),
        ]);
    }
}
