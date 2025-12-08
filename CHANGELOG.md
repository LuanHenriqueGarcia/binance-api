# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

## [1.0.0] - 2025-12-08

### Adicionado

#### Arquitetura
- **Contracts** - Interfaces para inversão de dependência
  - `ClientInterface` - Contrato para cliente HTTP
  - `CacheInterface` - Contrato para implementações de cache
  - `RateLimiterInterface` - Contrato para rate limiting
  - `LoggerInterface` - Contrato para logging

- **Enums** (PHP 8.1)
  - `OrderSide` - BUY, SELL
  - `OrderType` - LIMIT, MARKET, STOP_LOSS, etc.
  - `TimeInForce` - GTC, IOC, FOK
  - `KlineInterval` - 1m, 5m, 1h, 1d, etc.
  - `HttpStatus` - Códigos HTTP comuns

- **Exceptions** - Exceções customizadas
  - `BinanceException` - Base para todas as exceções
  - `ValidationException` - Erros de validação
  - `AuthenticationException` - Erros de autenticação
  - `RateLimitException` - Rate limit excedido
  - `NetworkException` - Erros de conexão
  - `OrderException` - Erros de ordem/trading

- **DTOs** - Data Transfer Objects
  - `OrderDTO` - Estrutura de dados para ordens
  - `TickerDTO` - Estrutura de dados para ticker
  - `BalanceDTO` - Estrutura de dados para saldo

- **Helpers**
  - `ArrayHelper` - Funções para manipulação de arrays
  - `Formatter` - Formatação de dados (moeda, datas, etc.)

- **HTTP Layer**
  - `Request` - Encapsulamento de requisição HTTP
  - `Response` - Padronização de respostas
  - Middlewares: Auth, RateLimit, Logging, CORS

- **Container** - Injeção de dependências simplificada
- **FileCache** - Implementação de cache em arquivos
- **BaseController** - Controller base com helpers comuns

#### Endpoints
- **General**: ping, time, exchange-info
- **Market**: ticker, order-book, trades, klines, avg-price, etc.
- **Account**: info, balance, open-orders, order-history, my-trades, etc.
- **Trading**: create-order, cancel-order, test-order, OCO, etc.
- **Sistema**: health, metrics

#### Infraestrutura
- Docker e docker-compose
- PHPUnit com 43 testes
- PHPStan level 5/6
- Insomnia collection

### Segurança
- Rate limiting por IP/endpoint
- Basic Auth opcional
- Mascaramento de dados sensíveis em logs
- Suporte a correlation ID

---

## [Unreleased]

### Planejado
- [ ] WebSocket para streams em tempo real
- [ ] Suporte a Futures API
- [ ] Margin Trading endpoints
- [ ] Redis como alternativa ao file cache
- [ ] Swagger/OpenAPI documentation
