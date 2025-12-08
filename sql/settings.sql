-- ================================================
-- Queries de Configurações (PostgreSQL)
-- Arquivo: sql/settings.sql
-- ================================================

-- @tag: get
-- Obtém uma configuração por chave
SELECT value
FROM settings
WHERE key = :key
LIMIT 1;

-- @tag: set
-- Define ou atualiza uma configuração (UPSERT)
INSERT INTO settings (key, value, updated_at)
VALUES (:key, :value, NOW())
ON CONFLICT (key)
DO UPDATE SET value = EXCLUDED.value, updated_at = NOW();

-- @tag: delete
-- Remove uma configuração
DELETE FROM settings WHERE key = :key;

-- @tag: list_all
-- Lista todas as configurações
SELECT key, value, updated_at
FROM settings
ORDER BY key ASC;

-- @tag: list_by_prefix
-- Lista configurações por prefixo
SELECT key, value, updated_at
FROM settings
WHERE key LIKE :prefix
ORDER BY key ASC;
