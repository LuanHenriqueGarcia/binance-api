-- ================================================
-- Queries de Ordens (PostgreSQL)
-- Arquivo: sql/orders.sql
-- ================================================

-- @tag: find_by_id
-- Busca ordem por ID local
SELECT *
FROM orders
WHERE id = :id
LIMIT 1;

-- @tag: find_by_binance_id
-- Busca ordem pelo ID da Binance
SELECT *
FROM orders
WHERE binance_order_id = :binance_order_id
LIMIT 1;

-- @tag: list_by_symbol
-- Lista ordens por símbolo
SELECT *
FROM orders
WHERE symbol = :symbol
ORDER BY created_at DESC
LIMIT :limit;

-- @tag: list_open
-- Lista ordens abertas
SELECT *
FROM orders
WHERE status IN ('NEW', 'PARTIALLY_FILLED')
ORDER BY created_at DESC;

-- @tag: list_by_user
-- Lista ordens de um usuário
SELECT *
FROM orders
WHERE user_id = :user_id
ORDER BY created_at DESC
LIMIT :limit OFFSET :offset;

-- @tag: create
-- Registra uma nova ordem
INSERT INTO orders (
    user_id, binance_order_id, symbol, side, type,
    quantity, price, status, created_at, updated_at
) VALUES (
    :user_id, :binance_order_id, :symbol, :side, :type,
    :quantity, :price, :status, NOW(), NOW()
)
RETURNING id;

-- @tag: update_status
-- Atualiza status da ordem
UPDATE orders
SET status = :status,
    executed_qty = :executed_qty
WHERE binance_order_id = :binance_order_id;

-- @tag: delete
-- Remove ordem
DELETE FROM orders WHERE id = :id;

-- @tag: stats_by_symbol
-- Estatísticas por símbolo
SELECT
    symbol,
    COUNT(*) as total_orders,
    SUM(CASE WHEN side = 'BUY' THEN 1 ELSE 0 END) as buy_orders,
    SUM(CASE WHEN side = 'SELL' THEN 1 ELSE 0 END) as sell_orders,
    SUM(CASE WHEN status = 'FILLED' THEN 1 ELSE 0 END) as filled_orders
FROM orders
WHERE user_id = :user_id
GROUP BY symbol
ORDER BY total_orders DESC;

-- @tag: daily_summary
-- Resumo diário de ordens
SELECT
    DATE(created_at) as date,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'FILLED' THEN quantity * price ELSE 0 END) as volume
FROM orders
WHERE user_id = :user_id
  AND created_at >= :start_date
  AND created_at <= :end_date
GROUP BY DATE(created_at)
ORDER BY date DESC;
