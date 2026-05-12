<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SystemHealthCommand extends Command
{
    protected $signature   = 'orthobrain:system-health';
    protected $description = 'Run a quick pre-flight health check (DB, queue, storage, mail, Gemini).';

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>OrthoBrain — System Health Check</>');
        $this->line('  <fg=gray>' . now()->format('Y-m-d H:i:s') . '</>');
        $this->newLine();

        $allOk = true;

        $allOk = $this->checkDatabase()  && $allOk;
        $allOk = $this->checkQueue()     && $allOk;
        $allOk = $this->checkStorage()   && $allOk;
        $allOk = $this->checkMail()      && $allOk;
        $allOk = $this->checkDiskSpace() && $allOk;
        $allOk = $this->checkGeminiApi() && $allOk;

        $this->newLine();

        if ($allOk) {
            $this->line('  <fg=green;options=bold>✓ All checks passed — system is healthy.</>');
        } else {
            $this->line('  <fg=yellow;options=bold>⚠ One or more checks need attention.</>');
        }

        $this->newLine();

        return $allOk ? self::SUCCESS : self::FAILURE;
    }

    // ── Individual checks ─────────────────────────────────────────

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            $db = config('database.connections.' . config('database.default') . '.database');
            return $this->ok('Database', "Connected ({$db})");
        } catch (\Throwable $e) {
            return $this->bad('Database', $e->getMessage());
        }
    }

    private function checkQueue(): bool
    {
        $connection = config('queue.default');

        // For the database driver, confirm the jobs table is accessible.
        if ($connection === 'database') {
            try {
                $pending = DB::table(config('queue.connections.database.table', 'jobs'))->count();
                return $this->ok('Queue', "Driver: database · {$pending} job(s) pending");
            } catch (\Throwable $e) {
                return $this->notice('Queue', "jobs table unreadable: {$e->getMessage()}");
            }
        }

        // Sync/null drivers need no worker — flag as info, not a failure.
        if (in_array($connection, ['sync', 'null'])) {
            return $this->notice('Queue', "Driver is '{$connection}' — no background worker");
        }

        return $this->ok('Queue', "Driver: {$connection}");
    }

    private function checkStorage(): bool
    {
        $path = storage_path('app');

        if (!is_dir($path)) {
            return $this->bad('Storage', "storage/app directory missing: {$path}");
        }

        if (!is_writable($path)) {
            return $this->bad('Storage', "storage/app is not writable");
        }

        // Confirm we can actually create a file (permissions alone don't guarantee it).
        $probe = $path . '/.health_probe_' . getmypid();
        try {
            file_put_contents($probe, '1');
            unlink($probe);
        } catch (\Throwable $e) {
            return $this->bad('Storage', "Write test failed: {$e->getMessage()}");
        }

        return $this->ok('Storage', "storage/app is writable");
    }

    private function checkMail(): bool
    {
        $mailer = config('mail.default', 'smtp');

        if (in_array($mailer, ['log', 'array'])) {
            return $this->notice('Mail', "Driver '{$mailer}' — no real delivery (OK for local dev)");
        }

        $host     = config("mail.mailers.{$mailer}.host");
        $port     = (int) config("mail.mailers.{$mailer}.port", 25);
        $username = (string) config("mail.mailers.{$mailer}.username", '');
        $password = (string) config("mail.mailers.{$mailer}.password", '');
        $from     = config('mail.from.address', '');

        if (empty($host)) {
            return $this->bad('Mail', "MAIL_HOST not configured for mailer '{$mailer}'");
        }

        $result = $this->smtpAuthCheck($host, $port, $username, $password);

        return match ($result['status']) {
            'ok'       => $this->ok('Mail', "SMTP authenticated · {$host}:{$port} · from: {$from}"),
            'unreach'  => $this->notice('Mail', "SMTP {$host}:{$port} unreachable — {$result['detail']}"),
            'authfail' => $this->bad('Mail', "SMTP credentials rejected by {$host}:{$port}"),
            default    => $this->notice('Mail', "SMTP {$host}:{$port} — {$result['detail']}"),
        };
    }

    private function smtpAuthCheck(string $host, int $port, string $username, string $password): array
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, 5);

        if ($socket === false) {
            return ['status' => 'unreach', 'detail' => $errstr];
        }

        stream_set_timeout($socket, 5);

        fgets($socket, 512);                                 // 220 banner
        fwrite($socket, "EHLO orthobrain.health\r\n");
        $ehlo = '';
        while ($line = fgets($socket, 512)) {
            $ehlo .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }

        if (str_contains($ehlo, 'STARTTLS')) {
            fwrite($socket, "STARTTLS\r\n");
            fgets($socket, 512);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return ['status' => 'unknown', 'detail' => 'TLS upgrade failed'];
            }
            fwrite($socket, "EHLO orthobrain.health\r\n");
            while ($line = fgets($socket, 512)) {
                if (isset($line[3]) && $line[3] === ' ') break;
            }
        }

        if (empty($username) || empty($password)) {
            fwrite($socket, "QUIT\r\n");
            fclose($socket);
            return ['status' => 'ok', 'detail' => 'connected (no credentials to verify)'];
        }

        fwrite($socket, "AUTH LOGIN\r\n");
        fgets($socket, 512);
        fwrite($socket, base64_encode($username) . "\r\n");
        fgets($socket, 512);
        fwrite($socket, base64_encode($password) . "\r\n");
        $authReply = (string) fgets($socket, 512);

        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        $code = (int) substr($authReply, 0, 3);

        if ($code === 235) {
            return ['status' => 'ok', 'detail' => 'authenticated'];
        }

        if (in_array($code, [535, 534, 538])) {
            return ['status' => 'authfail', 'detail' => trim($authReply)];
        }

        return ['status' => 'unknown', 'detail' => "Unexpected AUTH reply: {$authReply}"];
    }

    private function checkDiskSpace(): bool
    {
        $free  = disk_free_space('/');
        $total = disk_total_space('/');

        if ($free === false || $total === false || $total === 0.0) {
            return $this->notice('Disk', "Could not read disk stats");
        }

        $usedPct = (int) round((($total - $free) / $total) * 100);
        $freeGb  = round($free / 1024 ** 3, 1);
        $label   = "Used {$usedPct}% · {$freeGb} GB free";

        if ($usedPct >= 90) {
            return $this->bad('Disk', $label);
        }

        if ($usedPct >= 80) {
            return $this->notice('Disk', $label);
        }

        return $this->ok('Disk', $label);
    }

    private function checkGeminiApi(): bool
    {
        $apiKey = config('ai.providers.gemini.api_key');

        if (empty($apiKey)) {
            return $this->notice('Gemini API', 'GEMINI_API_KEY is not set');
        }

        $baseUrl = rtrim((string) config('ai.providers.gemini.base_url'), '/');
        $model   = config('ai.providers.gemini.model', 'gemini-2.0-flash');
        $url     = "{$baseUrl}/models/{$model}?key={$apiKey}";

        try {
            $ctx = stream_context_create([
                'http' => ['method' => 'GET', 'timeout' => 5, 'ignore_errors' => true],
                'ssl'  => ['verify_peer' => true, 'verify_peer_name' => true],
            ]);

            @file_get_contents($url, false, $ctx);
            $code = 0;
            if (!empty($http_response_header)) {
                preg_match('/HTTP\/\S+ (\d+)/', $http_response_header[0], $m);
                $code = (int) ($m[1] ?? 0);
            }

            if ($code === 200) {
                return $this->ok('Gemini API', "Reachable · model: {$model}");
            }
            if ($code === 401 || $code === 403) {
                return $this->bad('Gemini API', "Invalid API key (HTTP {$code})");
            }
            if ($code === 429) {
                return $this->notice('Gemini API', "Rate-limited (HTTP 429)");
            }
            if ($code >= 500) {
                return $this->notice('Gemini API', "Server error (HTTP {$code})");
            }

            return $this->bad('Gemini API', "Unexpected HTTP {$code}");
        } catch (\Throwable $e) {
            return $this->bad('Gemini API', "Unreachable: {$e->getMessage()}");
        }
    }

    // ── Output helpers (names avoid Command base-class conflicts) ──

    private function ok(string $name, string $message): bool
    {
        $pad = str_pad($name, 14);
        $this->line("  <fg=green;options=bold>✓</> <options=bold>{$pad}</> <fg=gray>{$message}</>");
        return true;
    }

    private function notice(string $name, string $message): bool
    {
        $pad = str_pad($name, 14);
        $this->line("  <fg=yellow;options=bold>⚠</> <options=bold>{$pad}</> <fg=yellow>{$message}</>");
        return true;  // warnings don't flip the exit code
    }

    private function bad(string $name, string $message): bool
    {
        $pad = str_pad($name, 14);
        $this->line("  <fg=red;options=bold>✗</> <options=bold>{$pad}</> <fg=red>{$message}</>");
        return false;
    }
}
