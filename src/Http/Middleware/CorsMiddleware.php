<?php

namespace BinanceAPI\Http\Middleware;

use BinanceAPI\Http\Request;
use BinanceAPI\Http\Response;

/**
 * Middleware para adicionar headers CORS
 */
class CorsMiddleware implements MiddlewareInterface
{
    /** @var array<string> */
    private array $allowedOrigins;

    /** @var array<string> */
    private array $allowedMethods;

    /** @var array<string> */
    private array $allowedHeaders;

    /**
     * @param array<string> $allowedOrigins Origens permitidas
     * @param array<string> $allowedMethods Métodos permitidos
     * @param array<string> $allowedHeaders Headers permitidos
     */
    public function __construct(
        array $allowedOrigins = ['*'],
        array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Correlation-Id']
    ) {
        $this->allowedOrigins = $allowedOrigins;
        $this->allowedMethods = $allowedMethods;
        $this->allowedHeaders = $allowedHeaders;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, callable $next): Response
    {
        // Preflight request
        if ($request->getMethod() === 'OPTIONS') {
            return $this->handlePreflight();
        }

        $response = $next($request);

        return $this->addCorsHeaders($response);
    }

    /**
     * Trata requisições preflight OPTIONS
     */
    private function handlePreflight(): Response
    {
        $response = new Response([], 204);
        return $this->addCorsHeaders($response);
    }

    /**
     * Adiciona headers CORS à resposta
     */
    private function addCorsHeaders(Response $response): Response
    {
        $origin = in_array('*', $this->allowedOrigins)
            ? '*'
            : ($_SERVER['HTTP_ORIGIN'] ?? '*');

        return $response
            ->setHeader('Access-Control-Allow-Origin', $origin)
            ->setHeader('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods))
            ->setHeader('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders))
            ->setHeader('Access-Control-Max-Age', '86400');
    }
}
