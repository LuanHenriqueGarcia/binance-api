<?php

use BinanceAPI\FileCache;
use BinanceAPI\Config;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
    private string $cacheDir;
    private FileCache $cache;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/binance_test_cache_' . uniqid();
        Config::fake(['STORAGE_PATH' => sys_get_temp_dir()]);
        $this->cache = new FileCache($this->cacheDir);
    }

    protected function tearDown(): void
    {
        // Clean up test cache directory
        $this->cache->clear();
        if (is_dir($this->cacheDir)) {
            @rmdir($this->cacheDir);
        }
    }

    public function testSetAndGet(): void
    {
        $data = ['symbol' => 'BTCUSDT', 'price' => '50000'];

        $this->cache->set('test_key', $data);
        $result = $this->cache->get('test_key', 3600);

        $this->assertSame($data, $result);
    }

    public function testGetNonExistentKey(): void
    {
        $result = $this->cache->get('non_existent', 3600);

        $this->assertNull($result);
    }

    public function testGetExpiredKey(): void
    {
        $data = ['test' => 'data'];
        $this->cache->set('expired_key', $data);

        // Touch the file to make it old
        $file = $this->cacheDir . '/' . md5('expired_key') . '.json';
        touch($file, time() - 7200); // 2 hours ago

        $result = $this->cache->get('expired_key', 3600); // 1 hour TTL

        $this->assertNull($result);
    }

    public function testDelete(): void
    {
        $data = ['test' => 'data'];
        $this->cache->set('to_delete', $data);

        $this->assertNotNull($this->cache->get('to_delete', 3600));

        $this->cache->delete('to_delete');

        $this->assertNull($this->cache->get('to_delete', 3600));
    }

    public function testDeleteNonExistent(): void
    {
        // Should not throw
        $this->cache->delete('non_existent_key');
        $this->assertTrue(true);
    }

    public function testClear(): void
    {
        $this->cache->set('key1', ['data' => 1]);
        $this->cache->set('key2', ['data' => 2]);
        $this->cache->set('key3', ['data' => 3]);

        $this->assertNotNull($this->cache->get('key1', 3600));
        $this->assertNotNull($this->cache->get('key2', 3600));
        $this->assertNotNull($this->cache->get('key3', 3600));

        $this->cache->clear();

        $this->assertNull($this->cache->get('key1', 3600));
        $this->assertNull($this->cache->get('key2', 3600));
        $this->assertNull($this->cache->get('key3', 3600));
    }

    public function testCacheCreatesDirectory(): void
    {
        $newDir = sys_get_temp_dir() . '/binance_new_cache_' . uniqid();

        $this->assertFalse(is_dir($newDir));

        $cache = new FileCache($newDir);
        $cache->set('test', ['data' => 'value']);

        $this->assertTrue(is_dir($newDir));

        // Cleanup
        $cache->clear();
        @rmdir($newDir);
    }

    public function testCacheStoresJsonPrettyPrint(): void
    {
        $data = ['symbol' => 'BTCUSDT', 'price' => '50000'];
        $this->cache->set('pretty_test', $data);

        $file = $this->cacheDir . '/' . md5('pretty_test') . '.json';
        $content = file_get_contents($file);

        $this->assertStringContainsString("\n", $content); // Pretty printed has newlines
    }

    public function testGetWithValidTTL(): void
    {
        $data = ['fresh' => 'data'];
        $this->cache->set('fresh_key', $data);

        $result = $this->cache->get('fresh_key', 3600);

        $this->assertSame($data, $result);
    }

    public function testCacheWithNestedData(): void
    {
        $data = [
            'user' => [
                'name' => 'Test',
                'settings' => [
                    'theme' => 'dark',
                    'notifications' => true
                ]
            ],
            'orders' => [
                ['id' => 1, 'symbol' => 'BTCUSDT'],
                ['id' => 2, 'symbol' => 'ETHUSDT']
            ]
        ];

        $this->cache->set('nested', $data);
        $result = $this->cache->get('nested', 3600);

        $this->assertSame($data, $result);
    }

    public function testSetOverwritesExistingKey(): void
    {
        $this->cache->set('overwrite_test', ['old' => 'data']);
        $this->cache->set('overwrite_test', ['new' => 'data']);

        $result = $this->cache->get('overwrite_test', 3600);
        $this->assertSame(['new' => 'data'], $result);
    }

    public function testGetReturnsNullForCorruptedFile(): void
    {
        // Write invalid JSON to cache file
        $file = $this->cacheDir . '/' . md5('corrupted') . '.json';
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0777, true);
        }
        file_put_contents($file, 'not valid json {');

        $result = $this->cache->get('corrupted', 3600);

        $this->assertNull($result);
    }

    public function testClearOnEmptyDirectory(): void
    {
        // Clear on fresh directory shouldn't error
        $this->cache->clear();
        $this->assertTrue(true);
    }
}
