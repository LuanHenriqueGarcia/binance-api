<?php

namespace BinanceAPI\Http\Middleware;

use BinanceAPI\Http\Request;
use BinanceAPI\Http\Response;
use BinanceAPI\Logger;

/**
 * Middleware para logging de requisições
 */
class LoggingMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, callable $next): Response
    {
        $startTime = microtime(true);

        // Log da requisição
        Logger::info([
            'event' => 'request_started',
            'method' => $request->getMethod(),
            'path' => $request->getPath(),
            'ip' => $request->getClientIp(),
            'correlation_id' => $request->getCorrelationId(),
        ]);

        // Executa o próximo middleware/handler
        $response = $next($request);

        // Log da resposta
        $duration = (int)((microtime(true) - $startTime) * 1000);
        Logger::info([
            'event' => 'request_completed',
            'method' => $request->getMethod(),
            'path' => $request->getPath(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
        ]);

        return $response;
    }
}
