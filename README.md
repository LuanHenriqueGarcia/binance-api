## Requisitos
- PHP 8.1+ com cURL habilitado
- Composer (opcional, só para atualizar o autoload e ferramentas de dev)

## Configuração
1. Clone ou baixe o projeto.
2. (Opcional) Rode `composer install` para gerar `vendor/autoload.php` e instalar ferramentas de dev.
3. (Produção) Gere autoload otimizado: `composer dumpautoload -o`.

## Como rodar localmente
- Usando o servidor embutido do PHP:
  ```
  php -S localhost:8000 -t .
  ```
  A aplicação responde em `http://localhost:8000`.
- Em Apache, habilite `mod_rewrite` e use o `.htaccess` já incluso.

## Como usar no Insomnia
1) Abra o Insomnia → Application → Import/Export → Import Data → From File.  
2) Selecione `insomnia.json` na raiz do projeto (já contém todas as rotas).  
3) No ambiente do Insomnia:
- `base_url`: ex. http://localhost:8000
- `api_key` / `secret_key`: suas chaves da Binance (ou deixe vazio para endpoints públicos).
- `BINANCE_BASE_URL` (opcional): override da base (ex.: https://testnet.binance.vision). Ou use `BINANCE_TESTNET=true`.
- `BINANCE_SSL_VERIFY` (opcional): `false` para ignorar certificado em ambiente local (não use em produção).
- `BINANCE_CA_BUNDLE` (opcional): caminho para um bundle de CA customizado (mantém verificação SSL).
- `BASIC_AUTH_USER` / `BASIC_AUTH_PASSWORD` (opcional): protege todas as rotas com Basic Auth.
- `RATE_LIMIT_ENABLED` (opcional): `true` para habilitar limitador em account/trading. Ajuste `RATE_LIMIT_MAX` e `RATE_LIMIT_WINDOW` (segundos).
- `APP_LOG_FILE` (opcional): caminho para arquivo de log JSON (somente loga em debug).
- `CACHE_EXCHANGEINFO_TTL` (opcional): TTL em segundos do cache de `/exchangeInfo` (default 30s).
- `BINANCE_RECV_WINDOW` (opcional): recvWindow para requisições assinadas (default 5000ms).
- `STORAGE_PATH` (opcional): base para pastas de cache/ratelimit (default `storage/`).
- `METRICS_ENABLED` (opcional): expõe `/metrics` com contagem e latência média.
4) Rode o servidor local: `php -S localhost:8000 -t .`  
5) Teste as rotas (General, Market, Account, Trading). Endpoints de conta/trading exigem chaves.

## Endpoints rápidos
- Ping: `GET /api/general/ping`
- Hora do servidor: `GET /api/general/time`
- Info de câmbio: `GET /api/general/exchange-info?symbol=BTCUSDT`
- Preço atual: `GET /api/market/ticker?symbol=BTCUSDT`
- Order book: `GET /api/market/order-book?symbol=BTCUSDT&limit=100`
- Trades: `GET /api/market/trades?symbol=BTCUSDT&limit=500`
- Agg trades: `GET /api/market/agg-trades?symbol=BTCUSDT&limit=500`
- Book ticker: `GET /api/market/book-ticker` (um ou todos com `symbol`)
- Avg price: `GET /api/market/avg-price?symbol=BTCUSDT`
- Klines/UI Klines: `GET /api/market/klines?symbol=BTCUSDT&interval=1h&limit=500` (ou `/ui-klines`)
- Historical trades: `GET /api/market/historical-trades?symbol=BTCUSDT&limit=100`
- Rolling window ticker: `GET /api/market/rolling-window-ticker?windowSize=1d` (opcional `symbol`/`symbols`)
- Ticker price (all): `GET /api/market/ticker-price` (ou com `symbol`)
- Ticker 24h (all): `GET /api/market/ticker-24h` (ou com `symbol`)

Endpoints autenticados (usam chaves do `.env` ou params `api_key`/`secret_key`):
- Info da conta: `GET /api/account/info`
- Ordens abertas: `GET /api/account/open-orders?symbol=BTCUSDT`
- Histórico de ordens: `GET /api/account/order-history?symbol=BTCUSDT&limit=500`
- Saldo de um ativo: `GET /api/account/balance?asset=USDT`
- Trades da conta: `GET /api/account/my-trades?symbol=BTCUSDT&limit=500`
- Status da conta: `GET /api/account/account-status`
- Status de trading: `GET /api/account/api-trading-status`
- Capital config (saldos detalhados): `GET /api/account/capital-config`
- Dust transfer: `POST /api/account/dust-transfer` (assets em JSON ou CSV)
- Asset dividend: `GET /api/account/asset-dividend?asset=BNB&limit=20`
- Convert transferable: `GET /api/account/convert-transferable?fromAsset=BTC&toAsset=USDT`
- P2P orders: `GET /api/account/p2p-orders?fiatSymbol=BRL&tradeType=BUY`

