-- ==================================================
-- MIGRATION: Adicionar coluna e FK instituicao_id na tabela usuarios
-- DATA: 2026-08-14
-- ==================================================

USE ancora;

-- 1. Garante que exista pelo menos uma instituição padrão no sistema
INSERT INTO instituicoes (id, nome, cnpj, email, cidade, estado) 
VALUES (1, 'Instituição ÂNCORA Principal', '00.000.000/0001-00', 'contato@ancora.edu.br', 'São Paulo', 'SP')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- 2. Adiciona a coluna instituicao_id se não existir
SET @dbname = DATABASE();
SET @tablename = "usuarios";
SET @columnname = "instituicao_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE usuarios ADD COLUMN instituicao_id BIGINT UNSIGNED NULL AFTER perfil_id;"
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. Associa todos os usuários sem instituição à instituição padrão 1
UPDATE usuarios SET instituicao_id = 1 WHERE instituicao_id IS NULL;

-- 4. Modifica a coluna para NOT NULL
ALTER TABLE usuarios MODIFY COLUMN instituicao_id BIGINT UNSIGNED NOT NULL;

-- 5. Adiciona a Chave Estrangeira se não existir
SET @fkPrepared = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      CONSTRAINT_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND CONSTRAINT_NAME = "fk_usuarios_instituicoes"
  ) > 0,
  "SELECT 1",
  "ALTER TABLE usuarios ADD CONSTRAINT fk_usuarios_instituicoes FOREIGN KEY (instituicao_id) REFERENCES instituicoes(id) ON UPDATE CASCADE ON DELETE RESTRICT;"
));

PREPARE addFkIfNotExists FROM @fkPrepared;
EXECUTE addFkIfNotExists;
DEALLOCATE PREPARE addFkIfNotExists;
