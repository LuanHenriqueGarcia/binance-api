<?php

namespace BinanceAPI;

class Cache
{
    private string $dir;

    public function __construct(?string $dir = null)
    {
        $this->dir = $dir ?? (__DIR__ . '/../storage/cache');
        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0777, true);
        }
    }

    public function get(string $key, int $ttlSeconds): ?array
    {
        $file = $this->path($key);
        if (!file_exists($file)) {
            return null;
        }

        if (filemtime($file) + $ttlSeconds < time()) {
            @unlink($file);
            return null;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : null;
    }

    public function set(string $key, array $value): void
    {
        $file = $this->path($key);
        file_put_contents($file, json_encode($value));
    }

    private function path(string $key): string
    {
        return $this->dir . '/' . md5($key) . '.json';
    }
}