Trading:
```bash
# Criar ordem LIMIT
curl -X POST http://localhost:8000/api/trading/create-order ^
-H "Content-Type: application/json" ^
-d "{\"api_key\":\"...\",\"secret_key\":\"...\",\"symbol\":\"BTCUSDT\",\"side\":\"BUY\",\"type\":\"LIMIT\",\"quantity\":\"0.001\",\"price\":\"42000\",\"timeInForce\":\"GTC\"}"

# Criar ordem MARKET com quoteOrderQty
curl -X POST http://localhost:8000/api/trading/create-order ^
-H "Content-Type: application/json" ^
  -d "{\"api_key\":\"...\",\"secret_key\":\"...\",\"symbol\":\"BTCUSDT\",\"side\":\"BUY\",\"type\":\"MARKET\",\"quoteOrderQty\":\"50\"}"

# Cancelar ordem
curl -X DELETE http://localhost:8000/api/trading/cancel-order ^
-H "Content-Type: application/json" ^
-d "{\"api_key\":\"...\",\"secret_key\":\"...\",\"symbol\":\"BTCUSDT\",\"orderId\":\"123456\"}"

# Cancel/Replace (SOR)
curl -X POST http://localhost:8000/api/trading/cancel-replace ^
-H "Content-Type: application/json" ^
-d "{\"api_key\":\"...\",\"secret_key\":\"...\",\"symbol\":\"BTCUSDT\",\"side\":\"BUY\",\"type\":\"LIMIT\",\"quantity\":\"0.001\",\"price\":\"42000\",\"cancelOrderId\":\"123\",\"cancelReplaceMode\":\"STOP_ON_FAILURE\"}"

# Test order (não executa)
curl -X POST http://localhost:8000/api/trading/test-order ^
-H "Content-Type: application/json" ^
-d "{\"api_key\":\"...\",\"secret_key\":\"...\",\"symbol\":\"BTCUSDT\",\"side\":\"BUY\",\"type\":\"LIMIT\",\"quantity\":\"0.001\",\"price\":\"42000\"}"

# Consultar ordem
curl -X GET "http://localhost:8000/api/trading/query-order?api_key=...&secret_key=...&symbol=BTCUSDT&orderId=123"

# Cancelar todas as ordens abertas de um símbolo
curl -X DELETE http://localhost:8000/api/trading/cancel-open-orders ^
-H "Content-Type: application/json" ^
-d "{\"api_key\":\"...\",\"secret_key\":\"...\",\"symbol\":\"BTCUSDT\"}"

# Criar OCO
curl -X POST http://localhost:8000/api/trading/create-oco ^
-H "Content-Type: application/json" ^
-d "{\"api_key\":\"...\",\"secret_key\":\"...\",\"symbol\":\"BTCUSDT\",\"side\":\"SELL\",\"quantity\":\"0.001\",\"price\":\"50000\",\"stopPrice\":\"48000\",\"stopLimitPrice\":\"47900\"}"

# Listar OCOs
curl -X GET "http://localhost:8000/api/trading/list-oco?api_key=...&secret_key=...&limit=10"

# Cancelar OCO
curl -X DELETE http://localhost:8000/api/trading/cancel-oco ^
-H "Content-Type: application/json" ^
-d "{\"api_key\":\"...\",\"secret_key\":\"...\",\"orderListId\":\"12345\"}"

# Comissão e rate limit
curl -X GET "http://localhost:8000/api/trading/commission-rate?api_key=...&secret_key=...&symbol=BTCUSDT"
curl -X GET "http://localhost:8000/api/trading/order-rate-limit?api_key=...&secret_key=..."
```

## Campos extras em ordens
- MARKET: envie `quantity` **ou** `quoteOrderQty`.
- LIMIT/STOP_LIMIT/TAKE_PROFIT_LIMIT/LIMIT_MAKER: inclua `price` e `timeInForce` (default GTC).
- STOP/TAKE (com ou sem LIMIT): inclua `stopPrice`.

## Testes e qualidade
- Rodar testes (PHPUnit): `vendor/bin/phpunit` (controllers, router e helpers)
- Análise estática (PHPStan nível 6): `vendor/bin/phpstan analyse`

## Docker
- Subir local: `docker-compose up --build` (expõe em http://localhost:8080).
- Usa volume `./storage` para cache/ratelimit/logs.

## Endpoints adicionais
- Health: `GET /health` (checa storage).
- Métricas (se `METRICS_ENABLED=true`): `GET /metrics`.
- Correlação: header `X-Correlation-Id` aceito e devolvido como `X-Request-Id`.
