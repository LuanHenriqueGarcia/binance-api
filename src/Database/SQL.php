<?php

namespace BinanceAPI\Database;

use PDO;
use PDOStatement;

/**
 * Executor de SQL por tags
 *
 * Uso:
 *   SQL::query('users.find_by_id', ['id' => 1])->fetch();
 *   SQL::query('users.list_active')->fetchAll();
 *   SQL::execute('users.delete', ['id' => 1]);
 */
class SQL
{
    private static ?PDO $connection = null;

    /**
     * Define uma conexão customizada
     */
    public static function setConnection(PDO $connection): void
    {
        self::$connection = $connection;
    }

    /**
     * Obtém a conexão atual
     */
    public static function getConnection(): PDO
    {
        return self::$connection ?? Connection::getInstance();
    }

    /**
     * Executa uma query por tag e retorna o statement para fetch
     *
     * @param string $tag Nome da tag da query
     * @param array<string, mixed> $params Parâmetros para bind
     * @return PDOStatement Statement executado
     *
     * @example
     * // Buscar um registro
     * $user = SQL::query('users.find_by_id', ['id' => 1])->fetch();
     *
     * // Buscar vários registros
     * $users = SQL::query('users.list_active')->fetchAll();
     *
     * // Com paginação
     * $users = SQL::query('users.paginate', ['limit' => 10, 'offset' => 0])->fetchAll();
     */
    public static function query(string $tag, array $params = []): PDOStatement
    {
        $sql = QueryLoader::get($tag);
        $pdo = self::getConnection();

        $stmt = $pdo->prepare($sql);
        self::bindParams($stmt, $params);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Executa uma query por tag e retorna o número de linhas afetadas
     *
     * @param string $tag Nome da tag da query
     * @param array<string, mixed> $params Parâmetros para bind
     * @return int Número de linhas afetadas
     *
     * @example
     * // Insert
     * $affected = SQL::execute('users.create', ['name' => 'João', 'email' => 'joao@email.com']);
     *
     * // Update
     * $affected = SQL::execute('users.update', ['id' => 1, 'name' => 'João Silva']);
     *
     * // Delete
     * $affected = SQL::execute('users.delete', ['id' => 1]);
     */
    public static function execute(string $tag, array $params = []): int
    {
        $stmt = self::query($tag, $params);
        return $stmt->rowCount();
    }

    /**
     * Executa uma query e retorna um único registro
     *
     * @param string $tag Nome da tag da query
     * @param array<string, mixed> $params Parâmetros para bind
     * @return array<string, mixed>|null Registro ou null se não encontrado
     */
    public static function one(string $tag, array $params = []): ?array
    {
        $result = self::query($tag, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Executa uma query e retorna todos os registros
     *
     * @param string $tag Nome da tag da query
     * @param array<string, mixed> $params Parâmetros para bind
     * @return array<int, array<string, mixed>> Lista de registros
     */
    public static function all(string $tag, array $params = []): array
    {
        return self::query($tag, $params)->fetchAll();
    }

    /**
     * Executa uma query e retorna o valor de uma coluna específica
     *
     * @param string $tag Nome da tag da query
     * @param array<string, mixed> $params Parâmetros para bind
     * @param int $column Índice da coluna (0 por padrão)
     * @return mixed Valor da coluna ou false se não encontrado
     */
    public static function scalar(string $tag, array $params = [], int $column = 0): mixed
    {
        return self::query($tag, $params)->fetchColumn($column);
    }

    /**
     * Executa um INSERT e retorna o último ID inserido
     *
     * @param string $tag Nome da tag da query
     * @param array<string, mixed> $params Parâmetros para bind
     * @return string|int Último ID inserido
     */
    public static function insert(string $tag, array $params = []): string|int
    {
        self::execute($tag, $params);
        $lastId = self::getConnection()->lastInsertId();
        return is_numeric($lastId) ? (int)$lastId : $lastId;
    }

    /**
     * Executa múltiplas queries em uma transação
     *
     * @param callable $callback Função que recebe a classe SQL
     * @return mixed Retorno do callback
     * @throws \Throwable Se houver erro, faz rollback
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::getConnection();
        $pdo->beginTransaction();

        try {
            $result = $callback(new self());
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Executa SQL raw (sem usar tag)
     *
     * @param string $sql SQL direto
     * @param array<string, mixed> $params Parâmetros para bind
     * @return PDOStatement Statement executado
     */
    public static function raw(string $sql, array $params = []): PDOStatement
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        self::bindParams($stmt, $params);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Faz bind dos parâmetros no statement
     *
     * @param PDOStatement $stmt Statement
     * @param array<string, mixed> $params Parâmetros
     */
    private static function bindParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $paramName = ':' . ltrim($key, ':');

            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };

            $stmt->bindValue($paramName, $value, $type);
        }
    }

    /**
     * Lista todas as tags de queries disponíveis
     *
     * @return array<string>
     */
    public static function listQueries(): array
    {
        return QueryLoader::listTags();
    }

    /**
     * Obtém o SQL de uma tag (para debug)
     */
    public static function getSql(string $tag): string
    {
        return QueryLoader::get($tag);
    }
}
