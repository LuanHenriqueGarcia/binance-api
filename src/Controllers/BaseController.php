<?php

namespace BinanceAPI\Controllers;

use BinanceAPI\Http\Response;

/**
 * Controller base com funcionalidades comuns
 */
abstract class BaseController
{
    /**
     * Formata a resposta para o padrão da API
     *
     * @param array<string,mixed> $data Dados a formatar
     * @return array<string,mixed> Dados formatados
     */
    protected function formatResponse(array $data): array
    {
        // Se já tem estrutura de erro, retorna como está
        if (isset($data['success']) && $data['success'] === false) {
            return $data;
        }

        // Se tem erro da Binance
        if (isset($data['code']) && isset($data['msg'])) {
            return [
                'success' => false,
                'error' => $data['msg'],
                'code' => $data['code'],
            ];
        }

        // Resposta de sucesso
        return [
            'success' => true,
            'data' => $data,
        ];
    }

    /**
     * Cria uma resposta de sucesso
     *
     * @param mixed $data Dados da resposta
     */
    protected function success($data = []): Response
    {
        return Response::success($data);
    }

    /**
     * Cria uma resposta de erro
     *
     * @param string $message Mensagem de erro
     * @param int $statusCode Código HTTP
     * @param int|null $errorCode Código de erro interno
     */
    protected function error(string $message, int $statusCode = 400, ?int $errorCode = null): Response
    {
        return Response::error($message, $statusCode, $errorCode);
    }

    /**
     * Cria uma resposta de recurso não encontrado
     */
    protected function notFound(string $message = 'Recurso não encontrado'): Response
    {
        return Response::notFound($message);
    }

    /**
     * Verifica se os parâmetros obrigatórios estão presentes
     *
     * @param array<string,mixed> $params Parâmetros recebidos
     * @param array<string> $required Campos obrigatórios
     * @return string|null Mensagem de erro ou null se OK
     */
    protected function validateRequired(array $params, array $required): ?string
    {
        foreach ($required as $field) {
            if (empty($params[$field])) {
                return "Parâmetro \"{$field}\" é obrigatório";
            }
        }

        return null;
    }

    /**
     * Obtém um parâmetro com valor padrão
     *
     * @param array<string,mixed> $params Parâmetros
     * @param string $key Chave do parâmetro
     * @param mixed $default Valor padrão
     * @return mixed Valor do parâmetro ou padrão
     */
    protected function getParam(array $params, string $key, $default = null)
    {
        return $params[$key] ?? $default;
    }

    /**
     * Obtém um parâmetro como inteiro
     *
     * @param array<string,mixed> $params Parâmetros
     * @param string $key Chave do parâmetro
     * @param int $default Valor padrão
     */
    protected function getIntParam(array $params, string $key, int $default = 0): int
    {
        return (int)($params[$key] ?? $default);
    }

    /**
     * Obtém um parâmetro como string
     *
     * @param array<string,mixed> $params Parâmetros
     * @param string $key Chave do parâmetro
     * @param string $default Valor padrão
     */
    protected function getStringParam(array $params, string $key, string $default = ''): string
    {
        return (string)($params[$key] ?? $default);
    }

    /**
     * Obtém um parâmetro como booleano
     *
     * @param array<string,mixed> $params Parâmetros
     * @param string $key Chave do parâmetro
     * @param bool $default Valor padrão
     */
    protected function getBoolParam(array $params, string $key, bool $default = false): bool
    {
        $value = $params[$key] ?? $default;

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
        }

        return (bool)$value;
    }
}
