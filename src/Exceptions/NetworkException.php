<?php

namespace BinanceAPI\Exceptions;

/**
 * Exceção para erros de conexão/rede
 */
class NetworkException extends BinanceException
{
    public function __construct(string $message = 'Erro de conexão com a API Binance')
    {
        parent::__construct($message, -1001, 503);
    }

    /**
     * Cria exceção para timeout
     */
    public static function timeout(int $seconds): self
    {
        return new self("Timeout após {$seconds} segundos aguardando resposta da Binance");
    }

    /**
     * Cria exceção para erro de conexão
     */
    public static function connectionFailed(string $reason = ''): self
    {
        $message = 'Falha ao conectar com a API Binance';
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($message);
    }

    /**
     * Cria exceção para resposta inválida
     */
    public static function invalidResponse(): self
    {
        return new self('Resposta inválida recebida da API Binance');
    }
}
