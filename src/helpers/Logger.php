<?php

namespace Helpers;

final class Logger
{
    public const LEVEL_DEBUG   = 'debug';
    public const LEVEL_INFO    = 'info';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR   = 'error';

    private static string $dir = ROOT_PATH . '/storage/logs';

    public static function setDirectory(string $dir): void
    {
        self::$dir = rtrim($dir, '/\\');
    }

    public static function debug(string $msg, array $ctx = []): void
    {
        self::write(self::LEVEL_DEBUG, $msg, $ctx);
    }
    public static function info(string $msg, array $ctx = []): void
    {
        self::write(self::LEVEL_INFO, $msg, $ctx);
    }
    public static function warning(string $msg, array $ctx = []): void
    {
        self::write(self::LEVEL_WARNING, $msg, $ctx);
    }
    public static function error(string $msg, array $ctx = []): void
    {
        self::write(self::LEVEL_ERROR, $msg, $ctx);
    }

    public static function exception(\Throwable $e, array $ctx = []): void
    {
        $ctx['exception'] = [
            'class'   => get_class($e),
            'message' => $e->getMessage(),
            'code'    => $e->getCode(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => explode("\n", $e->getTraceAsString()),
        ];
        self::write(self::LEVEL_ERROR, 'Unhandled exception', $ctx);
    }

    private static function write(string $level, string $message, array $context): void
    {
        $date  = new \DateTimeImmutable('now');
        $file  = sprintf('%s/app-%s.log', self::$dir, $date->format('Y-m-d'));

        if (!is_dir(self::$dir)) {
            @mkdir(self::$dir, 0775, true);
        }

        $line = json_encode([
            'ts'      => $date->format('c'),
            'level'   => $level,
            'message' => $message,
            'context' => $context,
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
            'uri'     => $_SERVER['REQUEST_URI'] ?? null,
            'method'  => $_SERVER['REQUEST_METHOD'] ?? null,
            'session' => session_id() ?: null,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $fh = @fopen($file, 'ab');
        if ($fh) {
            @flock($fh, LOCK_EX);
            @fwrite($fh, $line . PHP_EOL);
            @flock($fh, LOCK_UN);
            @fclose($fh);
        }
    }

    /**
     * Log e-mailů do denních NDJSON souborů:
     *  - storage/logs/email/email_ok-YYYY-mm-dd.ndjson
     *  - storage/logs/email/email_nok-YYYY-mm-dd.ndjson
     */
    public static function logEmail(bool $ok, array $meta): void
    {
        $date = new \DateTimeImmutable('now');

        $dir = self::$dir . '/email';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $file = $dir . '/'
              . ($ok ? 'email_ok' : 'email_nok')
              . '-' . $date->format('Y-m-d')
              . '.ndjson';

        // připrav payload (z byte-velkých věcí necháme jen text)
        $payload = [
            'ts'          => $date->format('c'),
            'ok'          => $ok,
            'to'          => $meta['to'] ?? null,
            'cc'          => array_values(array_filter((array)($meta['cc']  ?? []))),
            'bcc'         => array_values(array_filter((array)($meta['bcc'] ?? []))),
            'subject'     => (string)($meta['subject'] ?? ''),
            'body'        => isset($meta['body']) ? (string)$meta['body'] : null,
            'attachments' => array_map(function ($a) {
                return [
                    'name' => $a['name'] ?? (isset($a['path']) ? basename((string)$a['path']) : null),
                    'path' => $a['path'] ?? null,
                ];
            }, (array)($meta['attachments'] ?? [])),
            'error'       => $meta['error'] ?? null,
            'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
            'uri'         => $_SERVER['REQUEST_URI'] ?? null,
            'session'     => session_id() ?: null,
        ];

        $line = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $fh = @fopen($file, 'ab');
        if ($fh) {
            @flock($fh, LOCK_EX);
            @fwrite($fh, $line . PHP_EOL);
            @flock($fh, LOCK_UN);
            @fclose($fh);
        }
    }
}
