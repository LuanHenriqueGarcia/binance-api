<?php

namespace BinanceAPI\Http;

use BinanceAPI\Config;
use BinanceAPI\Enums\HttpStatus;

/**
 * Classe para formatação e envio de respostas HTTP
 */
class Response
{
    private int $statusCode;

    /** @var array<string,mixed> */
    private array $data;

    /** @var array<string,string> */
    private array $headers;

    /**
     * @param array<string,mixed> $data Dados da resposta
     * @param int $statusCode Código HTTP
     * @param array<string,string> $headers Headers adicionais
     */
    public function __construct(array $data = [], int $statusCode = 200, array $headers = [])
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Cria uma resposta de sucesso
     *
     * @param array<string,mixed>|mixed $data Dados da resposta
     * @param int $statusCode Código HTTP
     */
    public static function success($data = [], int $statusCode = 200): self
    {
        return new self([
            'success' => true,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Cria uma resposta de erro
     *
     * @param string $message Mensagem de erro
     * @param int $statusCode Código HTTP
     * @param int|null $errorCode Código de erro interno
     * @param array<string,mixed> $context Contexto adicional
     */
    public static function error(
        string $message,
        int $statusCode = 400,
        ?int $errorCode = null,
        array $context = []
    ): self {
        $data = [
            'success' => false,
            'error' => $message,
        ];

        if ($errorCode !== null) {
            $data['code'] = $errorCode;
        }

        if (!empty($context)) {
            $data['context'] = $context;
        }

        return new self($data, $statusCode);
    }

    /**
     * Cria uma resposta não encontrada
     */
    public static function notFound(string $message = 'Recurso não encontrado'): self
    {
        return self::error($message, 404);
    }

    /**
     * Cria uma resposta de não autorizado
     */
    public static function unauthorized(string $message = 'Não autorizado'): self
    {
        return self::error($message, 401);
    }

    /**
     * Cria uma resposta de rate limit excedido
     */
    public static function tooManyRequests(int $retryAfter = 60): self
    {
        $response = self::error(
            "Rate limit excedido. Tente novamente em {$retryAfter}s",
            429
        );
        $response->setHeader('Retry-After', (string)$retryAfter);
        return $response;
    }

    /**
     * Cria uma resposta de erro interno
     */
    public static function internalError(string $message = 'Erro interno do servidor'): self
    {
        return self::error($message, 500);
    }

    /**
     * Define um header
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Define o código de status
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Retorna o código de status
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Retorna os dados da resposta
     *
     * @return array<string,mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Envia a resposta HTTP
     */
    public function send(): void
    {
        http_response_code($this->statusCode);

        // Headers padrão
        header('Content-Type: application/json; charset=utf-8');
        header('X-Request-Id: ' . Config::getRequestId());
        header('X-Response-Time: ' . $this->getResponseTime() . 'ms');

        // Headers personalizados
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Corpo da resposta
        echo json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Calcula o tempo de resposta em ms
     */
    private function getResponseTime(): int
    {
        $start = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        return (int)((microtime(true) - $start) * 1000);
    }

    /**
     * Converte para JSON string
     */
    public function toJson(): string
    {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Converte para array
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
