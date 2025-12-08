<?php

namespace BinanceAPI\DTO;

/**
 * Data Transfer Object para Ticker/Preço
 */
class TickerDTO
{
    public function __construct(
        public readonly string $symbol,
        public readonly string $price,
        public readonly ?string $priceChange = null,
        public readonly ?string $priceChangePercent = null,
        public readonly ?string $highPrice = null,
        public readonly ?string $lowPrice = null,
        public readonly ?string $volume = null,
        public readonly ?string $quoteVolume = null,
        public readonly ?int $openTime = null,
        public readonly ?int $closeTime = null,
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
            symbol: $data['symbol'] ?? '',
            price: $data['lastPrice'] ?? $data['price'] ?? '0',
            priceChange: $data['priceChange'] ?? null,
            priceChangePercent: $data['priceChangePercent'] ?? null,
            highPrice: $data['highPrice'] ?? null,
            lowPrice: $data['lowPrice'] ?? null,
            volume: $data['volume'] ?? null,
            quoteVolume: $data['quoteVolume'] ?? null,
            openTime: isset($data['openTime']) ? (int)$data['openTime'] : null,
            closeTime: isset($data['closeTime']) ? (int)$data['closeTime'] : null,
        );
    }

    /**
     * Converte para array
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'symbol' => $this->symbol,
            'price' => $this->price,
            'priceChange' => $this->priceChange,
            'priceChangePercent' => $this->priceChangePercent,
            'highPrice' => $this->highPrice,
            'lowPrice' => $this->lowPrice,
            'volume' => $this->volume,
            'quoteVolume' => $this->quoteVolume,
            'openTime' => $this->openTime,
            'closeTime' => $this->closeTime,
        ], fn($v) => $v !== null);
    }

    /**
     * Retorna a variação formatada
     */
    public function getFormattedChange(): string
    {
        if ($this->priceChangePercent === null) {
            return 'N/A';
        }

        $value = (float)$this->priceChangePercent;
        $prefix = $value >= 0 ? '+' : '';
        return $prefix . number_format($value, 2) . '%';
    }

    /**
     * Verifica se o preço está em alta
     */
    public function isPositive(): bool
    {
        return $this->priceChangePercent !== null && (float)$this->priceChangePercent >= 0;
    }
}
