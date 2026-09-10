<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Model Turma (Gestão de Turmas e Vínculos de Professores e Alunos)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Gerenciar as turmas escolares e a associação de professores (com suas disciplinas)
 * e alunos pertencentes à mesma instituição (Multi-Tenancy).
 * 
 * REGRAS E SEGURANÇA:
 * 1. ISOLAMENTO MULTI-TENANCY: Todas as consultas filtram estritamente por instituicao_id.
 * 2. PREPARED STATEMENTS: Prevenção total contra SQL Injection.
 * 3. INTEGRIDADE REFERENCIAL: Chaves estrangeiras ON DELETE CASCADE limpam associações ao excluir a turma.
 */

require_once __DIR__ . '/../../config/database.php';

class Turma {

    /**
     * Lista todas as turmas de uma instituição com a contagem e resumo de membros.
     *
     * @param int $instituicaoId ID da instituição do Administrador logado.
     * @param string|null $busca Termo opcional para busca por nome da turma.
     * @return array Lista de turmas com professores e alunos associados.
     */
    public static function listarTodas(int $instituicaoId, ?string $busca = null): array {
        $db = getDatabaseConnection();

        $sql = "
            SELECT t.id, t.nome, t.instituicao_id, t.created_at, t.updated_at,
                   (SELECT COUNT(*) FROM turma_alunos ta WHERE ta.turma_id = t.id) as total_alunos,
                   (SELECT COUNT(*) FROM turma_professores tp WHERE tp.turma_id = t.id) as total_professores
            FROM turmas t
            WHERE t.instituicao_id = :instituicao_id
        ";

        $params = [':instituicao_id' => $instituicaoId];

        if (!empty($busca)) {
            $sql .= " AND LOWER(t.nome) LIKE :busca";
            $params[':busca'] = '%' . mb_strtolower(trim($busca)) . '%';
        }

        $sql .= " ORDER BY t.nome ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $turmas = $stmt->fetchAll();

        // Anexa os professores e alunos para exibição nos cards da interface
        foreach ($turmas as &$turma) {
            $turma['professores'] = self::listarProfessores((int)$turma['id']);
            $turma['alunos']      = self::listarAlunos((int)$turma['id']);
        }

        return $turmas;
    }

