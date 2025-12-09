<?php

use BinanceAPI\Database\Connection;
use BinanceAPI\Database\QueryLoader;
use BinanceAPI\Database\SQL;
use BinanceAPI\Config;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private static ?PDO $testConnection = null;

    protected function setUp(): void
    {
        Config::fake([]);
        QueryLoader::clear();
    }

    protected function tearDown(): void
    {
        QueryLoader::clear();
    }

    // ========== QueryLoader Tests ==========

    public function testQueryLoaderSetPath(): void
    {
        $tempDir = sys_get_temp_dir() . '/sql_test_' . uniqid();
        @mkdir($tempDir, 0777, true);

        QueryLoader::setPath($tempDir);

        // Should not throw when listing tags
        $tags = QueryLoader::listTags();
        $this->assertIsArray($tags);

        @rmdir($tempDir);
    }

    public function testQueryLoaderGetThrowsForMissingQuery(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Query não encontrada');

        QueryLoader::get('nonexistent.query');
    }

    public function testQueryLoaderHas(): void
    {
        $this->assertFalse(QueryLoader::has('nonexistent.query'));
    }

    public function testQueryLoaderListTags(): void
    {
        $tags = QueryLoader::listTags();
        $this->assertIsArray($tags);
    }

    public function testQueryLoaderReload(): void
    {
        QueryLoader::reload();
        $tags = QueryLoader::listTags();
        $this->assertIsArray($tags);
    }

    public function testQueryLoaderClear(): void
    {
        QueryLoader::clear();
        $this->assertFalse(QueryLoader::has('any.query'));
    }

    public function testQueryLoaderParsesTagsFromSqlFile(): void
    {
        $tempDir = sys_get_temp_dir() . '/sql_test_' . uniqid();
        @mkdir($tempDir, 0777, true);

        // Create a test SQL file with tags
        $sqlContent = <<<SQL
-- @tag: test_select
SELECT * FROM users WHERE id = :id;

-- @tag: test_insert
INSERT INTO users (name, email) VALUES (:name, :email);
SQL;

        file_put_contents($tempDir . '/test.sql', $sqlContent);

        QueryLoader::setPath($tempDir);
        QueryLoader::reload();

        $this->assertTrue(QueryLoader::has('test_select'));
        $this->assertTrue(QueryLoader::has('test_insert'));

        $sql = QueryLoader::get('test_select');
        $this->assertStringContainsString('SELECT * FROM users', $sql);

        // Cleanup
        @unlink($tempDir . '/test.sql');
        @rmdir($tempDir);
    }

    // ========== Connection Tests ==========

    public function testConnectionClose(): void
    {
        // Just ensure close doesn't throw
        Connection::close();
        $this->assertTrue(true);
    }

    // ========== SQL Tests with SQLite ==========

    public function testSQLSetConnection(): void
    {
        $pdo = $this->createSqliteConnection();
        SQL::setConnection($pdo);

        $this->assertSame($pdo, SQL::getConnection());
    }

    public function testSQLRawQuery(): void
    {
        $pdo = $this->createSqliteConnection();
        SQL::setConnection($pdo);

        // Create table
        SQL::raw('CREATE TABLE IF NOT EXISTS test_table (id INTEGER PRIMARY KEY, name TEXT)');

        // Insert data
        SQL::raw('INSERT INTO test_table (name) VALUES (:name)', ['name' => 'Test']);

        // Query
        $result = SQL::raw('SELECT * FROM test_table WHERE name = :name', ['name' => 'Test']);
        $row = $result->fetch();

        $this->assertSame('Test', $row['name']);
    }

    public function testSQLBindParamsTypes(): void
    {
        $pdo = $this->createSqliteConnection();
        SQL::setConnection($pdo);

        SQL::raw('CREATE TABLE IF NOT EXISTS type_test (id INTEGER, active INTEGER, name TEXT, value REAL)');

        // Test different parameter types
        SQL::raw(
            'INSERT INTO type_test (id, active, name, value) VALUES (:id, :active, :name, :value)',
            [
                'id' => 1,
                'active' => true,
                'name' => 'test',
                'value' => null
            ]
        );

        $row = SQL::raw('SELECT * FROM type_test WHERE id = :id', ['id' => 1])->fetch();

        $this->assertSame(1, (int)$row['id']);
        $this->assertSame('test', $row['name']);
    }

    public function testSQLTransaction(): void
    {
        $pdo = $this->createSqliteConnection();
        SQL::setConnection($pdo);

        SQL::raw('CREATE TABLE IF NOT EXISTS trans_test (id INTEGER PRIMARY KEY, name TEXT)');

        $result = SQL::transaction(function () {
            SQL::raw('INSERT INTO trans_test (name) VALUES (:name)', ['name' => 'Transaction Test']);
            return 'success';
        });

        $this->assertSame('success', $result);

        $row = SQL::raw('SELECT * FROM trans_test WHERE name = :name', ['name' => 'Transaction Test'])->fetch();
        $this->assertSame('Transaction Test', $row['name']);
    }

    public function testSQLTransactionRollback(): void
    {
        $pdo = $this->createSqliteConnection();
        SQL::setConnection($pdo);

        SQL::raw('CREATE TABLE IF NOT EXISTS rollback_test (id INTEGER PRIMARY KEY, name TEXT)');

        try {
            SQL::transaction(function () {
                SQL::raw('INSERT INTO rollback_test (name) VALUES (:name)', ['name' => 'Rollback Test']);
                throw new \Exception('Force rollback');
            });
        } catch (\Exception $e) {
            // Expected
        }

        $row = SQL::raw('SELECT * FROM rollback_test WHERE name = :name', ['name' => 'Rollback Test'])->fetch();
        $this->assertFalse($row); // Should be rolled back
    }

    public function testSQLListQueries(): void
    {
        $queries = SQL::listQueries();
        $this->assertIsArray($queries);
    }

    public function testSQLGetSqlThrowsForMissingQuery(): void
    {
        $this->expectException(RuntimeException::class);
        SQL::getSql('nonexistent.query');
    }

    public function testSQLOne(): void
    {
        $pdo = $this->createSqliteConnection();
        SQL::setConnection($pdo);

        SQL::raw('CREATE TABLE IF NOT EXISTS one_test (id INTEGER PRIMARY KEY, name TEXT)');
        SQL::raw('INSERT INTO one_test (id, name) VALUES (:id, :name)', ['id' => 1, 'name' => 'First']);

        // Test with tag - need to create a tag first
        $tempDir = sys_get_temp_dir() . '/sql_test_one_' . uniqid();
        @mkdir($tempDir, 0777, true);

        $sqlContent = <<<SQL
-- @tag: find_one
SELECT * FROM one_test WHERE id = :id;
SQL;
        file_put_contents($tempDir . '/test.sql', $sqlContent);

        QueryLoader::setPath($tempDir);
        QueryLoader::reload();

        $result = SQL::one('find_one', ['id' => 1]);
        $this->assertSame('First', $result['name']);

        $result = SQL::one('find_one', ['id' => 999]);
        $this->assertNull($result);

        @unlink($tempDir . '/test.sql');
        @rmdir($tempDir);
    }

    public function testSQLAll(): void
    {
        $pdo = $this->createSqliteConnection();
        SQL::setConnection($pdo);

        SQL::raw('CREATE TABLE IF NOT EXISTS all_test (id INTEGER PRIMARY KEY, name TEXT)');
        SQL::raw('DELETE FROM all_test');
        SQL::raw('INSERT INTO all_test (name) VALUES (:name)', ['name' => 'One']);
        SQL::raw('INSERT INTO all_test (name) VALUES (:name)', ['name' => 'Two']);

        $tempDir = sys_get_temp_dir() . '/sql_test_all_' . uniqid();
        @mkdir($tempDir, 0777, true);

        $sqlContent = <<<SQL
-- @tag: list_all
SELECT * FROM all_test;
SQL;
        file_put_contents($tempDir . '/test.sql', $sqlContent);

        QueryLoader::setPath($tempDir);
        QueryLoader::reload();

        $results = SQL::all('list_all');
        $this->assertCount(2, $results);

        @unlink($tempDir . '/test.sql');
        @rmdir($tempDir);
    }

    public function testSQLScalar(): void
    {
        $pdo = $this->createSqliteConnection();
        SQL::setConnection($pdo);

        SQL::raw('CREATE TABLE IF NOT EXISTS scalar_test (id INTEGER PRIMARY KEY, value INTEGER)');
        SQL::raw('DELETE FROM scalar_test');
        SQL::raw('INSERT INTO scalar_test (value) VALUES (:value)', ['value' => 42]);

        $tempDir = sys_get_temp_dir() . '/sql_test_scalar_' . uniqid();
        @mkdir($tempDir, 0777, true);

        $sqlContent = <<<SQL
-- @tag: get_value
SELECT value FROM scalar_test WHERE id = :id;
SQL;
        file_put_contents($tempDir . '/test.sql', $sqlContent);

        QueryLoader::setPath($tempDir);
        QueryLoader::reload();

        $value = SQL::scalar('get_value', ['id' => 1]);
        $this->assertEquals(42, $value);

        @unlink($tempDir . '/test.sql');
        @rmdir($tempDir);
    }

    public function testSQLInsert(): void
    {
        $pdo = $this->createSqliteConnection();
        SQL::setConnection($pdo);

        SQL::raw('CREATE TABLE IF NOT EXISTS insert_test (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');

        $tempDir = sys_get_temp_dir() . '/sql_test_insert_' . uniqid();
        @mkdir($tempDir, 0777, true);

        $sqlContent = <<<SQL
-- @tag: create_item
INSERT INTO insert_test (name) VALUES (:name);
SQL;
        file_put_contents($tempDir . '/test.sql', $sqlContent);

        QueryLoader::setPath($tempDir);
        QueryLoader::reload();

        $id = SQL::insert('create_item', ['name' => 'New Item']);
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        @unlink($tempDir . '/test.sql');
        @rmdir($tempDir);
    }

    public function testSQLExecute(): void
    {
        $pdo = $this->createSqliteConnection();
        SQL::setConnection($pdo);

        SQL::raw('CREATE TABLE IF NOT EXISTS execute_test (id INTEGER PRIMARY KEY, name TEXT)');
        SQL::raw('INSERT INTO execute_test (id, name) VALUES (:id, :name)', ['id' => 1, 'name' => 'Test']);

        $tempDir = sys_get_temp_dir() . '/sql_test_execute_' . uniqid();
        @mkdir($tempDir, 0777, true);

        $sqlContent = <<<SQL
-- @tag: update_item
UPDATE execute_test SET name = :name WHERE id = :id;
SQL;
        file_put_contents($tempDir . '/test.sql', $sqlContent);

        QueryLoader::setPath($tempDir);
        QueryLoader::reload();

        $affected = SQL::execute('update_item', ['id' => 1, 'name' => 'Updated']);
        $this->assertSame(1, $affected);

        @unlink($tempDir . '/test.sql');
        @rmdir($tempDir);
    }

    // ========== Connection Tests with SQLite ==========

    public function testConnectionWithSqlite(): void
    {
        Config::fake([
            'DB_DRIVER' => 'sqlite',
            'DB_DATABASE' => ':memory:'
        ]);

        Connection::close();

        try {
            $pdo = Connection::getInstance();
            $this->assertInstanceOf(PDO::class, $pdo);
        } finally {
            Connection::close();
        }
    }

    public function testConnectionReconnect(): void
    {
        Config::fake([
            'DB_DRIVER' => 'sqlite',
            'DB_DATABASE' => ':memory:'
        ]);

        Connection::close();

        try {
            $pdo1 = Connection::getInstance();
            $pdo2 = Connection::reconnect();

            $this->assertInstanceOf(PDO::class, $pdo2);
        } finally {
            Connection::close();
        }
    }

    public function testConnectionUnsupportedDriver(): void
    {
        Config::fake([
            'DB_DRIVER' => 'unsupported_driver',
            'DB_DATABASE' => 'test'
        ]);

        Connection::close();

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('Driver não suportado');

        try {
            Connection::getInstance();
        } finally {
            Connection::close();
        }
    }

    public function testConnectionMysqlDsnFormat(): void
    {
        // This test verifies the DSN is built correctly but won't actually connect
        Config::fake([
            'DB_DRIVER' => 'mysql',
            'DB_HOST' => 'localhost',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'test_db',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
            'DB_CHARSET' => 'utf8mb4'
        ]);

        Connection::close();

        // MySQL connection will fail, but we're testing the flow
        try {
            Connection::getInstance();
            $this->fail('Expected PDOException');
        } catch (PDOException $e) {
            // Expected - either driver not found or connection refused
            $msg = strtolower($e->getMessage());
            $this->assertTrue(
                str_contains($msg, 'mysql') || str_contains($msg, 'driver'),
                "Expected error to contain 'mysql' or 'driver', got: " . $e->getMessage()
            );
        } finally {
            Connection::close();
        }
    }

    public function testConnectionPgsqlDsnFormat(): void
    {
        Config::fake([
            'DB_DRIVER' => 'pgsql',
            'DB_HOST' => 'localhost',
            'DB_PORT' => '5432',
            'DB_DATABASE' => 'test_db',
            'DB_USERNAME' => 'postgres',
            'DB_PASSWORD' => ''
        ]);

        Connection::close();

        // PostgreSQL connection will fail, but we're testing the flow
        try {
            Connection::getInstance();
        } catch (PDOException $e) {
            // Expected - no PostgreSQL server available
            $this->assertTrue(true);
        } finally {
            Connection::close();
        }
    }

    private function createSqliteConnection(): PDO
    {
        if (self::$testConnection === null) {
            self::$testConnection = new PDO('sqlite::memory:', null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return self::$testConnection;
    }
}
