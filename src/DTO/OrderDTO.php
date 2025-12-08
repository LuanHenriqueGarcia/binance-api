<?php

namespace BinanceAPI\DTO;

/**
 * Data Transfer Object para Ordens
 */
class OrderDTO
{
    public function __construct(
        public readonly string $symbol,
        public readonly string $side,
        public readonly string $type,
        public readonly ?string $quantity = null,
        public readonly ?string $quoteOrderQty = null,
        public readonly ?string $price = null,
        public readonly ?string $stopPrice = null,
        public readonly ?string $timeInForce = null,
        public readonly ?string $newClientOrderId = null,
        public readonly ?int $strategyId = null,
        public readonly ?int $strategyType = null,
    ) {
    }

    /**
     * Cria DTO a partir de array de parâmetros
     *
     * @param array<string,mixed> $params
     */
    public static function fromArray(array $params): self
    {
        return new self(
            symbol: strtoupper($params['symbol'] ?? ''),
            side: strtoupper($params['side'] ?? ''),
            type: strtoupper($params['type'] ?? ''),
            quantity: $params['quantity'] ?? null,
            quoteOrderQty: $params['quoteOrderQty'] ?? null,
            price: $params['price'] ?? null,
            stopPrice: $params['stopPrice'] ?? null,
            timeInForce: isset($params['timeInForce']) ? strtoupper($params['timeInForce']) : null,
            newClientOrderId: $params['newClientOrderId'] ?? null,
            strategyId: isset($params['strategyId']) ? (int)$params['strategyId'] : null,
            strategyType: isset($params['strategyType']) ? (int)$params['strategyType'] : null,
        );
    }

    /**
     * Converte para array para envio à API
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $params = [
            'symbol' => $this->symbol,
            'side' => $this->side,
            'type' => $this->type,
        ];

        if ($this->quantity !== null) {
            $params['quantity'] = $this->quantity;
        }

        if ($this->quoteOrderQty !== null) {
            $params['quoteOrderQty'] = $this->quoteOrderQty;
        }

        if ($this->price !== null) {
            $params['price'] = $this->price;
        }

        if ($this->stopPrice !== null) {
            $params['stopPrice'] = $this->stopPrice;
        }

        if ($this->timeInForce !== null) {
            $params['timeInForce'] = $this->timeInForce;
        }

        if ($this->newClientOrderId !== null) {
            $params['newClientOrderId'] = $this->newClientOrderId;
        }

        if ($this->strategyId !== null) {
            $params['strategyId'] = $this->strategyId;
        }

        if ($this->strategyType !== null) {
            $params['strategyType'] = $this->strategyType;
        }

        return $params;
    }

    /**
     * Valida o DTO
     *
     * @return array<string> Lista de erros de validação
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->symbol)) {
            $errors[] = 'Symbol é obrigatório';
        }

        if (!in_array($this->side, ['BUY', 'SELL'])) {
            $errors[] = 'Side deve ser BUY ou SELL';
        }

        $validTypes = ['LIMIT', 'MARKET', 'STOP_LOSS', 'STOP_LOSS_LIMIT', 'TAKE_PROFIT', 'TAKE_PROFIT_LIMIT', 'LIMIT_MAKER'];
        if (!in_array($this->type, $validTypes)) {
            $errors[] = 'Type inválido. Valores permitidos: ' . implode(', ', $validTypes);
        }

        // Validações específicas por tipo
        if ($this->type === 'LIMIT') {
            if ($this->price === null) {
                $errors[] = 'Price é obrigatório para ordens LIMIT';
            }
            if ($this->quantity === null) {
                $errors[] = 'Quantity é obrigatório para ordens LIMIT';
            }
            if ($this->timeInForce === null) {
                $errors[] = 'TimeInForce é obrigatório para ordens LIMIT';
            }
        }

        if ($this->type === 'MARKET') {
            if ($this->quantity === null && $this->quoteOrderQty === null) {
                $errors[] = 'Quantity ou quoteOrderQty é obrigatório para ordens MARKET';
            }
        }

        if (in_array($this->type, ['STOP_LOSS', 'TAKE_PROFIT'])) {
            if ($this->stopPrice === null) {
                $errors[] = 'StopPrice é obrigatório para ordens ' . $this->type;
            }
        }

        return $errors;
    }
}
