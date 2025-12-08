<?php

namespace BinanceAPI\Enums;

/**
 * Enum para códigos HTTP comuns
 */
enum HttpStatus: int
{
    case OK = 200;
    case CREATED = 201;
    case NO_CONTENT = 204;
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case TOO_MANY_REQUESTS = 429;
    case INTERNAL_SERVER_ERROR = 500;
    case BAD_GATEWAY = 502;
    case SERVICE_UNAVAILABLE = 503;

    /**
     * Verifica se é um código de sucesso (2xx)
     */
    public function isSuccess(): bool
    {
        return in_array($this, [self::OK, self::CREATED, self::NO_CONTENT], true);
    }

    /**
     * Verifica se é um erro do cliente (4xx)
     */
    public function isClientError(): bool
    {
        return in_array($this, [
            self::BAD_REQUEST,
            self::UNAUTHORIZED,
            self::FORBIDDEN,
            self::NOT_FOUND,
            self::TOO_MANY_REQUESTS
        ], true);
    }

    /**
     * Verifica se é um erro do servidor (5xx)
     */
    public function isServerError(): bool
    {
        return in_array($this, [
            self::INTERNAL_SERVER_ERROR,
            self::BAD_GATEWAY,
            self::SERVICE_UNAVAILABLE
        ], true);
    }

    /**
     * Retorna a mensagem padrão do status
     */
    public function message(): string
    {
        return match ($this) {
            self::OK => 'OK',
            self::CREATED => 'Created',
            self::NO_CONTENT => 'No Content',
            self::BAD_REQUEST => 'Bad Request',
            self::UNAUTHORIZED => 'Unauthorized',
            self::FORBIDDEN => 'Forbidden',
            self::NOT_FOUND => 'Not Found',
            self::TOO_MANY_REQUESTS => 'Too Many Requests',
            self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
            self::BAD_GATEWAY => 'Bad Gateway',
            self::SERVICE_UNAVAILABLE => 'Service Unavailable',
        };
    }
}
