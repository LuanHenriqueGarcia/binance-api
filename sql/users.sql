-- ================================================
-- Queries de Usuários (PostgreSQL)
-- Arquivo: sql/users.sql
-- ================================================

-- @tag: find_by_id
-- Busca usuário por ID
SELECT id, name, email, created_at, updated_at
FROM users
WHERE id = :id
LIMIT 1;

-- @tag: find_by_email
-- Busca usuário por email
SELECT id, name, email, created_at, updated_at
FROM users
WHERE email = :email
LIMIT 1;

-- @tag: list_all
-- Lista todos os usuários
SELECT id, name, email, created_at, updated_at
FROM users
ORDER BY created_at DESC;

-- @tag: list_active
-- Lista usuários ativos
SELECT id, name, email, created_at, updated_at
FROM users
WHERE active = TRUE
ORDER BY name ASC;

-- @tag: paginate
-- Lista usuários com paginação
SELECT id, name, email, created_at, updated_at
FROM users
ORDER BY created_at DESC
LIMIT :limit OFFSET :offset;

-- @tag: count
-- Conta total de usuários
SELECT COUNT(*) as total FROM users;

-- @tag: create
-- Cria um novo usuário
INSERT INTO users (name, email, password, created_at, updated_at)
VALUES (:name, :email, :password, NOW(), NOW())
RETURNING id;

-- @tag: update
-- Atualiza um usuário
UPDATE users
SET name = :name, email = :email
WHERE id = :id;

-- @tag: update_password
-- Atualiza a senha do usuário
UPDATE users
SET password = :password
WHERE id = :id;

-- @tag: delete
-- Remove um usuário
DELETE FROM users WHERE id = :id;

-- @tag: soft_delete
-- Soft delete de usuário
UPDATE users
SET deleted_at = NOW()
WHERE id = :id;

-- @tag: search
-- Busca usuários por nome ou email
SELECT id, name, email, created_at
FROM users
WHERE name ILIKE :search OR email ILIKE :search
ORDER BY name ASC
LIMIT :limit;
