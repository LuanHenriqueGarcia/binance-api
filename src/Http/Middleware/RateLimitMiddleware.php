<?php

namespace BinanceAPI\Http\Middleware;

use BinanceAPI\Http\Request;
use BinanceAPI\Http\Response;
use BinanceAPI\Config;
use BinanceAPI\RateLimiter;

/**
 * Middleware de rate limiting
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    private RateLimiter $rateLimiter;

    /** @var array<string> */
    private array $protectedEndpoints = ['account', 'trading'];

    public function __construct(?RateLimiter $rateLimiter = null)
    {
        $this->rateLimiter = $rateLimiter ?? new RateLimiter();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, callable $next): Response
    {
        // Verifica se rate limit está habilitado
        if (!(bool)Config::get('RATE_LIMIT_ENABLED', false)) {
            return $next($request);
        }

        $segments = $request->getPathSegments();

        // Remove 'api' do início se existir
        if (!empty($segments) && $segments[0] === 'api') {
            array_shift($segments);
        }

        $endpoint = $segments[0] ?? null;

        // Verifica se o endpoint precisa de rate limiting
        if (!in_array($endpoint, $this->protectedEndpoints, true)) {
            return $next($request);
        }

        $ip = $request->getClientIp();
        $routeKey = "{$endpoint}:{$request->getMethod()}:{$ip}";

        $hit = $this->rateLimiter->hit($routeKey);

        if (!$hit['allowed']) {
            $retryAfter = $hit['retryAfter'] ?? 1;
            return Response::tooManyRequests($retryAfter);
        }

        return $next($request);
    }
}
