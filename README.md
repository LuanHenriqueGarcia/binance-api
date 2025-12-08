#  Binance API PHP

<div align="center">

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Binance](https://img.shields.io/badge/Binance-API-F0B90B?style=for-the-badge&logo=binance&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**API REST em PHP para integração com a Binance — Zero dependências externas**

[Instalação](#-instalação) •
[Endpoints](#-endpoints) •
[Configuração](#️-configuração) •
[Docker](#-docker) •
[Arquitetura](#-arquitetura)

</div>

---

##  Features

-  **Autenticação HMAC-SHA256** com suporte a API Keys da Binance
-  **Market Data** - Preços, Order Book, Trades, Klines
-  **Conta** - Saldos, Histórico de Ordens, Status
-  **Trading** - Criar/Cancelar Ordens, OCO, Test Orders
-  **Cache inteligente** para Exchange Info
-  **Rate Limiting** configurável por IP/endpoint
-  **Logging** estruturado em JSON com mascaramento de dados sensíveis
-  **Métricas** de latência e status HTTP
-  **Docker Ready** com docker-compose
-  **Testes** com PHPUnit + PHPStan (Level 6)

---

##  Instalação

### Requisitos
- PHP 8.1+ com extensão cURL habilitada
- Composer (opcional, para dev tools)

### Quick Start

```bash
# Clone o repositório
git clone https://github.com/LuanHenriqueGarcia/binance-api-php.git
cd binance-api-php

# Copie o .env.example para .env e configure
cp .env.example .env

# (Opcional) Instale dependências de desenvolvimento
composer install

# Inicie o servidor
php -S localhost:8000 -t .
```

A API estará disponível em `http://localhost:8000`

---

##  Arquitetura

```
binance-api-php/
├── src/
│   ├── Controllers/          # Controllers da aplicação
│   │   ├── BaseController.php    # Controller base com helpers
│   │   ├── GeneralController.php
│   │   ├── MarketController.php
│   │   ├── AccountController.php
│   │   └── TradingController.php
│   │
│   ├── Contracts/            # Interfaces (Dependency Inversion)
│   │   ├── ClientInterface.php
│   │   ├── CacheInterface.php
│   │   ├── RateLimiterInterface.php
│   │   └── LoggerInterface.php
│   │
│   ├── DTO/                  # Data Transfer Objects
│   │   ├── OrderDTO.php
│   │   ├── TickerDTO.php
│   │   └── BalanceDTO.php
│   │
│   ├── Enums/                # Enums PHP 8.1
│   │   ├── OrderSide.php
│   │   ├── OrderType.php
│   │   ├── TimeInForce.php
│   │   ├── KlineInterval.php
│   │   └── HttpStatus.php
│   │
│   ├── Exceptions/           # Exceções customizadas
│   │   ├── BinanceException.php
│   │   ├── ValidationException.php
│   │   ├── AuthenticationException.php
│   │   ├── RateLimitException.php
│   │   ├── NetworkException.php
│   │   └── OrderException.php
│   │
│   ├── Helpers/              # Funções auxiliares
│   │   ├── ArrayHelper.php
│   │   └── Formatter.php
│   │
│   ├── Http/                 # Camada HTTP
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── Middleware/
│   │       ├── MiddlewareInterface.php
│   │       ├── AuthMiddleware.php
│   │       ├── RateLimitMiddleware.php
│   │       ├── LoggingMiddleware.php
│   │       └── CorsMiddleware.php
│   │
│   ├── BinanceClient.php     # Cliente HTTP para Binance
│   ├── Cache.php             # Cache em arquivo
│   ├── Config.php            # Configurações (.env)
│   ├── Container.php         # DI Container simples
│   ├── FileCache.php         # Implementação CacheInterface
│   ├── Logger.php            # Logger JSON
│   ├── Metrics.php           # Métricas de performance
│   ├── RateLimiter.php       # Rate limiting por arquivo
│   ├── Router.php            # Router da aplicação
│   └── Validation.php        # Validação de parâmetros
│
├── storage/                  # Dados persistidos
│   ├── cache/
│   ├── logs/
│   └── ratelimit/
│
├── tests/                    # Testes PHPUnit
├── docker/                   # Configurações Docker
├── binance-front/            # Frontend React (opcional)
├── index.php                 # Entry point
├── composer.json
├── docker-compose.yml
├── phpstan.neon
└── phpunit.xml
```

---

## Configuração

Crie um arquivo `.env` na raiz do projeto:

```env
# Binance API Keys (opcional - pode passar via parâmetros)
BINANCE_API_KEY=your_api_key
BINANCE_SECRET_KEY=your_secret_key

# Base URL (opcional - padrão: https://api.binance.com)
BINANCE_BASE_URL=https://api.binance.com
# Ou use testnet:
BINANCE_TESTNET=true

# SSL (produção: sempre true)
BINANCE_SSL_VERIFY=true
BINANCE_CA_BUNDLE=/path/to/ca-bundle.crt

# Timing
BINANCE_RECV_WINDOW=5000

# Basic Auth (opcional)
BASIC_AUTH_USER=admin
BASIC_AUTH_PASSWORD=secret

# Rate Limiting
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60

# Cache
CACHE_EXCHANGEINFO_TTL=30

# Storage
STORAGE_PATH=storage/

# Logging (somente em debug)
APP_DEBUG=false
APP_LOG_FILE=storage/logs/app.log

# Métricas
METRICS_ENABLED=true
```

---

##  Endpoints

###  General

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `GET` | `/api/general/ping` | Testa conectividade |
| `GET` | `/api/general/time` | Hora do servidor Binance |
| `GET` | `/api/general/exchange-info` | Info de câmbio e símbolos |

###  Market Data (Públicos)

| Método | Endpoint | Parâmetros | Descrição |
|--------|----------|------------|-----------|
| `GET` | `/api/market/ticker` | `symbol` | Preço atual 24h |
| `GET` | `/api/market/ticker-price` | `symbol?` | Preço simples |
| `GET` | `/api/market/ticker-24h` | `symbol?` | Ticker 24h completo |
| `GET` | `/api/market/order-book` | `symbol`, `limit?` | Livro de ofertas |
| `GET` | `/api/market/trades` | `symbol`, `limit?` | Trades recentes |
| `GET` | `/api/market/agg-trades` | `symbol`, `limit?` | Trades agregados |
| `GET` | `/api/market/klines` | `symbol`, `interval`, `limit?` | Candlesticks |
| `GET` | `/api/market/avg-price` | `symbol` | Preço médio 5min |
| `GET` | `/api/market/book-ticker` | `symbol?` | Best bid/ask |

**Intervalos de Klines:** `1s`, `1m`, `3m`, `5m`, `15m`, `30m`, `1h`, `2h`, `4h`, `6h`, `8h`, `12h`, `1d`, `3d`, `1w`, `1M`

###  Account (Autenticados)

| Método | Endpoint | Parâmetros | Descrição |
|--------|----------|------------|-----------|
| `GET` | `/api/account/info` | - | Informações da conta |
| `GET` | `/api/account/balance` | `asset` | Saldo de um ativo |
| `GET` | `/api/account/open-orders` | `symbol?` | Ordens abertas |
| `GET` | `/api/account/order-history` | `symbol`, `limit?` | Histórico de ordens |
| `GET` | `/api/account/my-trades` | `symbol`, `limit?` | Trades executados |
| `GET` | `/api/account/account-status` | - | Status da conta |
| `GET` | `/api/account/api-trading-status` | - | Status de trading |
| `GET` | `/api/account/capital-config` | - | Config de capital |

###  Trading (Autenticados)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `POST` | `/api/trading/create-order` | Criar ordem |
| `POST` | `/api/trading/test-order` | Testar ordem (não executa) |
| `DELETE` | `/api/trading/cancel-order` | Cancelar ordem |
| `DELETE` | `/api/trading/cancel-open-orders` | Cancelar todas de um símbolo |
| `GET` | `/api/trading/query-order` | Consultar ordem |
| `POST` | `/api/trading/create-oco` | Criar ordem OCO |
| `GET` | `/api/trading/list-oco` | Listar OCOs |
| `DELETE` | `/api/trading/cancel-oco` | Cancelar OCO |
| `POST` | `/api/trading/cancel-replace` | Cancel e cria nova ordem |

###  Sistema

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `GET` | `/health` | Health check |
| `GET` | `/metrics` | Métricas (se habilitado) |

---

##  Exemplos de Uso

### Criar Ordem LIMIT

```bash
curl -X POST http://localhost:8000/api/trading/create-order \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "sua_api_key",
    "secret_key": "sua_secret_key",
    "symbol": "BTCUSDT",
    "side": "BUY",
    "type": "LIMIT",
    "quantity": "0.001",
    "price": "42000",
    "timeInForce": "GTC"
  }'
```

### Criar Ordem MARKET

```bash
curl -X POST http://localhost:8000/api/trading/create-order \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "sua_api_key",
    "secret_key": "sua_secret_key",
    "symbol": "BTCUSDT",
    "side": "BUY",
    "type": "MARKET",
    "quoteOrderQty": "50"
  }'
```

### Consultar Saldo

```bash
curl "http://localhost:8000/api/account/balance?api_key=...&secret_key=...&asset=USDT"
```

### Resposta de Exemplo

```json
{
  "success": true,
  "data": {
    "asset": "USDT",
    "free": "1234.56789000",
    "locked": "100.00000000",
    "total": "1334.56789000"
  }
}
```

---

##  Docker

### Usando docker-compose

```bash
# Build e inicia os containers
docker-compose up --build

# Modo background
docker-compose up -d

# Ver logs
docker-compose logs -f
```

A API estará disponível em `http://localhost:8080`

### Build manual

```bash
docker build -t binance-api-php .
docker run -p 8000:80 -v ./storage:/var/www/html/storage binance-api-php
```

---

##  Testes

```bash
# Executar todos os testes
vendor/bin/phpunit

# Com coverage
vendor/bin/phpunit --coverage-html coverage/

# Análise estática (PHPStan Level 6)
vendor/bin/phpstan analyse
```

---

##  Tipos de Ordem

| Tipo | Parâmetros Obrigatórios |
|------|------------------------|
| `LIMIT` | `quantity`, `price`, `timeInForce` |
| `MARKET` | `quantity` OU `quoteOrderQty` |
| `STOP_LOSS` | `quantity`, `stopPrice` |
| `STOP_LOSS_LIMIT` | `quantity`, `price`, `stopPrice`, `timeInForce` |
| `TAKE_PROFIT` | `quantity`, `stopPrice` |
| `TAKE_PROFIT_LIMIT` | `quantity`, `price`, `stopPrice`, `timeInForce` |
| `LIMIT_MAKER` | `quantity`, `price` |

### Time In Force

| Valor | Descrição |
|-------|-----------|
| `GTC` | Good Till Canceled - ativa até cancelamento |
| `IOC` | Immediate Or Cancel - executa imediatamente ou cancela |
| `FOK` | Fill Or Kill - executa totalmente ou cancela |

---

##  Segurança

- ⚠️ **Nunca** commite suas API Keys
- Use variáveis de ambiente ou `.env` (já no `.gitignore`)
- Em produção, sempre use `BINANCE_SSL_VERIFY=true`
- Habilite `RATE_LIMIT_ENABLED=true` para proteção contra abuso
- Configure `BASIC_AUTH_USER/PASSWORD` para proteção adicional

---

##  Contribuindo

1. Fork o projeto
2. Crie sua feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add: nova feature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

---

##  Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

<div align="center">

**Desenvolvido com ☕ e PHP**

⭐ Star este repositório se foi útil!

</div>
