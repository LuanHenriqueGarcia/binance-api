<?php

namespace BinanceAPI\DTO;

/**
 * Data Transfer Object para Saldo de Ativo
 */
class BalanceDTO
{
    public function __construct(
        public readonly string $asset,
        public readonly string $free,
        public readonly string $locked,
    ) {
    }

    /**
     * Cria DTO a partir de resposta da API
     *
     * @param array<string,mixed> $data
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            asset: $data['asset'] ?? '',
            free: $data['free'] ?? '0',
            locked: $data['locked'] ?? '0',
        );
    }

    /**
     * Converte para array
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'asset' => $this->asset,
            'free' => $this->free,
            'locked' => $this->locked,
            'total' => $this->getTotal(),
        ];
    }

    /**
     * Retorna o saldo total (free + locked)
     */
    public function getTotal(): string
    {
        return bcadd($this->free, $this->locked, 8);
    }

    /**
     * Verifica se tem saldo disponível
     */
    public function hasFreeBalance(): bool
    {
        return bccomp($this->free, '0', 8) > 0;
    }

    /**
     * Verifica se tem saldo bloqueado
     */
    public function hasLockedBalance(): bool
    {
        return bccomp($this->locked, '0', 8) > 0;
    }

    /**
     * Verifica se tem qualquer saldo
     */
    public function hasBalance(): bool
    {
        return bccomp($this->getTotal(), '0', 8) > 0;
    }
}
