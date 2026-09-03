<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Controller do Dashboard do Funcionário (/funcionario)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Gerenciar o ambiente próprio do perfil Funcionário, fornecendo acesso às funcionalidades
 * operacionais institucionais como Achados e Perdidos, eventos e avisos.
 * 
 * REGRAS DE SEGURANÇA E PERMISSÕES:
 * 1. AUTENTICAÇÃO: Exige sessão ativa ($_SESSION['user']).
 * 2. AUTORIZAÇÃO POR PERFIL: Exclusivo para perfil 'Funcionário' (perfil_id = 4).
 *    Usuários com perfis Administrador, Professor ou Aluno são bloqueados e redirecionados.
 * 3. ISOLAMENTO MULTI-TENANCY: Dados filtrados estritamente pela instituição do funcionário logado.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perfil.php';

class FuncionarioController {

    /**
     * Valida se o usuário logado possui a permissão estrita de Funcionário.
     */
    protected function protegerAcesso() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Exige autenticação prévia no sistema
        if (!isset($_SESSION['user'])) {
            header('Location: ' . url('login'));
            exit;
        }

        $usuarioLogado = $_SESSION['user'];

        // 2. Garante que o perfil seja estritamente 'Funcionário'
        $perfilNome = mb_strtolower(trim($usuarioLogado['perfil_nome'] ?? ''));
        if (empty($perfilNome) && isset($usuarioLogado['perfil_id'])) {
            $perfil = Perfil::buscarPorId((int)$usuarioLogado['perfil_id']);
            $perfilNome = $perfil ? mb_strtolower(trim($perfil['nome'])) : '';
        }

        if ($perfilNome !== 'funcionario' && $perfilNome !== 'funcionário') {
            if ($perfilNome === 'administrador') {
                header('Location: ' . url('admin'));
            } elseif ($perfilNome === 'professor') {
                header('Location: ' . url('professor'));
            } elseif ($perfilNome === 'aluno') {
                header('Location: ' . url('aluno'));
            } else {
                header('Location: ' . url('login'));
            }
            exit;
        }
    }

    public function index() {
        $this->protegerAcesso();

        $usuarioLogado = $_SESSION['user'];
        $instituicaoId = (int)($usuarioLogado['instituicao_id'] ?? 1);

        // Dados do Funcionário para o Header/Sidebar
        $userName       = $usuarioLogado['nome'] ?? 'Funcionário';
        $userRole       = 'Funcionário';
        $userDepartment = 'Administração';

        // Iniciais dinâmicas para o avatar circular (ex: 'Fernanda Rocha' -> 'FR')
        $nomePartes   = explode(' ', trim($userName));
        $firstChar    = !empty($nomePartes[0]) ? mb_strtoupper(mb_substr($nomePartes[0], 0, 1)) : 'F';
        $secondChar   = (isset($nomePartes[1]) && !empty($nomePartes[1])) ? mb_strtoupper(mb_substr($nomePartes[1], 0, 1)) : '';
        $userInitials = $firstChar . $secondChar;

        $pageTitle = "Dashboard Funcionário — ÂNCORA";

        require __DIR__ . '/../views/funcionario/dashboard.php';
    }
}
