-- ==================================================
-- ÂNCORA - Migração 007: Módulo Completo de Tarefas
-- ==================================================

USE ancora;

-- 1. Tabela Principal de Tarefas
CREATE TABLE IF NOT EXISTS tarefas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instituicao_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT NULL,
    disciplina VARCHAR(100) NULL,
    tipo_atividade ENUM('tradicional', 'questionario', 'hibrida') NOT NULL DEFAULT 'tradicional',
    permite_anexo_aluno TINYINT(1) NOT NULL DEFAULT 1,
    disponivel_em DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    prazo_entrega DATETIME NOT NULL,
    status ENUM('rascunho', 'publicada', 'encerrada') NOT NULL DEFAULT 'publicada',
    deleted_at DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tarefas_instituicao
        FOREIGN KEY (instituicao_id)
        REFERENCES instituicoes(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_tarefas_criador
        FOREIGN KEY (created_by)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices de Alta Performance para Tarefas
CREATE INDEX idx_tarefas_instituicao_criador ON tarefas (instituicao_id, created_by, deleted_at);
CREATE INDEX idx_tarefas_prazo ON tarefas (prazo_entrega, status);

-- 2. Tabela de Destinatários da Tarefa (Turmas e/ou Alunos Específicos)
CREATE TABLE IF NOT EXISTS tarefa_destinatarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tarefa_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('turma', 'aluno') NOT NULL,
    turma_id BIGINT UNSIGNED NULL,
    aluno_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tdest_tarefa
        FOREIGN KEY (tarefa_id)
        REFERENCES tarefas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_tdest_turma
        FOREIGN KEY (turma_id)
        REFERENCES turmas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_tdest_aluno
        FOREIGN KEY (aluno_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_tdest_tarefa ON tarefa_destinatarios (tarefa_id);
CREATE INDEX idx_tdest_turma ON tarefa_destinatarios (turma_id);
CREATE INDEX idx_tdest_aluno ON tarefa_destinatarios (aluno_id);

-- 3. Tabela de Materiais de Apoio Anexados pelo Professor / Administrador
CREATE TABLE IF NOT EXISTS tarefa_materiais (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tarefa_id BIGINT UNSIGNED NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    caminho_arquivo VARCHAR(255) NOT NULL,
    tamanho_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    mime_type VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tmateriais_tarefa
        FOREIGN KEY (tarefa_id)
        REFERENCES tarefas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_tmateriais_tarefa ON tarefa_materiais (tarefa_id);

-- 4. Tabela de Questões do Questionário ÂNCORA
CREATE TABLE IF NOT EXISTS tarefa_questoes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tarefa_id BIGINT UNSIGNED NOT NULL,
    ordem INT UNSIGNED NOT NULL DEFAULT 1,
    enunciado TEXT NOT NULL,
    tipo ENUM('multipla_escolha', 'verdadeiro_falso', 'resposta_curta', 'discursiva', 'multipla_selecao') NOT NULL,
    pontos DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    obrigatoria TINYINT(1) NOT NULL DEFAULT 1,
    alternativas_json JSON NULL,
    resposta_correta_json JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tquestoes_tarefa
        FOREIGN KEY (tarefa_id)
        REFERENCES tarefas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_tquestoes_tarefa ON tarefa_questoes (tarefa_id, ordem);

-- 5. Tabela de Entregas dos Alunos
CREATE TABLE IF NOT EXISTS tarefa_entregas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tarefa_id BIGINT UNSIGNED NOT NULL,
    aluno_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pendente', 'entregue', 'corrigida', 'devolvida') NOT NULL DEFAULT 'entregue',
    nota DECIMAL(5,2) NULL DEFAULT NULL,
    nota_maxima DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    feedback_geral TEXT NULL,
    entregue_em DATETIME NOT NULL,
    corrigida_em DATETIME NULL DEFAULT NULL,
    corrigida_por BIGINT UNSIGNED NULL,
    devolvida_em DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_tarefa_aluno_entrega UNIQUE (tarefa_id, aluno_id),
    CONSTRAINT fk_tentregas_tarefa
        FOREIGN KEY (tarefa_id)
        REFERENCES tarefas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_tentregas_aluno
        FOREIGN KEY (aluno_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_tentregas_corretor
        FOREIGN KEY (corrigida_por)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_tentregas_tarefa ON tarefa_entregas (tarefa_id, status);
CREATE INDEX idx_tentregas_aluno ON tarefa_entregas (aluno_id);

-- 6. Tabela de Arquivos Anexados na Entrega pelo Aluno
CREATE TABLE IF NOT EXISTS tarefa_entrega_arquivos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entrega_id BIGINT UNSIGNED NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    caminho_arquivo VARCHAR(255) NOT NULL,
    tamanho_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    mime_type VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tarquivos_entrega
        FOREIGN KEY (entrega_id)
        REFERENCES tarefa_entregas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_tarquivos_entrega ON tarefa_entrega_arquivos (entrega_id);

-- 7. Tabela de Respostas do Aluno às Questões do Questionário
CREATE TABLE IF NOT EXISTS tarefa_entrega_respostas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entrega_id BIGINT UNSIGNED NOT NULL,
    questao_id BIGINT UNSIGNED NOT NULL,
    resposta_texto TEXT NULL,
    resposta_selecao_json JSON NULL,
    pontos_obtidos DECIMAL(5,2) NULL DEFAULT NULL,
    comentario_professor TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_trespostas_entrega
        FOREIGN KEY (entrega_id)
        REFERENCES tarefa_entregas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_trespostas_questao
        FOREIGN KEY (questao_id)
        REFERENCES tarefa_questoes(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_trespostas_entrega ON tarefa_entrega_respostas (entrega_id);
