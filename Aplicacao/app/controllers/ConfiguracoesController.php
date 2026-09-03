<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Controller da Tela de Configurações (/configuracoes)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Gerenciar as preferências e configurações de conta do usuário autenticado no sistema,
 * permitindo edição de dados pessoais autorizados, alteração de e-mail com validação de unicidade,
 * troca segura de senha com verificação da credencial atual, e consulta ao código institucional.
 * 
 * REGRAS DE SEGURANÇA E ACESSO:
 * 1. ACESSO UNIVERSAL AUTENTICADO: Acessível por qualquer perfil (Administrador, Professor, Aluno, Funcionário).
 * 2. VALIDAÇÃO DE SESSÃO: Exige sessão ativa ($_SESSION['user']); redireciona não autenticados para login.
 * 3. ISOLAMENTO DE USUÁRIO: O usuário SOMENTE visualiza e edita suas próprias informações.
 * 4. BLINDAGEM DE PERFIL: O usuário não pode alterar seu próprio perfil, cargo ou nível de acesso.
 * 5. CRIPTOGRAFIA: Alterações de senha utilizam hash BCRYPT via password_hash().
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perfil.php';
require_once __DIR__ . '/../models/Instituicao.php';

class ConfiguracoesController {

    /**
     * Valida se existe um usuário autenticado na sessão do PHP
     */
    private function protegerAcesso() {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        // Se não houver usuário autenticado na sessão, redireciona imediatamente para o login
        if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
            header('Location: ' . url('login'));
            exit;
        }
    }

    /**
     * Ponto de entrada principal da tela de Configurações
     */
    public function index() {
        // 1. Garante que apenas usuários logados acessem
        $this->protegerAcesso();

        $usuarioSessao = $_SESSION['user'];
        $usuarioId     = (int)$usuarioSessao['id'];

        // 2. Busca dados frescos do usuário diretamente no banco de dados
        $usuarioBanco = Usuario::buscarPorId($usuarioId);

        // Se por alguma razão o usuário foi excluído do banco enquanto a sessão estava ativa
        if (!$usuarioBanco) {
            // Limpa os dados da sessão sem destruí-la abruptamente
            unset($_SESSION['user']);
            header('Location: ' . url('login'));
            exit;
        }

        // 3. Identifica a instituição vinculada ao usuário
        $instituicaoId = (int)($usuarioBanco['instituicao_id'] ?? 1);
        $instituicao   = Instituicao::buscarPorId($instituicaoId);
        $instituicaoNome   = $instituicao ? $instituicao['nome'] : 'Instituição ÂNCORA';
        $instituicaoCodigo = Instituicao::formatarCodigo($instituicaoId);

        // 4. Processamento de Requisições POST (Ações do Usuário)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            try {
                // Ação A: Edição do Perfil (Nome do Usuário)
                if ($action === 'editar_perfil') {
                    $novoNome = trim($_POST['nome'] ?? '');

                    // Validação de preenchimento obrigatório
                    if (empty($novoNome)) {
                        $_SESSION['flash_error'] = "Por favor, informe seu nome completo.";
                    } elseif (mb_strlen($novoNome) < 3) {
                        $_SESSION['flash_error'] = "O nome deve possuir no mínimo 3 caracteres.";
                    } else {
                        // Atualiza no banco de dados via Prepared Statement
                        Usuario::atualizarNome($usuarioId, $novoNome);

                        // Sincroniza a sessão ativa do usuário
                        $_SESSION['user']['nome'] = $novoNome;
                        $_SESSION['flash_success'] = "Nome atualizado com sucesso!";
                    }
                }

                // Ação B: Alteração de E-mail
                elseif ($action === 'editar_email') {
                    $novoEmail = trim($_POST['email'] ?? '');

                    // Validação de preenchimento e formato
                    if (empty($novoEmail)) {
                        $_SESSION['flash_error'] = "Por favor, informe um endereço de e-mail.";
                    } elseif (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
                        $_SESSION['flash_error'] = "Por favor, informe um e-mail válido.";
                    } else {
                        // Atualiza o e-mail no banco com validação de unicidade
                        Usuario::atualizarEmailProprio($usuarioId, $novoEmail);

                        // Sincroniza o e-mail na sessão ativa
                        $_SESSION['user']['email'] = $novoEmail;
                        $_SESSION['flash_success'] = "Endereço de e-mail atualizado com sucesso!";
                    }
                }

                // Ação C: Alteração de Senha
                elseif ($action === 'alterar_senha') {
                    $senhaAtual     = $_POST['senha_atual'] ?? '';
                    $novaSenha      = $_POST['nova_senha'] ?? '';
                    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

                    // Validações obrigatórias
                    if (empty($senhaAtual) || empty($novaSenha) || empty($confirmarSenha)) {
                        $_SESSION['flash_error'] = "Por favor, preencha todos os campos para alterar sua senha.";
                    } elseif (strlen($novaSenha) < 6) {
                        $_SESSION['flash_error'] = "A nova senha deve possuir no mínimo 6 caracteres.";
                    } elseif ($novaSenha !== $confirmarSenha) {
                        $_SESSION['flash_error'] = "A confirmação não confere com a nova senha digitada.";
                    } elseif (!Usuario::verificarSenhaAtual($usuarioId, $senhaAtual)) {
                        // Validação criptográfica da senha atual
                        $_SESSION['flash_error'] = "A senha atual informada está incorreta.";
                    } else {
                        // Grava a nova senha com BCRYPT
                        Usuario::redefinirSenha($usuarioId, $novaSenha);
                        $_SESSION['flash_success'] = "Sua senha foi alterada com sucesso!";
                    }
                }
            } catch (Exception $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }

            // Redireciona via GET para evitar reenvio de formulário ao atualizar (Post-Redirect-Get)
            header('Location: ' . url('configuracoes'));
            exit;
        }

        // 5. Preparação dos dados para a View
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $userName    = $usuarioBanco['nome'] ?? 'Usuário';
        $userEmail   = $usuarioBanco['email'] ?? '';
        $perfilNome  = $usuarioBanco['perfil_nome'] ?? 'Usuário';
        $perfilSlug  = mb_strtolower(trim($perfilNome));

        // Determinação dinâmica do departamento/setor conforme o perfil institucional
        if ($perfilSlug === 'administrador') {
            $userDepartment  = 'Diretoria';
            $userRoleTitle   = 'Administrador';
            $userAccessLevel = 'Administrador';
            $userPrefix      = 'Dir. ';
        } elseif ($perfilSlug === 'professor') {
            $userDepartment  = 'Ciência da Computação';
            $userRoleTitle   = 'Professor';
            $userAccessLevel = 'Professor';
            $userPrefix      = 'Prof. ';
        } elseif ($perfilSlug === 'aluno') {
            $userDepartment  = 'Ciência da Computação';
            $userRoleTitle   = 'Aluno';
            $userAccessLevel = 'Aluno';
            $userPrefix      = '';
        } elseif ($perfilSlug === 'funcionario' || $perfilSlug === 'funcionário') {
            $userDepartment  = 'Administração';
            $userRoleTitle   = 'Funcionário';
            $userAccessLevel = 'Funcionário';
            $userPrefix      = '';
        } else {
            $userDepartment  = 'Geral';
            $userRoleTitle   = $perfilNome;
            $userAccessLevel = $perfilNome;
            $userPrefix      = '';
        }

        // Iniciais dinâmicas para o avatar circular (ex: 'Roberto Lima' -> 'RL')
        $nomePartes   = explode(' ', trim($userName));
        $firstChar    = !empty($nomePartes[0]) ? mb_strtoupper(mb_substr($nomePartes[0], 0, 1)) : 'U';
        $secondChar   = (isset($nomePartes[1]) && !empty($nomePartes[1])) ? mb_strtoupper(mb_substr($nomePartes[1], 0, 1)) : '';
        $userInitials = $firstChar . $secondChar;

        $pageTitle = "Configurações — ÂNCORA";

        require __DIR__ . '/../views/configuracoes/index.php';
    }
}
