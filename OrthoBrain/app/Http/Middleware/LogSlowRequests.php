<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
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

    private int $queryCount = 0;
    private float $queryTimeMs = 0.0;
    private float $startTime = 0.0;

    public function handle(Request $request, Closure $next): Response
    {
        if (! App::environment('local')) {
            return $next($request);
        }

        $this->startTime = microtime(true);

        DB::listen(function (QueryExecuted $event): void {
            $this->queryCount++;
            $this->queryTimeMs += $event->time;
        });

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! App::environment('local') || $this->startTime === 0.0) {
            return;
        }

        $requestMs = (microtime(true) - $this->startTime) * 1000;

        $exceeds = $this->queryCount   > self::THRESHOLD_QUERY_COUNT
                || $this->queryTimeMs  > self::THRESHOLD_QUERY_MS
                || $requestMs          > self::THRESHOLD_REQUEST_MS;

        if (! $exceeds) {
            return;
        }

        $line = sprintf(
            "%s | %s | %s | %d | %.2f | %.2f\n",
            now()->toDateTimeString(),
            $request->fullUrl(),
            $request->method(),
            $this->queryCount,
            $this->queryTimeMs,
            $requestMs,
        );

        @file_put_contents(
            storage_path('logs/slow-requests.log'),
            $line,
            FILE_APPEND | LOCK_EX,
        );
    }
}
