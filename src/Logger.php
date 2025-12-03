<?php

namespace BinanceAPI;

class Logger
{
    public static function info(array $context): void
    {
        if (!Config::isDebug()) {
            return;
        }

        self::write($context, 'info');
    }

    public static function error(array $context): void
    {
        self::write($context, 'error');
    }

    private static function write(array $context, string $level): void
    {
        $payload = array_merge($context, [
            'level' => $level,
            'ts' => date('c')
        ]);

        $line = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $logFile = Config::get('APP_LOG_FILE');

        if ($logFile) {
            $dir = dirname($logFile);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND);
            return;
        }

        error_log($line);
    }
}
