<?php

namespace BinanceAPI\Database;

use BinanceAPI\Config;
use RuntimeException;

/**
 * Carrega queries SQL de arquivos .sql organizados por tags
 */
class QueryLoader
{
    /** @var array<string, string> Cache de queries carregadas */
    private static array $queries = [];

    /** @var bool Se já carregou os arquivos */
    private static bool $loaded = false;

    /** @var string Diretório das queries */
    private static string $queriesPath = '';

    /**
     * Define o diretório das queries
     */
    public static function setPath(string $path): void
    {
        self::$queriesPath = $path;
        self::$loaded = false;
        self::$queries = [];
    }

    /**
     * Obtém uma query pelo nome da tag
     *
     * @param string $tag Nome da tag (ex: "users.find_by_id", "orders.list_all")
     * @return string SQL da query
     * @throws RuntimeException Se a query não for encontrada
     */
    public static function get(string $tag): string
    {
        self::loadAll();

        if (!isset(self::$queries[$tag])) {
            throw new RuntimeException("Query não encontrada: {$tag}");
        }

        return self::$queries[$tag];
    }

    /**
     * Verifica se uma query existe
     */
    public static function has(string $tag): bool
    {
        self::loadAll();
        return isset(self::$queries[$tag]);
    }

    /**
     * Lista todas as tags disponíveis
     *
     * @return array<string>
     */
    public static function listTags(): array
    {
        self::loadAll();
        return array_keys(self::$queries);
    }

    /**
     * Carrega todas as queries dos arquivos .sql
     */
    private static function loadAll(): void
    {
        if (self::$loaded) {
            return;
        }

        $path = self::$queriesPath ?: Config::get('SQL_QUERIES_PATH', __DIR__ . '/../../sql');

        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }

        // Carrega todos os arquivos .sql recursivamente
        $files = self::findSqlFiles($path);

        foreach ($files as $file) {
            self::parseFile($file, $path);
        }

        self::$loaded = true;
    }

    /**
     * Encontra todos os arquivos .sql no diretório
     *
     * @return array<string>
     */
    private static function findSqlFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'sql') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Faz o parse de um arquivo .sql extraindo as tags
     */
    private static function parseFile(string $filePath, string $basePath): void
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return;
        }

        // Calcula o prefixo baseado no caminho do arquivo
        // Ex: sql/users/queries.sql -> "users"
        $relativePath = str_replace($basePath, '', $filePath);
        $relativePath = trim($relativePath, '/\\');
        $pathParts = pathinfo($relativePath);
        $prefix = str_replace(['/', '\\'], '.', $pathParts['dirname'] ?? '');
        $prefix = $prefix === '.' ? '' : $prefix . '.';

        // Pattern para encontrar tags: -- @tag: nome_da_query
        $pattern = '/--\s*@tag:\s*([a-zA-Z0-9_.-]+)\s*\n(.*?)(?=--\s*@tag:|$)/s';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tagName = trim($match[1]);
                $sql = trim($match[2]);

                // Remove comentários SQL no início
                $sql = preg_replace('/^--.*$/m', '', $sql);
                $sql = trim($sql);

                if (!empty($sql)) {
                    $fullTag = $prefix . $tagName;
                    // Remove prefixo duplicado se houver
                    $fullTag = preg_replace('/^\.+/', '', $fullTag);
                    self::$queries[$fullTag] = $sql;
                }
            }
        }
    }

    /**
     * Recarrega todas as queries (útil para dev)
     */
    public static function reload(): void
    {
        self::$loaded = false;
        self::$queries = [];
        self::loadAll();
    }

    /**
     * Limpa o cache de queries
     */
    public static function clear(): void
    {
        self::$loaded = false;
        self::$queries = [];
    }
}
