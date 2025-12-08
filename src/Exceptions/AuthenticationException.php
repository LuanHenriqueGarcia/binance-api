<?php

namespace BinanceAPI\Exceptions;

/**
 * Exceção para erros de autenticação
 */
class AuthenticationException extends BinanceException
{
    public function __construct(string $message = 'Chaves de API não fornecidas ou inválidas')
    {
        parent::__construct($message, -2015, 401);
    }

    /**
     * Cria exceção para chaves ausentes
     */
    public static function missingKeys(): self
    {
        return new self('Chaves de API não fornecidas. Configure no .env ou passe como parâmetros.');
    }

    /**
     * Cria exceção para chaves inválidas
     */
    public static function invalidKeys(): self
    {
        return new self('Chaves de API inválidas ou expiradas.');
    }

    /**
     * Cria exceção para assinatura inválida
     */
    public static function invalidSignature(): self
    {
        return new self('Assinatura da requisição inválida.');
    }

    /**
     * Cria exceção para permissões insuficientes
     */
    public static function insufficientPermissions(): self
    {
        return new self('Chave de API sem permissões suficientes para esta operação.');
    }
}
