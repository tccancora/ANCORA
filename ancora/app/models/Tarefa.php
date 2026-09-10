<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Model Tarefa (Módulo Completo de Gestão de Atividades Acadêmicas)
 * 
 * REGRAS DE NEGÓCIO E SEGURANÇA (TCC):
 * 1. PRIVACIDADE E PROPRIEDADE: Professor e Administrador só visualizam/editam tarefas que criaram.
 * 2. MULTI-TENANCY: Isolamento estrito por instituicao_id em 100% das operações.
 * 3. PRAZOS ESTRITOS: Validação no servidor no momento da submissão.
 * 4. DEDUPLICAÇÃO: Consolidação de destinatários por turma e individuais para evitar duplicidade de tarefas e notificações.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../services/NotificationService.php';

class Tarefa {

    private static function getDb(): PDO {
        return getDatabaseConnection();
    }

    /**
     * Lista todas as tarefas criadas pelo usuário (Professor ou Administrador).
     * Aplica filtro estrito de propriedade (created_by) e instituição (instituicao_id).
     */
    public static function listarCriadasPorUsuario(
        int $userId, 
        int $instituicaoId, 
        ?string $busca = null, 
        ?string $statusFiltro = null, 
        ?string $turmaFiltro = null, 
        string $ordenacao = 'recentes'
    ): array {
        $db = self::getDb();

        $sql = "
            SELECT t.*, 
                   u.nome as criador_nome,
                   u.perfil_id as criador_perfil_id,
                   (CASE 
                       WHEN NOW() > t.prazo_entrega THEN 1 
                       ELSE 0 
                    END) as is_atrasada,
                   (CASE 
                       WHEN t.disponivel_em IS NOT NULL AND NOW() < t.disponivel_em THEN 'futura'
                       WHEN NOW() > t.prazo_entrega THEN 'encerrada'
                       ELSE 'aberta'
                    END) as estado_prazo
            FROM tarefas t
            JOIN usuarios u ON t.created_by = u.id
            WHERE t.instituicao_id = :instituicao_id 
              AND t.created_by = :created_by
              AND t.deleted_at IS NULL
        ";

        $params = [
            ':instituicao_id' => $instituicaoId,
            ':created_by'     => $userId
        ];

        if (!empty($busca)) {
            $sql .= " AND (LOWER(t.titulo) LIKE :busca OR LOWER(t.descricao) LIKE :busca OR LOWER(t.disciplina) LIKE :busca)";
            $params[':busca'] = '%' . mb_strtolower(trim($busca)) . '%';
        }

        if (!empty($statusFiltro) && $statusFiltro !== 'todos') {
            if ($statusFiltro === 'atrasada') {
                $sql .= " AND NOW() > t.prazo_entrega";
            } elseif ($statusFiltro === 'aberta' || $statusFiltro === 'pendente') {
                $sql .= " AND (t.disponivel_em IS NULL OR NOW() >= t.disponivel_em) AND NOW() <= t.prazo_entrega";
            } elseif ($statusFiltro === 'encerrada') {
                $sql .= " AND NOW() > t.prazo_entrega";
            }
        }

        if (!empty($turmaFiltro) && $turmaFiltro !== 'todas') {
            $sql .= " AND t.id IN (
                SELECT td.tarefa_id FROM tarefa_destinatarios td 
                WHERE td.tipo = 'turma' AND td.turma_id = :turma_id
            )";
            $params[':turma_id'] = (int) $turmaFiltro;
        }

        if ($ordenacao === 'antigas') {
            $sql .= " ORDER BY t.created_at ASC, t.id ASC";
        } else {
            $sql .= " ORDER BY t.created_at DESC, t.id DESC";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tarefas as &$tarefa) {
            $tId = (int)$tarefa['id'];
            $tarefa['destinatarios_resumo'] = self::obterResumoDestinatariosTexto($tId);
            $tarefa['resumo_entregas']      = self::obterResumoEntregas($tId, $instituicaoId);
            $tarefa['prazo_formatado']      = self::formatarPrazo($tarefa['prazo_entrega']);
            $tarefa['materiais_count']      = self::contarMateriais($tId);
            $tarefa['questoes_count']       = self::contarQuestoes($tId);
        }

