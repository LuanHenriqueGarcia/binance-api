<?php

namespace BinanceAPI\Database;

use BinanceAPI\Config;
use PDO;
use PDOException;

/**
 * Gerenciador de conexão com banco de dados
 */
class Connection
{
    private static ?PDO $instance = null;

    /**
     * Obtém a instância da conexão (singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }

        return self::$instance;
    }

    /**
     * Cria uma nova conexão
     */
    private static function createConnection(): PDO
    {
        $driver = Config::get('DB_DRIVER', 'pgsql');
        $host = Config::get('DB_HOST', 'localhost');
        $port = Config::get('DB_PORT', '5432');
        $database = Config::get('DB_DATABASE', '');
        $username = Config::get('DB_USERNAME', 'postgres');
        $password = Config::get('DB_PASSWORD', '');
        $charset = Config::get('DB_CHARSET', 'utf8');

        try {
            $dsn = match ($driver) {
                'mysql' => "mysql:host={$host};port={$port};dbname={$database};charset={$charset}",
                'pgsql' => "pgsql:host={$host};port={$port};dbname={$database}",
                'sqlite' => "sqlite:{$database}",
                default => throw new PDOException("Driver não suportado: {$driver}"),
            };

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new PDOException("Erro ao conectar: " . $e->getMessage());
        }
    }

    /**
     * Fecha a conexão
     */
    public static function close(): void
    {
        self::$instance = null;
    }

    /**
     * Reconecta ao banco
     */
    public static function reconnect(): PDO
    {
        self::close();
        return self::getInstance();
    }
}
