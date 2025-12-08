<?php

namespace BinanceAPI\Http\Middleware;

use BinanceAPI\Http\Request;
use BinanceAPI\Http\Response;

/**
 * Interface para middlewares HTTP
 */
interface MiddlewareInterface
{
    /**
     * Processa a requisição
     *
     * @param Request $request Requisição HTTP
     * @param callable $next Próximo middleware/handler
     * @return Response Resposta HTTP
     */
    public function handle(Request $request, callable $next): Response;
}
