-- ================================================
-- Queries de Logs de API (PostgreSQL)
-- Arquivo: sql/api_logs.sql
-- ================================================

-- @tag: create
-- Registra um log de chamada à API
INSERT INTO api_logs (
    endpoint, method, request_params, response_code,
    response_body, duration_ms, ip_address, created_at
) VALUES (
    :endpoint, :method, :request_params, :response_code,
    :response_body, :duration_ms, :ip_address, NOW()
)
RETURNING id;

-- @tag: list_recent
-- Lista logs recentes
SELECT *
FROM api_logs
ORDER BY created_at DESC
LIMIT :limit;

-- @tag: list_by_endpoint
-- Lista logs por endpoint
SELECT *
FROM api_logs
WHERE endpoint = :endpoint
ORDER BY created_at DESC
LIMIT :limit;

-- @tag: list_errors
-- Lista logs com erro (4xx e 5xx)
SELECT *
FROM api_logs
WHERE response_code >= 400
ORDER BY created_at DESC
LIMIT :limit;

-- @tag: stats_by_endpoint
-- Estatísticas por endpoint
SELECT
    endpoint,
    method,
    COUNT(*) as total_calls,
    AVG(duration_ms) as avg_duration,
    MAX(duration_ms) as max_duration,
    SUM(CASE WHEN response_code >= 400 THEN 1 ELSE 0 END) as errors
FROM api_logs
WHERE created_at >= :since
GROUP BY endpoint, method
ORDER BY total_calls DESC;

-- @tag: cleanup_old
-- Remove logs antigos
DELETE FROM api_logs
WHERE created_at < NOW() - INTERVAL '1 day' * :days;