    /**
     * Busca uma turma específica pelo seu ID garantindo o escopo da instituição.
     */
    public static function buscarPorId(int $id, int $instituicaoId) {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("
            SELECT * FROM turmas 
            WHERE id = :id AND instituicao_id = :instituicao_id
        ");
        $stmt->execute([
            ':id'             => $id,
            ':instituicao_id' => $instituicaoId
        ]);
        $turma = $stmt->fetch();

        if ($turma) {
            $turma['professores'] = self::listarProfessores((int)$turma['id']);
            $turma['alunos']      = self::listarAlunos((int)$turma['id']);
        }

        return $turma;
    }

    /**
     * Cadastra uma nova turma no banco de dados.
     *
     * @param string $nome Nome da turma (ex: "1º Ano A")
     * @param int $instituicaoId ID da instituição
     * @return int ID da turma criada
     * @throws Exception Se o nome já existir na mesma instituição.
     */
    public static function criar(string $nome, int $instituicaoId): int {
        $db = getDatabaseConnection();

        // Valida duplicidade de nome na mesma instituição
        $stmtCheck = $db->prepare("SELECT id FROM turmas WHERE LOWER(nome) = LOWER(:nome) AND instituicao_id = :instituicao_id");
        $stmtCheck->execute([
            ':nome'           => trim($nome),
            ':instituicao_id' => $instituicaoId
        ]);
        if ($stmtCheck->fetch()) {
            throw new Exception("Já existe uma turma cadastrada com o nome '" . htmlspecialchars($nome) . "' nesta instituição.");
        }

        $stmt = $db->prepare("
            INSERT INTO turmas (nome, instituicao_id, created_at, updated_at) 
            VALUES (:nome, :instituicao_id, NOW(), NOW())
        ");
        $stmt->execute([
            ':nome'           => trim($nome),
            ':instituicao_id' => $instituicaoId
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Atualiza o nome de uma turma existente.
     */
    public static function atualizar(int $id, string $nome, int $instituicaoId): bool {
        $db = getDatabaseConnection();

        // Valida se outro registro utiliza o mesmo nome na mesma instituição
        $stmtCheck = $db->prepare("
            SELECT id FROM turmas 
            WHERE LOWER(nome) = LOWER(:nome) AND instituicao_id = :instituicao_id AND id != :id
        ");
        $stmtCheck->execute([
            ':nome'           => trim($nome),
            ':instituicao_id' => $instituicaoId,
            ':id'             => $id
        ]);
        if ($stmtCheck->fetch()) {
            throw new Exception("Já existe outra turma com o nome '" . htmlspecialchars($nome) . "' nesta instituição.");
        }

        $stmt = $db->prepare("
            UPDATE turmas 
            SET nome = :nome, updated_at = NOW() 
            WHERE id = :id AND instituicao_id = :instituicao_id
        ");
        return $stmt->execute([
            ':nome'           => trim($nome),
            ':id'             => $id,
            ':instituicao_id' => $instituicaoId
        ]);
    }

    /**
     * Exclui uma turma e seus vínculos de alunos e professores (via CASCADE).
     */
    public static function excluir(int $id, int $instituicaoId): bool {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("DELETE FROM turmas WHERE id = :id AND instituicao_id = :instituicao_id");
        return $stmt->execute([
            ':id'             => $id,
            ':instituicao_id' => $instituicaoId
        ]);
    }

    /**
     * Retorna a lista de professores vinculados a uma turma.
     */
    public static function listarProfessores(int $turmaId): array {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("
            SELECT tp.id as vinculo_id, u.id as professor_id, u.nome, u.email, tp.disciplina
            FROM turma_professores tp
            JOIN usuarios u ON tp.professor_id = u.id
            WHERE tp.turma_id = :turma_id
            ORDER BY u.nome ASC
        ");
        $stmt->execute([':turma_id' => $turmaId]);
        return $stmt->fetchAll();
    }

    /**
     * Vincula um professor a uma turma com uma disciplina.
     */
    public static function adicionarProfessor(int $turmaId, int $professorId, ?string $disciplina = null): bool {
        $db = getDatabaseConnection();

        // Evita duplicidade do mesmo professor na mesma disciplina e turma
        $stmtCheck = $db->prepare("
            SELECT id FROM turma_professores 
            WHERE turma_id = :turma_id AND professor_id = :professor_id AND (disciplina = :disciplina OR (disciplina IS NULL AND :disciplina2 IS NULL))
        ");
        $stmtCheck->execute([
            ':turma_id'     => $turmaId,
            ':professor_id' => $professorId,
            ':disciplina'   => $disciplina,
            ':disciplina2'  => $disciplina
        ]);
        if ($stmtCheck->fetch()) {
            throw new Exception("Este professor já está vinculado a esta turma com a mesma disciplina.");
        }

        $stmt = $db->prepare("
            INSERT INTO turma_professores (turma_id, professor_id, disciplina, created_at)
            VALUES (:turma_id, :professor_id, :disciplina, NOW())
        ");
        return $stmt->execute([
            ':turma_id'     => $turmaId,
            ':professor_id' => $professorId,
            ':disciplina'   => !empty($disciplina) ? trim($disciplina) : null
        ]);
    }

    /**
     * Remove o vínculo de um professor de uma turma.
     */
    public static function removerProfessor(int $turmaId, int $professorId, ?int $vinculoId = null): bool {
        $db = getDatabaseConnection();
        if ($vinculoId !== null) {
            $stmt = $db->prepare("DELETE FROM turma_professores WHERE id = :id AND turma_id = :turma_id");
            return $stmt->execute([':id' => $vinculoId, ':turma_id' => $turmaId]);
        }
        $stmt = $db->prepare("DELETE FROM turma_professores WHERE turma_id = :turma_id AND professor_id = :professor_id");
        return $stmt->execute([':turma_id' => $turmaId, ':professor_id' => $professorId]);
    }

    /**
     * Retorna a lista de alunos vinculados a uma turma.
     */
    public static function listarAlunos(int $turmaId): array {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("
            SELECT ta.id as vinculo_id, u.id as aluno_id, u.nome, u.email
            FROM turma_alunos ta
            JOIN usuarios u ON ta.aluno_id = u.id
            WHERE ta.turma_id = :turma_id
            ORDER BY u.nome ASC
        ");
        $stmt->execute([':turma_id' => $turmaId]);
        return $stmt->fetchAll();
    }

    /**
     * Vincula um aluno a uma turma.
     */
    public static function adicionarAluno(int $turmaId, int $alunoId): bool {
        $db = getDatabaseConnection();

        // Evita duplicidade do mesmo aluno na mesma turma
        $stmtCheck = $db->prepare("SELECT id FROM turma_alunos WHERE turma_id = :turma_id AND aluno_id = :aluno_id");
        $stmtCheck->execute([
            ':turma_id' => $turmaId,
            ':aluno_id' => $alunoId
        ]);
        if ($stmtCheck->fetch()) {
            throw new Exception("Este aluno já está vinculado a esta turma.");
        }

        $stmt = $db->prepare("
            INSERT INTO turma_alunos (turma_id, aluno_id, created_at)
            VALUES (:turma_id, :aluno_id, NOW())
        ");
        return $stmt->execute([
            ':turma_id' => $turmaId,
            ':aluno_id' => $alunoId
        ]);
    }

    /**
     * Remove o vínculo de um aluno de uma turma.
     */
    public static function removerAluno(int $turmaId, int $alunoId): bool {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("DELETE FROM turma_alunos WHERE turma_id = :turma_id AND aluno_id = :aluno_id");
        return $stmt->execute([
            ':turma_id' => $turmaId,
            ':aluno_id' => $alunoId
        ]);
    }

    /**
     * Lista todos os professores ativos da instituição do Administrador para os selects.
     */
    public static function listarProfessoresDisponiveis(int $instituicaoId): array {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("
            SELECT u.id, u.nome, u.email 
            FROM usuarios u
            JOIN perfis p ON u.perfil_id = p.id
            WHERE LOWER(p.nome) = 'professor' AND u.status = 'ativo' AND u.instituicao_id = :instituicao_id
            ORDER BY u.nome ASC
        ");
        $stmt->execute([':instituicao_id' => $instituicaoId]);
        return $stmt->fetchAll();
    }

    /**
     * Lista todos os alunos ativos da instituição do Administrador para os selects.
     */
    public static function listarAlunosDisponiveis(int $instituicaoId): array {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("
            SELECT u.id, u.nome, u.email 
            FROM usuarios u
            JOIN perfis p ON u.perfil_id = p.id
            WHERE LOWER(p.nome) = 'aluno' AND u.status = 'ativo' AND u.instituicao_id = :instituicao_id
            ORDER BY u.nome ASC
        ");
        $stmt->execute([':instituicao_id' => $instituicaoId]);
        return $stmt->fetchAll();
    }
}
