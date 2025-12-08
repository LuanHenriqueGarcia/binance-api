<?php

namespace BinanceAPI\Http;

/**
 * Classe para encapsular a requisição HTTP
 */
class Request
{
    private string $method;
    private string $path;

    /** @var array<string,mixed> */
    private array $params;

    /** @var array<string,string> */
    private array $headers;

    private string $body;

    /**
     * @param string|null $method Método HTTP (override)
     * @param string|null $path Caminho (override)
     * @param array<string,mixed>|null $params Parâmetros (override)
     */
    public function __construct(
        ?string $method = null,
        ?string $path = null,
        ?array $params = null
    ) {
        $this->method = $method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->path = $path ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->body = file_get_contents('php://input') ?: '';
        $this->headers = $this->parseHeaders();
        $this->params = $params ?? $this->parseParams();
    }

    /**
     * Retorna o método HTTP
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Retorna o caminho da requisição
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Retorna os parâmetros da requisição
     *
     * @return array<string,mixed>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Retorna um parâmetro específico
     *
     * @param mixed $default Valor padrão
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * Verifica se um parâmetro existe
     */
    public function has(string $key): bool
    {
        return isset($this->params[$key]);
    }

    /**
     * Retorna os headers da requisição
     *
     * @return array<string,string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Retorna um header específico
     */
    public function getHeader(string $name, string $default = ''): string
    {
        $name = strtolower($name);
        return $this->headers[$name] ?? $default;
    }

    /**
     * Retorna o corpo raw da requisição
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Retorna o IP do cliente
     */
    public function getClientIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_REAL_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? 'unknown';
    }

    /**
     * Retorna o Correlation ID se existir
     */
    public function getCorrelationId(): ?string
    {
        return $this->getHeader('x-correlation-id') ?: null;
    }

    /**
     * Verifica se a requisição é AJAX
     */
    public function isAjax(): bool
    {
        return $this->getHeader('x-requested-with') === 'XMLHttpRequest';
    }

    /**
     * Retorna os segmentos do path
     *
     * @return array<string>
     */
    public function getPathSegments(): array
    {
        $segments = array_filter(explode('/', $this->path));
        return array_values($segments);
    }

    /**
     * Parse dos headers HTTP
     *
     * @return array<string,string>
     */
    private function parseHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    /**
     * Parse dos parâmetros da requisição
     *
     * @return array<string,mixed>
     */
    private function parseParams(): array
    {
        if ($this->method === 'GET') {
            return $this->normalize($_GET);
        }

        if (in_array($this->method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $decoded = json_decode($this->body, true);
            $params = is_array($decoded) ? $decoded : [];
            return $this->normalize($params);
        }

        return [];
    }

    /**
     * Normaliza parâmetros (ex: uppercase em symbols)
     *
     * @param array<string,mixed> $params
     * @return array<string,mixed>
     */
    private function normalize(array $params): array
    {
        if (isset($params['symbol']) && is_string($params['symbol'])) {
            $params['symbol'] = strtoupper($params['symbol']);
        }

        return $params;
    }
}
