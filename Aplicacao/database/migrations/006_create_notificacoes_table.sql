-- ==================================================
-- ÂNCORA - Migração 006: Tabela de Notificações
-- ==================================================

USE ancora;

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

-- Índices de Alta Performance para Consultas Rápidas no Polling
CREATE INDEX idx_notificacoes_usuario_lida ON notificacoes (usuario_id, lida, created_at);
CREATE INDEX idx_notificacoes_instituicao ON notificacoes (instituicao_id, created_at);
