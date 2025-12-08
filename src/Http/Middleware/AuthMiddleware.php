<?php

namespace BinanceAPI\Http\Middleware;

use BinanceAPI\Http\Request;
use BinanceAPI\Http\Response;
use BinanceAPI\Config;

/**
 * Middleware de autenticação Basic Auth
 */
class AuthMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, callable $next): Response
    {
        $user = Config::getAuthUser();
        $pass = Config::getAuthPassword();

        // Se não houver autenticação configurada, passa direto
        if (!$user || !$pass) {
            return $next($request);
        }

        $inputUser = $_SERVER['PHP_AUTH_USER'] ?? null;
        $inputPass = $_SERVER['PHP_AUTH_PW'] ?? null;

        if ($inputUser === $user && $inputPass === $pass) {
            return $next($request);
        }

        header('WWW-Authenticate: Basic realm="Restricted"');
        return Response::unauthorized('Credenciais inválidas');
    }
}
