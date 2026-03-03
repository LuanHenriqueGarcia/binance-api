# Binance API Monorepo

Monorepo com:
- `api/`: API REST em PHP para Binance e Coinbase.
- `web/`: frontend Next.js (dashboard de operacao e diagnostico).

## Portas padrao

- Frontend: `http://localhost:3000`
- API: `http://localhost:8080/api`
- PostgreSQL (docker): `localhost:5432`

## Requisitos

### Para API (local sem Docker)
- PHP 8.2+
- Composer
- Extensoes PHP: `curl`, `openssl`, `json`, `bcmath`, `pdo`, `pdo_sqlite`, `pdo_pgsql`

### Para Frontend
- Node 20+
- pnpm

## Setup rapido

### 1) API com Docker (recomendado)

```bash
cd api
docker-compose up --build -d
```

A API sobe em `http://localhost:8080/api`.

### 2) Frontend

```bash
cd web
cp .env.local.example .env.local
pnpm install
pnpm dev
```

Frontend em `http://localhost:3000`.

## Variaveis de ambiente

### Frontend (`web/.env.local`)

```env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8080/api
```

### API (`api/.env`)

Pode usar `api/.env.example` como base.

Principais chaves:
- `APP_ENV`, `APP_DEBUG`
- `BASIC_AUTH_USER`, `BASIC_AUTH_PASSWORD` (opcional)
- `BINANCE_API_KEY`, `BINANCE_SECRET_KEY` (endpoints privados Binance)
- `COINBASE_API_KEY`, `COINBASE_API_SECRET`, `COINBASE_KEY_FILE` (endpoints privados Coinbase)
- `CORS_ALLOWED_ORIGINS` (padrao: `http://localhost:3000`)
- `CORS_ALLOWED_METHODS`
- `CORS_ALLOWED_HEADERS`
- `METRICS_ENABLED`
- `RATE_LIMIT_ENABLED`, `RATE_LIMIT_MAX`, `RATE_LIMIT_WINDOW`

## CORS e autenticacao

- A API responde preflight `OPTIONS` com `204`.
- Headers CORS enviados:
  - `Access-Control-Allow-Origin`
  - `Access-Control-Allow-Methods`
  - `Access-Control-Allow-Headers`
- O frontend usa `Authorization: Basic ...` quando usuario/senha forem preenchidos.
- O frontend **nao** envia cookies (`credentials: 'include'` removido).

## Banco de dados: decisao do projeto

- Execucao padrao: PostgreSQL no `docker-compose` da API.
- Testes locais: SQLite em memoria (mais rapido e sem dependencia externa).

## Validacoes de qualidade

### Backend

```bash
cd api
composer install
composer test
composer stan
```

### Frontend

```bash
cd web
pnpm install
pnpm exec tsc --noEmit
pnpm lint
pnpm build
```

## Smoke test manual

Com API no ar (`http://localhost:8080/api`):

```bash
# Infra
curl -i http://localhost:8080/api/health
curl -i http://localhost:8080/api/metrics

# Binance publicos
curl -i http://localhost:8080/api/general/ping
curl -i "http://localhost:8080/api/market/ticker?symbol=BTCUSDT"

# Coinbase publicos
curl -i http://localhost:8080/api/coinbase/general/time
curl -i "http://localhost:8080/api/coinbase/market/product?product_id=BTC-USD"
```

Para endpoints privados, inclua Basic Auth (se habilitado) e credenciais da exchange no request.

## Estrutura util

- Backend entrypoint: `api/index.php`
- Router: `api/src/Router.php`
- Clients: `api/src/BinanceClient.php`, `api/src/CoinbaseClient.php`
- Front API client: `web/lib/api-client.ts`
- Dashboard pages: `web/app/(dashboard)/*`
