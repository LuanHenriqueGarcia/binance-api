## Requisitos
- PHP 8.1+ com cURL habilitado
- Composer (opcional, só para atualizar o autoload)

## Configuração
1. Clone ou baixe o projeto.
2. Crie um arquivo `.env` na raiz (ou edite o existente):

Para endpoints públicos, pode deixar as chaves em branco.
3. (Opcional) Rode `composer install` para gerar `vendor/autoload.php` conforme o `composer.json`.

## Como rodar localmente
- Usando o servidor embutido do PHP:

 <code>php -S localhost:8000 -t .</code>

 A aplicação responde em `http://localhost:8000`.
- Em Apache, habilite `mod_rewrite` e use o `.htaccess` já incluso.

## Endpoints rápidos
- Ping: `GET /api/general/ping`
- Hora do servidor: `GET /api/general/time`
- Info de câmbio: `GET /api/general/exchange-info?symbol=BTCUSDT`
- Preço atual: `GET /api/market/ticker?symbol=BTCUSDT`
- Order book: `GET /api/market/order-book?symbol=BTCUSDT&limit=100`
- Trades: `GET /api/market/trades?symbol=BTCUSDT&limit=500`

Endpoints autenticados (usam chaves do `.env` ou params `api_key`/`secret_key`):
- Info da conta: `GET /api/account/info`
- Ordens abertas: `GET /api/account/open-orders?symbol=BTCUSDT`
- Histórico de ordens: `GET /api/account/order-history?symbol=BTCUSDT&limit=500`
- Saldo de um ativo: `GET /api/account/balance?asset=USDT`

Trading:
```bash
# Criar ordem
curl -X POST http://localhost:8000/api/trading/create-order ^
-H "Content-Type: application/json" ^
-d "{\"api_key\":\"...\",\"secret_key\":\"...\",\"symbol\":\"BTCUSDT\",\"side\":\"BUY\",\"type\":\"LIMIT\",\"quantity\":\"0.001\",\"price\":\"42000\"}"

# Cancelar ordem
curl -X DELETE http://localhost:8000/api/trading/cancel-order ^
-H "Content-Type: application/json" ^
-d "{\"api_key\":\"...\",\"secret_key\":\"...\",\"symbol\":\"BTCUSDT\",\"orderId\":\"123456\"}"
