-- ==================================================
-- MIGRATION: Adicionar coluna primeiro_acesso na tabela usuarios
-- DATA: 2026-08-13
-- ==================================================

USE ancora;

-- Adiciona a coluna primeiro_acesso caso ainda não exista
SET @dbname = DATABASE();
SET @tablename = "usuarios";
SET @columnname = "primeiro_acesso";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE usuarios ADD COLUMN primeiro_acesso TINYINT(1) NOT NULL DEFAULT 1 AFTER status;"
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
