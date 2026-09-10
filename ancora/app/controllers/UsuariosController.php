<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Controller responsável pela Gestão de Usuários (Acesso exclusivo do Administrador com Isolamento de Instituição / Multi-Tenancy)
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perfil.php';

class UsuariosController {

    /**
     * Valida autenticação e permissão de Administrador
     */
    private function protegerAcesso() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: ' . url('login'));
            exit;
        }

        $perfilNome = mb_strtolower(trim($_SESSION['user']['perfil_nome'] ?? ''));
        if (empty($perfilNome) && isset($_SESSION['user']['perfil_id'])) {
            $perfil = Perfil::buscarPorId((int)$_SESSION['user']['perfil_id']);
            $perfilNome = $perfil ? mb_strtolower(trim($perfil['nome'])) : '';
        }

        if ($perfilNome !== 'administrador') {
            if ($perfilNome === 'aluno') {
                header('Location: ' . url('aluno'));
            } elseif ($perfilNome === 'professor') {
                header('Location: ' . url('professor'));
            } elseif ($perfilNome === 'funcionario' || $perfilNome === 'funcionário') {
                header('Location: ' . url('funcionario'));
            } else {
                header('Location: ' . url('login'));
            }
            exit;
        }
    }

    public function index() {
        $this->protegerAcesso();

        $usuarioLogado = $_SESSION['user'];
        $userName      = $usuarioLogado['nome'] ?? 'Administrador';
        $userRole      = 'Administrador';
        $userSector    = 'Diretoria';
        $instituicaoId = (int)($usuarioLogado['instituicao_id'] ?? 1);

        $nomePartes   = explode(' ', trim($userName));
        $firstChar    = !empty($nomePartes[0]) ? mb_strtoupper(mb_substr($nomePartes[0], 0, 1)) : 'A';
        $secondChar   = (isset($nomePartes[1]) && !empty($nomePartes[1])) ? mb_strtoupper(mb_substr($nomePartes[1], 0, 1)) : '';
        $userInitials = $firstChar . $secondChar;

        $errorMsg = '';
        $successMsg = '';

        // Processa Ações POST (Criar, Editar, Alternar Status)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'criar') {
                $nome       = trim($_POST['nome'] ?? '');
                $email      = trim($_POST['email'] ?? '');
                $senha      = $_POST['senha'] ?? '';
                $perfilNome = trim($_POST['perfil_nome'] ?? '');

                if (empty($nome) || empty($email) || empty($senha) || empty($perfilNome)) {
                    $errorMsg = "Por favor, preencha todos os campos obrigatórios.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errorMsg = "E-mail inválido.";
                } elseif (strlen($senha) < 6) {
                    $errorMsg = "A senha inicial deve possuir no mínimo 6 caracteres.";
                } elseif (strtolower($perfilNome) === 'administrador') {
                    $errorMsg = "Não é permitido criar administradores por esta tela.";
                } else {
                    $usuarioExistente = Usuario::buscarPorEmail($email);
                    if ($usuarioExistente) {
                        $errorMsg = "Este e-mail já está cadastrado.";
                    } else {
                        $perfilObj = Perfil::buscarPorNome($perfilNome);
                        if (!$perfilObj) {
                            $errorMsg = "Perfil selecionado é inválido.";
                        } else {
                            try {
                                // Associa automaticamente o novo usuário à MESMA instituição do Administrador logado
                                Usuario::criar($nome, $email, $senha, (int)$perfilObj['id'], $instituicaoId);
                                $successMsg = "Usuário '{$nome}' cadastrado com sucesso!";
                            } catch (Exception $e) {
                                $errorMsg = "Erro ao cadastrar usuário: " . $e->getMessage();
                            }
                        }
                    }
                }
            } elseif ($action === 'editar') {
                $id         = (int)($_POST['id'] ?? 0);
                $nome       = trim($_POST['nome'] ?? '');
                $email      = trim($_POST['email'] ?? '');
                $perfilNome = trim($_POST['perfil_nome'] ?? '');
                $status     = trim($_POST['status'] ?? 'ativo');

                // Valida se o usuário alvo pertence à MESMA instituição do Administrador
                $usuarioAtual = Usuario::buscarPorId($id, $instituicaoId);

                if (!$usuarioAtual) {
                    $errorMsg = "Acesso negado: Usuário não encontrado ou pertence a outra instituição.";
                } elseif (empty($nome) || empty($email) || empty($perfilNome)) {
                    $errorMsg = "Por favor, preencha todos os campos obrigatórios.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errorMsg = "E-mail inválido.";
                } elseif ((int)$id === (int)$usuarioLogado['id'] && ($status === 'inativo' || strtolower($perfilNome) !== 'administrador')) {
                    $errorMsg = "Você não pode desativar ou alterar o perfil da sua própria conta de Administrador.";
                } else {
                    // Se tentar desativar um administrador, verificar se é o único daquela instituição
                    if (strtolower($usuarioAtual['perfil_nome']) === 'administrador' && $status === 'inativo') {
                        $adminsAtivos = Usuario::contarAdministradoresAtivos($instituicaoId);
                        if ($adminsAtivos <= 1) {
                            $errorMsg = "Não é possível desativar o único administrador da instituição.";
                        }
                    }

                    if (empty($errorMsg)) {
                        // Verificar se alterou o e-mail para um já existente
                        $outroUsuario = Usuario::buscarPorEmail($email);
                        if ($outroUsuario && (int)$outroUsuario['id'] !== (int)$id) {
                            $errorMsg = "Este e-mail já está cadastrado.";
                        } else {
                            if (strtolower($usuarioAtual['perfil_nome']) !== 'administrador' && strtolower($perfilNome) === 'administrador') {
                                $errorMsg = "Não é permitido alterar o perfil de um usuário para Administrador nesta tela.";
                            } else {
                                $perfilObj = Perfil::buscarPorNome($perfilNome);
                                if (!$perfilObj) {
                                    $errorMsg = "Perfil selecionado é inválido.";
                                } else {
                                    try {
                                        Usuario::atualizar($id, $nome, $email, (int)$perfilObj['id'], $status, $instituicaoId);
                                        $successMsg = "Dados do usuário '{$nome}' atualizados com sucesso!";
                                    } catch (Exception $e) {
                                        $errorMsg = "Erro ao atualizar usuário: " . $e->getMessage();
                                    }
                                }
                            }
                        }
                    }
                }
            } elseif ($action === 'toggle_status') {
                $id     = (int)($_POST['id'] ?? 0);
                $status = trim($_POST['status'] ?? 'ativo');

                // Valida se o usuário alvo pertence à MESMA instituição do Administrador
                $targetUser = Usuario::buscarPorId($id, $instituicaoId);

                if (!$targetUser) {
                    $errorMsg = "Acesso negado: Usuário não encontrado ou pertence a outra instituição.";
                } elseif ((int)$id === (int)$usuarioLogado['id'] && $status === 'inativo') {
                    $errorMsg = "Você não pode desativar seu próprio acesso.";
                } elseif (strtolower($targetUser['perfil_nome']) === 'administrador' && $status === 'inativo' && Usuario::contarAdministradoresAtivos($instituicaoId) <= 1) {
                    $errorMsg = "Não é possível desativar o único administrador da instituição.";
                } else {
                    try {
                        Usuario::alterarStatus($id, $status, $instituicaoId);
                        $statusTxt = $status === 'inativo' ? 'desativado' : 'reativado';
                        $successMsg = "Status do usuário atualizado para {$statusTxt}!";
                    } catch (Exception $e) {
                        $errorMsg = "Erro ao alterar status: " . $e->getMessage();
                    }
                }
            }
        }

        // Buscar lista atualizada e contagens filtradas ESTRITAMENTE pela instituição do Administrador
        $usuarios = Usuario::listarTodosComPerfil($instituicaoId);
        $counts   = Usuario::contarPorPerfil($instituicaoId);

        $pageTitle = "Gestão de Usuários — ÂNCORA";

        require __DIR__ . '/../views/admin/usuarios.php';
    }
}
