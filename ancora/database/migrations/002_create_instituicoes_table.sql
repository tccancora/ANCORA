-- ==================================================
-- MIGRATION: Criar tabela instituicoes
-- DATA: 2026-08-14
-- ==================================================

USE ancora;

CREATE TABLE IF NOT EXISTS instituicoes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cnpj VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    telefone VARCHAR(20) NULL,
    cidade VARCHAR(100) NULL,
    estado VARCHAR(50) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
