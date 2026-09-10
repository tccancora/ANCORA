-- ==================================================
-- ÂNCORA - Sistema de Gestão Acadêmica
-- Estrutura Inicial do Banco de Dados MySQL
-- ==================================================

-- 1. Criar o Banco de Dados se não existir
CREATE DATABASE IF NOT EXISTS ancora
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- 2. Selecionar o Banco de Dados
USE ancora;

-- 3. Tabela de Perfis
CREATE TABLE IF NOT EXISTS perfis (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Inserção dos Perfis Padrão
-- NOTA IMPORTANTE: O perfil Administrador representa o Diretor no sistema ÂNCORA.
INSERT INTO perfis (id, nome, descricao) VALUES
(1, 'Administrador', 'Administrador / Diretor com acesso total ao sistema'),
(2, 'Professor', 'Perfil com permissões para gestão de turmas, disciplinas e notas'),
(3, 'Aluno', 'Perfil de estudante para acesso a conteúdos e entregas'),
(4, 'Funcionario', 'Perfil de funcionário administrativo')
ON DUPLICATE KEY UPDATE nome = VALUES(nome), descricao = VALUES(descricao);

-- 5. Tabela de Instituições (Multi-Tenancy)
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

-- 6. Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil_id BIGINT UNSIGNED NOT NULL,
    instituicao_id BIGINT UNSIGNED NOT NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    primeiro_acesso TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_perfis
        FOREIGN KEY (perfil_id)
        REFERENCES perfis(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_usuarios_instituicoes
        FOREIGN KEY (instituicao_id)
        REFERENCES instituicoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabela de Recuperação de Senha (Password Resets)
CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(150) NOT NULL,
    codigo_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    used_at DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_usuarios
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabela de Turmas (Multi-Tenancy por Instituição)
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

-- 9. Tabela de Associação de Turmas e Professores (com Disciplina)
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

-- 10. Tabela de Associação de Turmas e Alunos
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

-- 11. Tabela de Notificações (Notificações e Avisos do Sistema)
CREATE TABLE IF NOT EXISTS notificacoes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instituicao_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    remetente_id BIGINT UNSIGNED NULL,
    tipo ENUM('Informativo', 'Alerta', 'Sucesso', 'Erro') NOT NULL DEFAULT 'Informativo',
    categoria VARCHAR(50) NOT NULL DEFAULT 'geral',
    titulo VARCHAR(150) NOT NULL,
    mensagem TEXT NOT NULL,
    link VARCHAR(255) NULL,
    lida TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_notificacoes_usuario_lida (usuario_id, lida, created_at),
    INDEX idx_notificacoes_instituicao (instituicao_id, created_at),
    CONSTRAINT fk_notificacoes_instituicao
        FOREIGN KEY (instituicao_id)
        REFERENCES instituicoes(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_notificacoes_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_notificacoes_remetente
        FOREIGN KEY (remetente_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- MÓDULO DE TAREFAS E ATIVIDADES
-- ==================================================

-- 12. Tabela Principal de Tarefas
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
    INDEX idx_tarefas_instituicao_criador (instituicao_id, created_by, deleted_at),
    INDEX idx_tarefas_prazo (prazo_entrega, status),
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

-- 13. Tabela de Destinatários da Tarefa (Turmas e/ou Alunos Específicos)
CREATE TABLE IF NOT EXISTS tarefa_destinatarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tarefa_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('turma', 'aluno') NOT NULL,
    turma_id BIGINT UNSIGNED NULL,
    aluno_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tdest_tarefa (tarefa_id),
    INDEX idx_tdest_turma (turma_id),
    INDEX idx_tdest_aluno (aluno_id),
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

-- 14. Tabela de Materiais de Apoio Anexados pelo Professor / Administrador
CREATE TABLE IF NOT EXISTS tarefa_materiais (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tarefa_id BIGINT UNSIGNED NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    caminho_arquivo VARCHAR(255) NOT NULL,
    tamanho_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    mime_type VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tmateriais_tarefa (tarefa_id),
    CONSTRAINT fk_tmateriais_tarefa
        FOREIGN KEY (tarefa_id)
        REFERENCES tarefas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Tabela de Questões do Questionário ÂNCORA
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
    INDEX idx_tquestoes_tarefa (tarefa_id, ordem),
    CONSTRAINT fk_tquestoes_tarefa
        FOREIGN KEY (tarefa_id)
        REFERENCES tarefas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Tabela de Entregas dos Alunos
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
    INDEX idx_tentregas_tarefa (tarefa_id, status),
    INDEX idx_tentregas_aluno (aluno_id),
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

-- 17. Tabela de Arquivos Anexados na Entrega pelo Aluno
CREATE TABLE IF NOT EXISTS tarefa_entrega_arquivos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entrega_id BIGINT UNSIGNED NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    caminho_arquivo VARCHAR(255) NOT NULL,
    tamanho_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    mime_type VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tarquivos_entrega (entrega_id),
    CONSTRAINT fk_tarquivos_entrega
        FOREIGN KEY (entrega_id)
        REFERENCES tarefa_entregas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Tabela de Respostas do Aluno às Questões do Questionário
CREATE TABLE IF NOT EXISTS tarefa_entrega_respostas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entrega_id BIGINT UNSIGNED NOT NULL,
    questao_id BIGINT UNSIGNED NOT NULL,
    resposta_texto TEXT NULL,
    resposta_selecao_json JSON NULL,
    pontos_obtidos DECIMAL(5,2) NULL DEFAULT NULL,
    comentario_professor TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_trespostas_entrega (entrega_id),
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


