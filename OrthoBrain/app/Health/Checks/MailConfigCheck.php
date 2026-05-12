<?php

namespace App\Health\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class MailConfigCheck extends Check
{
    public function run(): Result
    {
        $mailer = config('mail.default', 'smtp');

        if (in_array($mailer, ['log', 'array'])) {
            return Result::make()->warning("Mail driver is '{$mailer}' (no real delivery)");
        }

        $host     = config("mail.mailers.{$mailer}.host");
        $port     = (int) config("mail.mailers.{$mailer}.port", 25);
        $username = config("mail.mailers.{$mailer}.username");
        $password = config("mail.mailers.{$mailer}.password");
        $from     = config('mail.from.address', '');

        if (empty($host)) {
            return Result::make()->failed("MAIL_HOST not configured for mailer '{$mailer}'");
        }

        // Attempt full SMTP handshake including AUTH LOGIN so wrong credentials
        // are caught rather than just verifying the port is open.
        try {
            $result = $this->smtpAuthCheck($host, $port, (string) $username, (string) $password);
        } catch (\Throwable $e) {
            return Result::make()->warning("SMTP check error: {$e->getMessage()}");
        }

        return match ($result['status']) {
            'ok'      => Result::make()->ok("SMTP authenticated · {$host}:{$port} · from: {$from}"),
            'unreach' => Result::make()->warning("SMTP {$host}:{$port} unreachable — check MAIL_HOST/PORT"),
            'authfail'=> Result::make()->failed("SMTP credentials rejected by {$host}:{$port}"),
            default   => Result::make()->warning("SMTP {$host}:{$port} — {$result['detail']}"),
        };
    }

    /**
     * Open a socket, do EHLO + optional STARTTLS, then AUTH LOGIN.
     * Returns ['status' => 'ok'|'unreach'|'authfail'|'unknown', 'detail' => string].
     */
    private function smtpAuthCheck(string $host, int $port, string $username, string $password): array
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, 5);

        if ($socket === false) {
            return ['status' => 'unreach', 'detail' => $errstr];
        }

        stream_set_timeout($socket, 5);

        $this->smtpRead($socket);                             // 220 banner
        $this->smtpSend($socket, "EHLO orthobrain.health");
        $ehlo = $this->smtpReadMulti($socket);               // 250 capabilities

        // Upgrade to TLS if the server advertises STARTTLS (e.g. Mailtrap port 2525).
        if (str_contains($ehlo, 'STARTTLS')) {
            $this->smtpSend($socket, "STARTTLS");
            $this->smtpRead($socket);                        // 220 Ready

            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return ['status' => 'unknown', 'detail' => 'TLS upgrade failed'];
            }

            $this->smtpSend($socket, "EHLO orthobrain.health");
            $this->smtpReadMulti($socket);                   // re-read capabilities after TLS
        }

        if (empty($username) || empty($password)) {
            $this->smtpSend($socket, "QUIT");
            fclose($socket);
            return ['status' => 'ok', 'detail' => 'connected (no credentials to verify)'];
        }

        // AUTH LOGIN
        $this->smtpSend($socket, "AUTH LOGIN");
        $this->smtpRead($socket);                            // 334 Username:
        $this->smtpSend($socket, base64_encode($username));
        $this->smtpRead($socket);                            // 334 Password:
        $this->smtpSend($socket, base64_encode($password));
        $authReply = $this->smtpRead($socket);               // 235 OK or 535 fail

        $this->smtpSend($socket, "QUIT");
        fclose($socket);

        $code = (int) substr($authReply, 0, 3);

        if ($code === 235) {
            return ['status' => 'ok', 'detail' => 'authenticated'];
        }

        if ($code === 535 || $code === 534 || $code === 538) {
            return ['status' => 'authfail', 'detail' => trim($authReply)];
        }

        return ['status' => 'unknown', 'detail' => "Unexpected AUTH reply: {$authReply}"];
    }

    private function smtpSend($socket, string $cmd): void
    {
        fwrite($socket, $cmd . "\r\n");
    }

    private function smtpRead($socket): string
    {
        return (string) fgets($socket, 512);
    }

    /** Read a potentially multi-line SMTP response (lines ending in `250-`). */
    private function smtpReadMulti($socket): string
    {
        $buffer = '';
        while ($line = fgets($socket, 512)) {
            $buffer .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break; // last line of the block has a space after the code
            }
        }
        return $buffer;
    }
}
