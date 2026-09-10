-- ==================================================
-- MIGRATION: Criar tabelas para o Módulo de Gestão de Turmas (Turmas, Turma_Professores, Turma_Alunos)
-- DATA: 2026-08-20
-- ==================================================

USE ancora;

-- 1. Tabela de Turmas escopada por Instituição (Multi-Tenancy)
CREATE TABLE IF NOT EXISTS turmas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    instituicao_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_turmas_instituicoes
        FOREIGN KEY (instituicao_id)
        REFERENCES instituicoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT uq_turmas_nome_instituicao UNIQUE (nome, instituicao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela de Associação entre Turmas e Professores (com Disciplina opcional)
CREATE TABLE IF NOT EXISTS turma_professores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    turma_id BIGINT UNSIGNED NOT NULL,
    professor_id BIGINT UNSIGNED NOT NULL,
    disciplina VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_turma_professores_turma
        FOREIGN KEY (turma_id)
        REFERENCES turmas(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_turma_professores_professor
        FOREIGN KEY (professor_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE,
    CONSTRAINT uq_turma_professor_disciplina UNIQUE (turma_id, professor_id, disciplina)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela de Associação entre Turmas e Alunos
CREATE TABLE IF NOT EXISTS turma_alunos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    turma_id BIGINT UNSIGNED NOT NULL,
    aluno_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_turma_alunos_turma
        FOREIGN KEY (turma_id)
        REFERENCES turmas(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_turma_alunos_aluno
        FOREIGN KEY (aluno_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE,
    CONSTRAINT uq_turma_aluno UNIQUE (turma_id, aluno_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
