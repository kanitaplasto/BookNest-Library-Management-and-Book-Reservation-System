<?php

/** Small file logger used by API and external-service code. */
class ApiLogger
{
    public static function info(string $message): void
    {
        self::write('info', $message);
    }

    public static function error(string $message): void
    {
        self::write('error', $message);
    }

    private static function write(string $level, string $message): void
    {
        $directory = __DIR__ . '/../logs';

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            error_log(strtoupper($level) . ': ' . $message);
            return;
        }

        $file = $directory . '/' . $level . '.log';
        $line = sprintf(
            "[%s] %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            str_replace(["\r", "\n"], ' ', $message)
        );

        if (file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
            error_log(rtrim($line));
        }
    }
}