        return $tarefas;
    }

    /**
     * Lista as tarefas destinadas a um aluno específico (via turma ou individual).
     * Aplica isolamento de instituição e permissão de aluno.
     */
    public static function listarDestinadasAoAluno(
        int $alunoId, 
        int $instituicaoId, 
        ?string $busca = null, 
        ?string $statusFiltro = null, 
        ?string $turmaFiltro = null, 
        string $ordenacao = 'recentes'
    ): array {
        $db = self::getDb();

        $sql = "
            SELECT t.*, 
                   u.nome as criador_nome,
                   te.id as entrega_id,
                   te.status as entrega_status,
                   te.nota as entrega_nota,
                   te.nota_maxima as entrega_nota_maxima,
                   te.feedback_geral as entrega_feedback,
                   te.entregue_em as entrega_data,
                   te.devolvida_em as entrega_devolvida_data,
                   (CASE 
                       WHEN NOW() > t.prazo_entrega THEN 1 
                       ELSE 0 
                    END) as is_atrasada,
                   (CASE 
                       WHEN t.disponivel_em IS NOT NULL AND NOW() < t.disponivel_em THEN 'futura'
                       WHEN NOW() > t.prazo_entrega THEN 'encerrada'
                       ELSE 'aberta'
                    END) as estado_prazo
            FROM tarefas t
            JOIN usuarios u ON t.created_by = u.id
            LEFT JOIN tarefa_entregas te ON (te.tarefa_id = t.id AND te.aluno_id = :aluno_id_entrega)
            WHERE t.instituicao_id = :instituicao_id 
              AND t.status != 'rascunho'
              AND t.deleted_at IS NULL
              AND (
                  -- Destinada individualmente ao aluno
                  t.id IN (SELECT td1.tarefa_id FROM tarefa_destinatarios td1 WHERE td1.tipo = 'aluno' AND td1.aluno_id = :aluno_id_dest1)
                  OR
                  -- Destinada à turma do aluno
                  t.id IN (
                      SELECT td2.tarefa_id FROM tarefa_destinatarios td2 
                      JOIN turma_alunos ta ON td2.turma_id = ta.turma_id 
                      WHERE td2.tipo = 'turma' AND ta.aluno_id = :aluno_id_dest2
                  )
              )
        ";

        $params = [
            ':instituicao_id'      => $instituicaoId,
            ':aluno_id_entrega'   => $alunoId,
            ':aluno_id_dest1'     => $alunoId,
            ':aluno_id_dest2'     => $alunoId
        ];

        if (!empty($busca)) {
            $sql .= " AND (LOWER(t.titulo) LIKE :busca OR LOWER(t.descricao) LIKE :busca OR LOWER(t.disciplina) LIKE :busca)";
            $params[':busca'] = '%' . mb_strtolower(trim($busca)) . '%';
        }

        if (!empty($statusFiltro) && $statusFiltro !== 'todos') {
            if ($statusFiltro === 'pendente') {
                $sql .= " AND (te.id IS NULL OR te.status = 'pendente') AND NOW() <= t.prazo_entrega";
            } elseif ($statusFiltro === 'entregue') {
                $sql .= " AND te.status IN ('entregue', 'corrigida')";
            } elseif ($statusFiltro === 'devolvida' || $statusFiltro === 'avaliada') {
                $sql .= " AND te.status = 'devolvida'";
            } elseif ($statusFiltro === 'atrasada') {
                $sql .= " AND (te.id IS NULL OR te.status = 'pendente') AND NOW() > t.prazo_entrega";
            }
        }

        if (!empty($turmaFiltro) && $turmaFiltro !== 'todas') {
            $sql .= " AND t.id IN (
                SELECT td.tarefa_id FROM tarefa_destinatarios td 
                WHERE td.tipo = 'turma' AND td.turma_id = :turma_id
            )";
            $params[':turma_id'] = (int) $turmaFiltro;
        }

        if ($ordenacao === 'antigas') {
            $sql .= " ORDER BY t.created_at ASC, t.id ASC";
        } else {
            $sql .= " ORDER BY t.created_at DESC, t.id DESC";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tarefas as &$tarefa) {
            $tId = (int)$tarefa['id'];
            $tarefa['destinatarios_resumo'] = self::obterResumoDestinatariosTexto($tId);
            $tarefa['prazo_formatado']      = self::formatarPrazo($tarefa['prazo_entrega']);
            $tarefa['materiais_count']      = self::contarMateriais($tId);
            $tarefa['questoes_count']       = self::contarQuestoes($tId);

            // Determinar status consolidado para visualização do aluno
            if (!empty($tarefa['entrega_status'])) {
                $tarefa['status_aluno'] = $tarefa['entrega_status']; // 'entregue', 'corrigida', 'devolvida'
            } else {
                $tarefa['status_aluno'] = ($tarefa['is_atrasada'] == 1) ? 'atrasada' : 'pendente';
            }
        }

        return $tarefas;
    }

    /**
     * Busca os detalhes completos de uma tarefa pelo ID.
     */
    public static function buscarPorId(int $tarefaId, int $instituicaoId): ?array {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT t.*, 
                   u.nome as criador_nome,
                   u.email as criador_email,
                   (CASE 
                       WHEN NOW() > t.prazo_entrega THEN 1 
                       ELSE 0 
                    END) as is_atrasada,
                   (CASE 
                       WHEN t.disponivel_em IS NOT NULL AND NOW() < t.disponivel_em THEN 'futura'
                       WHEN NOW() > t.prazo_entrega THEN 'encerrada'
                       ELSE 'aberta'
                    END) as estado_prazo
            FROM tarefas t
            JOIN usuarios u ON t.created_by = u.id
            WHERE t.id = :id AND t.instituicao_id = :instituicao_id AND t.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([
            ':id'             => $tarefaId,
            ':instituicao_id' => $instituicaoId
        ]);
        $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tarefa) {
            return null;
        }

        $tarefa['destinatarios']   = self::obterDestinatarios($tarefaId);
        $tarefa['materiais']       = self::obterMateriais($tarefaId);
        $tarefa['questoes']        = self::obterQuestoes($tarefaId);
        $tarefa['prazo_formatado'] = self::formatarPrazo($tarefa['prazo_entrega']);

        return $tarefa;
    }

    /**
     * Valida se a tarefa pertence estritamente ao criador e instituição logados.
     */
    public static function validarPropriedade(int $tarefaId, int $userId, int $instituicaoId): bool {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT id FROM tarefas 
            WHERE id = :id AND created_by = :created_by AND instituicao_id = :instituicao_id AND deleted_at IS NULL
        ");
        $stmt->execute([
            ':id'             => $tarefaId,
            ':created_by'     => $userId,
            ':instituicao_id' => $instituicaoId
        ]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Valida se o aluno é um destinatário legítimo da tarefa.
     */
    public static function validarAcessoAluno(int $tarefaId, int $alunoId, int $instituicaoId): bool {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT t.id FROM tarefas t
            WHERE t.id = :tarefa_id 
              AND t.instituicao_id = :instituicao_id 
              AND t.status != 'rascunho'
              AND t.deleted_at IS NULL
              AND (
                  t.id IN (SELECT td1.tarefa_id FROM tarefa_destinatarios td1 WHERE td1.tipo = 'aluno' AND td1.aluno_id = :aluno_id1)
                  OR
                  t.id IN (
                      SELECT td2.tarefa_id FROM tarefa_destinatarios td2 
                      JOIN turma_alunos ta ON td2.turma_id = ta.turma_id 
                      WHERE td2.tipo = 'turma' AND ta.aluno_id = :aluno_id2
                  )
              )
        ");
        $stmt->execute([
            ':tarefa_id'      => $tarefaId,
            ':instituicao_id' => $instituicaoId,
            ':aluno_id1'      => $alunoId,
            ':aluno_id2'      => $alunoId
        ]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Cria uma nova tarefa com seus destinatários, materiais e questionário.
     * Envia notificações automáticas sem duplicidades.
     */
    public static function criarTarefa(
        array $dados, 
        array $destinatarios, 
        array $materiais = [], 
        array $questoes = []
    ): int {
        $db = self::getDb();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare("
                INSERT INTO tarefas 
                (instituicao_id, created_by, titulo, descricao, disciplina, tipo_atividade, permite_anexo_aluno, disponivel_em, prazo_entrega, status, created_at, updated_at)
                VALUES 
                (:instituicao_id, :created_by, :titulo, :descricao, :disciplina, :tipo_atividade, :permite_anexo_aluno, :disponivel_em, :prazo_entrega, :status, NOW(), NOW())
            ");

            $stmt->execute([
                ':instituicao_id'      => $dados['instituicao_id'],
                ':created_by'          => $dados['created_by'],
                ':titulo'              => trim($dados['titulo']),
                ':descricao'           => trim($dados['descricao'] ?? ''),
                ':disciplina'          => trim($dados['disciplina'] ?? ''),
                ':tipo_atividade'      => $dados['tipo_atividade'] ?? 'tradicional',
                ':permite_anexo_aluno' => !empty($dados['permite_anexo_aluno']) ? 1 : 0,
                ':disponivel_em'       => !empty($dados['disponivel_em']) ? $dados['disponivel_em'] : date('Y-m-d H:i:s'),
                ':prazo_entrega'       => $dados['prazo_entrega'],
                ':status'              => $dados['status'] ?? 'publicada',
            ]);

            $tarefaId = (int) $db->lastInsertId();

            // Salvar Destinatários
            self::salvarDestinatarios($db, $tarefaId, $destinatarios, $dados['instituicao_id']);

            // Salvar Materiais de Apoio
            if (!empty($materiais)) {
                $stmtMat = $db->prepare("
                    INSERT INTO tarefa_materiais 
                    (tarefa_id, nome_original, caminho_arquivo, tamanho_bytes, mime_type, created_at)
                    VALUES 
                    (:tarefa_id, :nome_original, :caminho_arquivo, :tamanho_bytes, :mime_type, NOW())
                ");
                foreach ($materiais as $mat) {
                    $stmtMat->execute([
                        ':tarefa_id'       => $tarefaId,
                        ':nome_original'   => $mat['nome_original'],
                        ':caminho_arquivo' => $mat['caminho_arquivo'],
                        ':tamanho_bytes'   => $mat['tamanho_bytes'] ?? 0,
                        ':mime_type'       => $mat['mime_type'] ?? null
                    ]);
                }
            }

            // Salvar Questões do Questionário ÂNCORA
            if (!empty($questoes)) {
                $stmtQ = $db->prepare("
                    INSERT INTO tarefa_questoes 
                    (tarefa_id, ordem, enunciado, tipo, pontos, obrigatoria, alternativas_json, resposta_correta_json, created_at)
                    VALUES 
                    (:tarefa_id, :ordem, :enunciado, :tipo, :pontos, :obrigatoria, :alternativas_json, :resposta_correta_json, NOW())
                ");
                foreach ($questoes as $idx => $q) {
                    $stmtQ->execute([
                        ':tarefa_id'             => $tarefaId,
                        ':ordem'                 => $idx + 1,
                        ':enunciado'             => trim($q['enunciado']),
                        ':tipo'                  => $q['tipo'],
                        ':pontos'                => (float)($q['pontos'] ?? 1.0),
                        ':obrigatoria'           => !empty($q['obrigatoria']) ? 1 : 0,
                        ':alternativas_json'     => !empty($q['alternativas']) ? json_encode($q['alternativas'], JSON_UNESCAPED_UNICODE) : null,
                        ':resposta_correta_json' => isset($q['resposta_correta']) ? json_encode($q['resposta_correta'], JSON_UNESCAPED_UNICODE) : null
                    ]);
                }
            }

            $db->commit();

            // Disparo de Notificações para os Alunos Destinatários (Não duplicadas)
            if (($dados['status'] ?? 'publicada') === 'publicada') {
                self::notificarPublicacaoTarefa($tarefaId, (int)$dados['instituicao_id'], (int)$dados['created_by'], $dados['titulo'], $dados['prazo_entrega']);
            }

            return $tarefaId;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Atualiza os dados de uma tarefa existente (validando propriedade).
     */
    public static function atualizarTarefa(
        int $tarefaId, 
        int $userId, 
        int $instituicaoId, 
        array $dados, 
        array $destinatarios, 
        array $novosMateriais = [], 
        array $removerMateriaisIds = [], 
        array $questoes = []
    ): bool {
        if (!self::validarPropriedade($tarefaId, $userId, $instituicaoId)) {
            return false;
        }

        $db = self::getDb();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare("
                UPDATE tarefas 
                SET titulo = :titulo,
                    descricao = :descricao,
                    disciplina = :disciplina,
                    tipo_atividade = :tipo_atividade,
                    permite_anexo_aluno = :permite_anexo_aluno,
                    prazo_entrega = :prazo_entrega,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id AND instituicao_id = :instituicao_id AND created_by = :created_by
            ");

            $stmt->execute([
                ':id'                  => $tarefaId,
                ':instituicao_id'      => $instituicaoId,
                ':created_by'          => $userId,
                ':titulo'              => trim($dados['titulo']),
                ':descricao'           => trim($dados['descricao'] ?? ''),
                ':disciplina'          => trim($dados['disciplina'] ?? ''),
                ':tipo_atividade'      => $dados['tipo_atividade'] ?? 'tradicional',
                ':permite_anexo_aluno' => !empty($dados['permite_anexo_aluno']) ? 1 : 0,
                ':prazo_entrega'       => $dados['prazo_entrega'],
                ':status'              => $dados['status'] ?? 'publicada',
            ]);

            // Atualizar Destinatários (limpa e recria)
            $db->prepare("DELETE FROM tarefa_destinatarios WHERE tarefa_id = :id")->execute([':id' => $tarefaId]);
            self::salvarDestinatarios($db, $tarefaId, $destinatarios, $instituicaoId);

            // Remover materiais marcados
            if (!empty($removerMateriaisIds)) {
                $inQuery = implode(',', array_map('intval', $removerMateriaisIds));
                $db->query("DELETE FROM tarefa_materiais WHERE tarefa_id = {$tarefaId} AND id IN ({$inQuery})");
            }

            // Inserir novos materiais
            if (!empty($novosMateriais)) {
                $stmtMat = $db->prepare("
                    INSERT INTO tarefa_materiais 
                    (tarefa_id, nome_original, caminho_arquivo, tamanho_bytes, mime_type, created_at)
                    VALUES 
                    (:tarefa_id, :nome_original, :caminho_arquivo, :tamanho_bytes, :mime_type, NOW())
                ");
                foreach ($novosMateriais as $mat) {
                    $stmtMat->execute([
                        ':tarefa_id'       => $tarefaId,
                        ':nome_original'   => $mat['nome_original'],
                        ':caminho_arquivo' => $mat['caminho_arquivo'],
                        ':tamanho_bytes'   => $mat['tamanho_bytes'] ?? 0,
                        ':mime_type'       => $mat['mime_type'] ?? null
                    ]);
                }
            }

            // Se novas questões foram enviadas, sincronizar questões
            if (!empty($questoes)) {
                $db->prepare("DELETE FROM tarefa_questoes WHERE tarefa_id = :id")->execute([':id' => $tarefaId]);
                $stmtQ = $db->prepare("
                    INSERT INTO tarefa_questoes 
                    (tarefa_id, ordem, enunciado, tipo, pontos, obrigatoria, alternativas_json, resposta_correta_json, created_at)
                    VALUES 
                    (:tarefa_id, :ordem, :enunciado, :tipo, :pontos, :obrigatoria, :alternativas_json, :resposta_correta_json, NOW())
                ");
                foreach ($questoes as $idx => $q) {
                    $stmtQ->execute([
                        ':tarefa_id'             => $tarefaId,
                        ':ordem'                 => $idx + 1,
                        ':enunciado'             => trim($q['enunciado']),
                        ':tipo'                  => $q['tipo'],
                        ':pontos'                => (float)($q['pontos'] ?? 1.0),
                        ':obrigatoria'           => !empty($q['obrigatoria']) ? 1 : 0,
                        ':alternativas_json'     => !empty($q['alternativas']) ? json_encode($q['alternativas'], JSON_UNESCAPED_UNICODE) : null,
                        ':resposta_correta_json' => isset($q['resposta_correta']) ? json_encode($q['resposta_correta'], JSON_UNESCAPED_UNICODE) : null
                    ]);
                }
            }

            $db->commit();
            return true;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Exclui logicamente a tarefa (Soft Delete).
     */
    public static function excluirTarefa(int $tarefaId, int $userId, int $instituicaoId): bool {
        if (!self::validarPropriedade($tarefaId, $userId, $instituicaoId)) {
            return false;
        }

        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE tarefas 
            SET deleted_at = NOW() 
            WHERE id = :id AND created_by = :created_by AND instituicao_id = :instituicao_id
        ");
        return $stmt->execute([
            ':id'             => $tarefaId,
            ':created_by'     => $userId,
            ':instituicao_id' => $instituicaoId
        ]);
    }

    /**
     * Obtém o conjunto de alunos únicos destinatários da tarefa dentro da instituição.
     */
    public static function obterAlunosDestinatariosUnicos(int $tarefaId, int $instituicaoId): array {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT DISTINCT u.id, u.nome, u.email,
                   (
                       SELECT t.nome FROM turmas t 
                       JOIN turma_alunos ta ON t.id = ta.turma_id 
                       WHERE ta.aluno_id = u.id AND t.instituicao_id = :inst_id1
                       LIMIT 1
                   ) as turma_nome
            FROM usuarios u
            WHERE u.instituicao_id = :inst_id2 
              AND u.perfil_id = 3 
              AND u.status = 'ativo'
              AND (
                  u.id IN (
                      SELECT td1.aluno_id FROM tarefa_destinatarios td1 
                      WHERE td1.tarefa_id = :tarefa_id1 AND td1.tipo = 'aluno'
                  )
                  OR
                  u.id IN (
                      SELECT ta2.aluno_id FROM turma_alunos ta2
                      JOIN tarefa_destinatarios td2 ON ta2.turma_id = td2.turma_id
                      WHERE td2.tarefa_id = :tarefa_id2 AND td2.tipo = 'turma'
                  )
              )
            ORDER BY u.nome ASC
        ");

        $stmt->execute([
            ':inst_id1'    => $instituicaoId,
            ':inst_id2'    => $instituicaoId,
            ':tarefa_id1'  => $tarefaId,
            ':tarefa_id2'  => $tarefaId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna resumo de métricas de entregas de uma tarefa.
     */
    public static function obterResumoEntregas(int $tarefaId, int $instituicaoId): array {
        $db = self::getDb();

        // 1. Obter total de destinatários únicos
        $alunos = self::obterAlunosDestinatariosUnicos($tarefaId, $instituicaoId);
        $totalDestinatarios = count($alunos);

        // 2. Contagens de entregas
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_entregues,
                SUM(CASE WHEN status = 'corrigida' THEN 1 ELSE 0 END) as total_corrigidas,
                SUM(CASE WHEN status = 'devolvida' THEN 1 ELSE 0 END) as total_devolvidas,
                AVG(CASE WHEN nota IS NOT NULL THEN nota ELSE NULL END) as media_notas
            FROM tarefa_entregas
            WHERE tarefa_id = :tarefa_id
        ");
        $stmt->execute([':tarefa_id' => $tarefaId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalEntregues = (int)($row['total_entregues'] ?? 0);
        $naoEntregues   = max(0, $totalDestinatarios - $totalEntregues);

        return [
            'total_destinatarios' => $totalDestinatarios,
            'total_entregues'     => $totalEntregues,
            'total_nao_entregues' => $naoEntregues,
            'total_corrigidas'    => (int)($row['total_corrigidas'] ?? 0),
            'total_devolvidas'    => (int)($row['total_devolvidas'] ?? 0),
            'media_notas'         => $row['media_notas'] !== null ? round((float)$row['media_notas'], 1) : null
        ];
    }

    /**
     * Retorna lista completa de todos os alunos destinatários da tarefa combinados com o status de suas entregas.
     */
    public static function listarAlunosEEntregasDaTarefa(int $tarefaId, int $instituicaoId): array {
        $alunos = self::obterAlunosDestinatariosUnicos($tarefaId, $instituicaoId);
        $db = self::getDb();

        // Obter todas as entregas registradas para esta tarefa
        $stmt = $db->prepare("
            SELECT te.*, 
                   u_corretor.nome as corretor_nome
            FROM tarefa_entregas te
            LEFT JOIN usuarios u_corretor ON te.corrigida_por = u_corretor.id
            WHERE te.tarefa_id = :tarefa_id
        ");
        $stmt->execute([':tarefa_id' => $tarefaId]);
        $entregasRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $entregasPorAluno = [];
        foreach ($entregasRaw as $e) {
            $entregasPorAluno[(int)$e['aluno_id']] = $e;
        }

        $resultado = [];
        foreach ($alunos as $aluno) {
            $aId = (int)$aluno['id'];
            $entrega = $entregasPorAluno[$aId] ?? null;

            $alunoData = [
                'aluno_id'      => $aId,
                'aluno_nome'    => $aluno['nome'],
                'aluno_email'   => $aluno['email'],
                'turma_nome'    => $aluno['turma_nome'] ?? 'Sem Turma',
                'tem_entrega'   => ($entrega !== null),
                'entrega_id'    => $entrega ? (int)$entrega['id'] : null,
                'status'        => $entrega ? $entrega['status'] : 'nao_entregue',
                'nota'          => $entrega ? $entrega['nota'] : null,
                'nota_maxima'   => $entrega ? $entrega['nota_maxima'] : 10.00,
                'feedback_geral'=> $entrega ? $entrega['feedback_geral'] : null,
                'entregue_em'   => $entrega ? $entrega['entregue_em'] : null,
                'corrigida_em'  => $entrega ? $entrega['corrigida_em'] : null,
                'devolvida_em'  => $entrega ? $entrega['devolvida_em'] : null,
                'corretor_nome' => $entrega ? $entrega['corretor_nome'] : null,
            ];

            if ($entrega) {
                $alunoData['arquivos']  = self::obterArquivosEntrega((int)$entrega['id']);
                $alunoData['respostas'] = self::obterRespostasEntrega((int)$entrega['id']);
            } else {
                $alunoData['arquivos']  = [];
                $alunoData['respostas'] = [];
            }

            $resultado[] = $alunoData;
        }

        return $resultado;
    }

    /**
     * Obtém a entrega do aluno para uma tarefa específica.
     */
    public static function obterEntregaDoAluno(int $tarefaId, int $alunoId): ?array {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT te.*, 
                   u.nome as aluno_nome, 
                   u.email as aluno_email,
                   c.nome as corretor_nome
            FROM tarefa_entregas te
            JOIN usuarios u ON te.aluno_id = u.id
            LEFT JOIN usuarios c ON te.corrigida_por = c.id
            WHERE te.tarefa_id = :tarefa_id AND te.aluno_id = :aluno_id
            LIMIT 1
        ");
        $stmt->execute([
            ':tarefa_id' => $tarefaId,
            ':aluno_id'  => $alunoId
        ]);
        $entrega = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$entrega) {
            return null;
        }

        $eId = (int)$entrega['id'];
        $entrega['arquivos']  = self::obterArquivosEntrega($eId);
        $entrega['respostas'] = self::obterRespostasEntrega($eId);

        return $entrega;
    }

    /**
     * Submete a entrega do aluno (validando prazo rigoroso no servidor).
     */
    public static function salvarEntregaAluno(
        int $tarefaId, 
        int $alunoId, 
        int $instituicaoId, 
        array $respostas = [], 
        array $arquivos = []
    ): array {
        $db = self::getDb();

        // 1. Validar acesso do aluno à tarefa
        if (!self::validarAcessoAluno($tarefaId, $alunoId, $instituicaoId)) {
            return ['success' => false, 'error' => 'Acesso negado. Esta atividade não está destinada a você.'];
        }

        // 2. Buscar tarefa e verificar PRAZO ESTRITO NO SERVIDOR
        $stmtT = $db->prepare("SELECT * FROM tarefas WHERE id = :id AND instituicao_id = :inst_id AND deleted_at IS NULL");
        $stmtT->execute([':id' => $tarefaId, ':inst_id' => $instituicaoId]);
        $tarefa = $stmtT->fetch(PDO::FETCH_ASSOC);

        if (!$tarefa) {
            return ['success' => false, 'error' => 'Tarefa não encontrada.'];
        }

        // Verificar data de abertura futura
        if (!empty($tarefa['disponivel_em']) && strtotime('now') < strtotime($tarefa['disponivel_em'])) {
            return ['success' => false, 'error' => 'Esta atividade ainda não está aberta para respostas.'];
        }

        // REGRA MANDATÓRIA: Bloqueio estrito de prazo no backend
        if (strtotime('now') > strtotime($tarefa['prazo_entrega'])) {
            return ['success' => false, 'error' => 'Prazo de entrega encerrado! O servidor não aceita mais envios para esta atividade.'];
        }

        // 3. Verificar se já existe entrega
        $stmtCheck = $db->prepare("SELECT id, status FROM tarefa_entregas WHERE tarefa_id = :tid AND aluno_id = :aid");
        $stmtCheck->execute([':tid' => $tarefaId, ':aid' => $alunoId]);
        $entregaExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($entregaExistente && ($entregaExistente['status'] === 'corrigida' || $entregaExistente['status'] === 'devolvida')) {
            return ['success' => false, 'error' => 'Sua atividade já foi corrigida/devolvida e não pode ser reenviada.'];
        }

        $questoes = self::obterQuestoes($tarefaId);
        $notaCalculada = 0.0;
        $totalPontosPossiveis = 0.0;

        $db->beginTransaction();
        try {
            if ($entregaExistente) {
                $entregaId = (int)$entregaExistente['id'];
                $stmtUpdate = $db->prepare("
                    UPDATE tarefa_entregas 
                    SET status = 'entregue', entregue_em = NOW(), updated_at = NOW() 
                    WHERE id = :id
                ");
                $stmtUpdate->execute([':id' => $entregaId]);

                // Limpar respostas anteriores para atualizar
                $db->prepare("DELETE FROM tarefa_entrega_respostas WHERE entrega_id = :eid")->execute([':eid' => $entregaId]);
            } else {
                $stmtInsert = $db->prepare("
                    INSERT INTO tarefa_entregas 
                    (tarefa_id, aluno_id, status, nota_maxima, entregue_em, created_at, updated_at)
                    VALUES 
                    (:tarefa_id, :aluno_id, 'entregue', 10.00, NOW(), NOW(), NOW())
                ");
                $stmtInsert->execute([
                    ':tarefa_id' => $tarefaId,
                    ':aluno_id'  => $alunoId
                ]);
                $entregaId = (int) $db->lastInsertId();
            }

            // Gravar Arquivos Anexados pelo Aluno
            if (!empty($arquivos)) {
                $stmtArq = $db->prepare("
                    INSERT INTO tarefa_entrega_arquivos 
                    (entrega_id, nome_original, caminho_arquivo, tamanho_bytes, mime_type, created_at)
                    VALUES 
                    (:entrega_id, :nome_original, :caminho_arquivo, :tamanho_bytes, :mime_type, NOW())
                ");
                foreach ($arquivos as $arq) {
                    $stmtArq->execute([
                        ':entrega_id'      => $entregaId,
                        ':nome_original'   => $arq['nome_original'],
                        ':caminho_arquivo' => $arq['caminho_arquivo'],
                        ':tamanho_bytes'   => $arq['tamanho_bytes'] ?? 0,
                        ':mime_type'       => $arq['mime_type'] ?? null
                    ]);
                }
            }

            // Gravar Respostas do Questionário e Auto-Corrigir Objetivas
            if (!empty($questoes)) {
                $stmtResp = $db->prepare("
                    INSERT INTO tarefa_entrega_respostas 
                    (entrega_id, questao_id, resposta_texto, resposta_selecao_json, pontos_obtidos, created_at)
                    VALUES 
                    (:entrega_id, :questao_id, :resposta_texto, :resposta_selecao_json, :pontos_obtidos, NOW())
                ");

                foreach ($questoes as $q) {
                    $qId = (int)$q['id'];
                    $qTipo = $q['tipo'];
                    $qPontos = (float)$q['pontos'];
                    $totalPontosPossiveis += $qPontos;

                    $respEnviada = $respostas[$qId] ?? null;
                    $respostaTexto = null;
                    $respostaSelecaoJson = null;
                    $pontosObtidos = null; // null = precisa de correção manual

                    $respCorreta = !empty($q['resposta_correta_json']) ? json_decode($q['resposta_correta_json'], true) : null;

                    if ($qTipo === 'multipla_escolha' || $qTipo === 'verdadeiro_falso') {
                        $respostaTexto = is_string($respEnviada) ? trim($respEnviada) : '';
                        if ($respCorreta !== null) {
                            $corretaTexto = is_array($respCorreta) ? ($respCorreta['correta'] ?? '') : (string)$respCorreta;
                            $pontosObtidos = (mb_strtolower($respostaTexto) === mb_strtolower($corretaTexto)) ? $qPontos : 0.0;
                            $notaCalculada += $pontosObtidos;
                        }
                    } elseif ($qTipo === 'multipla_selecao') {
                        $selecaoArray = is_array($respEnviada) ? $respEnviada : [];
                        $respostaSelecaoJson = json_encode($selecaoArray, JSON_UNESCAPED_UNICODE);
                        if ($respCorreta !== null && is_array($respCorreta)) {
                            sort($selecaoArray);
                            $corretaArray = $respCorreta;
                            sort($corretaArray);
                            $pontosObtidos = ($selecaoArray === $corretaArray) ? $qPontos : 0.0;
                            $notaCalculada += $pontosObtidos;
                        }
                    } elseif ($qTipo === 'resposta_curta') {
                        $respostaTexto = is_string($respEnviada) ? trim($respEnviada) : '';
                        if ($respCorreta !== null && !empty($respCorreta['texto'])) {
                            $esperado = mb_strtolower(trim($respCorreta['texto']));
                            $pontosObtidos = (mb_strtolower($respostaTexto) === $esperado) ? $qPontos : 0.0;
                            $notaCalculada += $pontosObtidos;
                        }
                    } else { // discursiva
                        $respostaTexto = is_string($respEnviada) ? trim($respEnviada) : '';
                        $pontosObtidos = null; // Requer avaliação do professor
                    }

                    $stmtResp->execute([
                        ':entrega_id'             => $entregaId,
                        ':questao_id'             => $qId,
                        ':resposta_texto'         => $respostaTexto,
                        ':resposta_selecao_json'  => $respostaSelecaoJson,
                        ':pontos_obtidos'         => $pontosObtidos
                    ]);
                }
            }

            $db->commit();
            return ['success' => true, 'entrega_id' => $entregaId];

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Corrige e Devolve a atividade ao aluno (com atribuição de notas, feedback e notificação).
     */
    public static function corrigirEDevolverEntrega(
        int $entregaId, 
        int $tarefaId, 
        int $corretorId, 
        int $instituicaoId, 
        ?float $notaFinal, 
        ?string $feedbackGeral, 
        array $notasQuestoes = [], 
        array $comentariosQuestoes = [], 
        bool $devolver = true
    ): bool {
        // Validar que a tarefa pertence ao corretor logado
        if (!self::validarPropriedade($tarefaId, $corretorId, $instituicaoId)) {
            return false;
        }

        $db = self::getDb();
        $db->beginTransaction();

        try {
            // 1. Atualizar notas e comentários por questão
            if (!empty($notasQuestoes) || !empty($comentariosQuestoes)) {
                $stmtQ = $db->prepare("
                    UPDATE tarefa_entrega_respostas 
                    SET pontos_obtidos = :pontos,
                        comentario_professor = :comentario
                    WHERE entrega_id = :eid AND questao_id = :qid
                ");

                $questoesIds = array_unique(array_merge(array_keys($notasQuestoes), array_keys($comentariosQuestoes)));
                foreach ($questoesIds as $qid) {
                    $pontos = isset($notasQuestoes[$qid]) && $notasQuestoes[$qid] !== '' ? (float)$notasQuestoes[$qid] : null;
                    $coment = trim($comentariosQuestoes[$qid] ?? '');

                    $stmtQ->execute([
                        ':eid'        => $entregaId,
                        ':qid'        => (int)$qid,
                        ':pontos'     => $pontos,
                        ':comentario' => !empty($coment) ? $coment : null
                    ]);
                }
            }

            // 2. Atualizar registro da entrega
            $novoStatus = $devolver ? 'devolvida' : 'corrigida';
            $stmtE = $db->prepare("
                UPDATE tarefa_entregas 
                SET status = :status,
                    nota = :nota,
                    feedback_geral = :feedback,
                    corrigida_por = :corretor_id,
                    corrigida_em = NOW(),
                    devolvida_em = :devolvida_em,
                    updated_at = NOW()
                WHERE id = :id AND tarefa_id = :tarefa_id
            ");

            $stmtE->execute([
                ':id'           => $entregaId,
                ':tarefa_id'    => $tarefaId,
                ':status'       => $novoStatus,
                ':nota'         => $notaFinal,
                ':feedback'     => trim($feedbackGeral ?? ''),
                ':corretor_id'  => $corretorId,
                ':devolvida_em' => $devolver ? date('Y-m-d H:i:s') : null
            ]);

            $db->commit();

            // 3. Notificar aluno caso a atividade tenha sido devolvida
            if ($devolver) {
                // Obter dados do aluno e da tarefa
                $stmtInfo = $db->prepare("
                    SELECT te.aluno_id, t.titulo 
                    FROM tarefa_entregas te 
                    JOIN tarefas t ON te.tarefa_id = t.id 
                    WHERE te.id = :eid
                ");
                $stmtInfo->execute([':eid' => $entregaId]);
                $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

                if ($info) {
                    $alunoId = (int)$info['aluno_id'];
                    $tituloTarefa = $info['titulo'];
                    $notaStr = ($notaFinal !== null) ? number_format($notaFinal, 1, ',', '.') . '/10,0' : 'Avaliada';

                    NotificationService::criarParaUsuario(
                        $instituicaoId,
                        $alunoId,
                        'Sucesso',
                        'Atividade devolvida: ' . $tituloTarefa,
                        "Sua atividade \"{$tituloTarefa}\" foi corrigida pelo professor. Nota: {$notaStr}. Clique para ver o feedback.",
                        'tarefas',
                        url('tarefas/detalhes', ['id' => $tarefaId]),
                        $corretorId
                    );
                }
            }

            return true;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Retorna contadores para exibição nos dashboards do sistema.
     */
    public static function obterContadoresDashboard(int $userId, int $perfilId, int $instituicaoId): array {
        $db = self::getDb();

        if ($perfilId === 1 || $perfilId === 2) { // Admin ou Professor
            $stmt = $db->prepare("
                SELECT 
                    COUNT(DISTINCT t.id) as total_tarefas,
                    SUM(CASE WHEN (t.disponivel_em IS NULL OR NOW() >= t.disponivel_em) AND NOW() <= t.prazo_entrega THEN 1 ELSE 0 END) as tarefas_ativas,
                    SUM(CASE WHEN NOW() > t.prazo_entrega THEN 1 ELSE 0 END) as tarefas_encerradas,
                    (
                        SELECT COUNT(*) FROM tarefa_entregas te 
                        JOIN tarefas t2 ON te.tarefa_id = t2.id 
                        WHERE t2.created_by = :uid1 AND t2.instituicao_id = :inst1 AND te.status = 'entregue'
                    ) as entregas_pendentes_correcao
                FROM tarefas t
                WHERE t.created_by = :uid2 AND t.instituicao_id = :inst2 AND t.deleted_at IS NULL
            ");
            $stmt->execute([
                ':uid1'  => $userId,
                ':inst1' => $instituicaoId,
                ':uid2'  => $userId,
                ':inst2' => $instituicaoId
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'total_tarefas'               => (int)($row['total_tarefas'] ?? 0),
                'tarefas_ativas'              => (int)($row['tarefas_ativas'] ?? 0),
                'tarefas_encerradas'          => (int)($row['tarefas_encerradas'] ?? 0),
                'entregas_pendentes_correcao' => (int)($row['entregas_pendentes_correcao'] ?? 0)
            ];
        } else { // Aluno
            $tarefasAluno = self::listarDestinadasAoAluno($userId, $instituicaoId);
            $pendentes = 0;
            $entregues = 0;
            $avaliadas = 0;
            $atrasadas = 0;

            foreach ($tarefasAluno as $t) {
                if ($t['status_aluno'] === 'pendente') $pendentes++;
                elseif ($t['status_aluno'] === 'atrasada') $atrasadas++;
                elseif ($t['status_aluno'] === 'entregue' || $t['status_aluno'] === 'corrigida') $entregues++;
                elseif ($t['status_aluno'] === 'devolvida') $avaliadas++;
            }

            return [
                'total_tarefas'     => count($tarefasAluno),
                'tarefas_pendentes' => $pendentes,
                'tarefas_entregues' => $entregues,
                'tarefas_avaliadas' => $avaliadas,
                'tarefas_atrasadas' => $atrasadas
            ];
        }
    }

    /* -------------------------------------------------------------
     * MÉTODOS AUXILIARES PRIVADOS
     * ------------------------------------------------------------- */

    private static function salvarDestinatarios(PDO $db, int $tarefaId, array $destinatarios, int $instituicaoId): void {
        $stmtTurma = $db->prepare("
            INSERT INTO tarefa_destinatarios (tarefa_id, tipo, turma_id, created_at)
            SELECT :tarefa_id, 'turma', id, NOW() 
            FROM turmas 
            WHERE id = :turma_id AND instituicao_id = :inst_id
        ");

        $stmtAluno = $db->prepare("
            INSERT INTO tarefa_destinatarios (tarefa_id, tipo, aluno_id, created_at)
            SELECT :tarefa_id, 'aluno', id, NOW() 
            FROM usuarios 
            WHERE id = :aluno_id AND instituicao_id = :inst_id AND perfil_id = 3
        ");

        if (!empty($destinatarios['turmas'])) {
            foreach ($destinatarios['turmas'] as $tId) {
                $stmtTurma->execute([
                    ':tarefa_id' => $tarefaId,
                    ':turma_id'  => (int)$tId,
                    ':inst_id'   => $instituicaoId
                ]);
            }
        }

        if (!empty($destinatarios['alunos'])) {
            foreach ($destinatarios['alunos'] as $aId) {
                $stmtAluno->execute([
                    ':tarefa_id' => $tarefaId,
                    ':aluno_id'  => (int)$aId,
                    ':inst_id'   => $instituicaoId
                ]);
            }
        }
    }

    private static function notificarPublicacaoTarefa(int $tarefaId, int $instituicaoId, int $criadorId, string $titulo, string $prazo): void {
        $alunos = self::obterAlunosDestinatariosUnicos($tarefaId, $instituicaoId);
        $prazoFormatado = date('d/m/Y \à\s H:i', strtotime($prazo));

        foreach ($alunos as $aluno) {
            NotificationService::criarParaUsuario(
                $instituicaoId,
                (int)$aluno['id'],
                'Informativo',
                'Nova Tarefa: ' . $titulo,
                "Você recebeu uma nova atividade: \"{$titulo}\". Prazo de entrega: {$prazoFormatado}.",
                'tarefas',
                url('tarefas/detalhes', ['id' => $tarefaId]),
                $criadorId
            );
        }
    }

    public static function obterDestinatarios(int $tarefaId): array {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT td.*, 
                   t.nome as turma_nome, 
                   u.nome as aluno_nome, 
                   u.email as aluno_email
            FROM tarefa_destinatarios td
            LEFT JOIN turmas t ON td.turma_id = t.id
            LEFT JOIN usuarios u ON td.aluno_id = u.id
            WHERE td.tarefa_id = :tarefa_id
        ");
        $stmt->execute([':tarefa_id' => $tarefaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obterMateriais(int $tarefaId): array {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM tarefa_materiais WHERE tarefa_id = :tarefa_id ORDER BY id ASC");
        $stmt->execute([':tarefa_id' => $tarefaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obterQuestoes(int $tarefaId): array {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM tarefa_questoes WHERE tarefa_id = :tarefa_id ORDER BY ordem ASC, id ASC");
        $stmt->execute([':tarefa_id' => $tarefaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obterArquivosEntrega(int $entregaId): array {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM tarefa_entrega_arquivos WHERE entrega_id = :eid ORDER BY id ASC");
        $stmt->execute([':eid' => $entregaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obterRespostasEntrega(int $entregaId): array {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT tr.*, 
                   tq.enunciado, 
                   tq.tipo as questao_tipo, 
                   tq.pontos as questao_pontos, 
                   tq.alternativas_json, 
                   tq.resposta_correta_json, 
                   tq.ordem
            FROM tarefa_entrega_respostas tr
            JOIN tarefa_questoes tq ON tr.questao_id = tq.id
            WHERE tr.entrega_id = :eid
            ORDER BY tq.ordem ASC
        ");
        $stmt->execute([':eid' => $entregaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function contarMateriais(int $tarefaId): int {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT COUNT(*) FROM tarefa_materiais WHERE tarefa_id = :id");
        $stmt->execute([':id' => $tarefaId]);
        return (int)$stmt->fetchColumn();
    }

    private static function contarQuestoes(int $tarefaId): int {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT COUNT(*) FROM tarefa_questoes WHERE tarefa_id = :id");
        $stmt->execute([':id' => $tarefaId]);
        return (int)$stmt->fetchColumn();
    }

    public static function obterResumoDestinatariosTexto(int $tarefaId): string {
        $dest = self::obterDestinatarios($tarefaId);
        $nomes = [];
        foreach ($dest as $d) {
            if ($d['tipo'] === 'turma' && !empty($d['turma_nome'])) {
                $nomes[] = $d['turma_nome'];
            } elseif ($d['tipo'] === 'aluno' && !empty($d['aluno_nome'])) {
                $nomes[] = $d['aluno_nome'];
            }
        }
        return !empty($nomes) ? implode(', ', $nomes) : 'Sem destinatários';
    }

    public static function formatarPrazo(string $dataPrazo): string {
        $timestamp = strtotime($dataPrazo);
        $meses = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
        ];
        $dia = date('d', $timestamp);
        $mes = $meses[(int)date('m', $timestamp)] ?? '';
        $hora = date('H:i', $timestamp);
        return "{$dia} de {$mes} às {$hora}";
    }
}
