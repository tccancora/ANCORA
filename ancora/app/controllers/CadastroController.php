<?php
/**
 * Controller responsável pela exibição e processamento do fluxo de Cadastro ("Criar Instituição")
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perfil.php';
require_once __DIR__ . '/../models/Instituicao.php';

class CadastroController {

    public function index() {
        $pageTitle = "Criar nova instituição — ÂNCORA";
        $errorMsg = '';
        $successMsg = '';

        // Campos preservados para renderização
        $nomeInstituicao = '';
        $nomeResponsavel = '';
        $email           = '';

        // Processa envio do formulário via método POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nomeInstituicao = trim($_POST['nome_instituicao'] ?? '');
            $nomeResponsavel = trim($_POST['nome_responsavel'] ?? '');
            $email           = trim($_POST['email'] ?? '');
            $senha           = $_POST['senha'] ?? '';
            $confirmarSenha  = $_POST['confirmar_senha'] ?? '';

            // 1. Validação no servidor: Campos obrigatórios
            if (empty($nomeInstituicao) || empty($nomeResponsavel) || empty($email) || empty($senha) || empty($confirmarSenha)) {
                $errorMsg = "Por favor, preencha todos os campos obrigatórios.";
            }
            // 2. Validação do formato do e-mail
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMsg = "Por favor, informe um endereço de e-mail válido.";
            }
            // 3. Validação do tamanho mínimo da senha
            elseif (strlen($senha) < 6) {
                $errorMsg = "A senha deve possuir no mínimo 6 caracteres.";
            }
            // 4. Confirmação de senha
            elseif ($senha !== $confirmarSenha) {
                $errorMsg = "A confirmação de senha não confere com a senha digitada.";
            }
            else {
                try {
                    // 5. Verificar se o e-mail já existe no MySQL
                    $usuarioExistente = Usuario::buscarPorEmail($email);
                    if ($usuarioExistente) {
                        $errorMsg = "O e-mail informado já está cadastrado no sistema.";
                    } else {
                        // 6. Criar a Instituição no banco de dados
                        $instituicaoId = Instituicao::criar($nomeInstituicao, null, $email);

                        // 7. Atribuir automaticamente o perfil 'Administrador'
                        $perfilAdmin = Perfil::buscarPorNome('Administrador');
                        if (!$perfilAdmin) {
                            throw new Exception("Perfil 'Administrador' não encontrado na tabela de perfis.");
                        }
                        $perfilId = (int)$perfilAdmin['id'];

                        // 8. Criar usuário Administrador vinculado à nova instituição
                        $userId = Usuario::criar($nomeResponsavel, $email, $senha, $perfilId, $instituicaoId);

                        if ($userId) {
                            $successMsg = "Instituição e conta de Administrador criadas com sucesso! Você já pode realizar o login.";
                            // Limpa os campos após o sucesso
                            $nomeInstituicao = '';
                            $nomeResponsavel = '';
                            $email           = '';
                        } else {
                            $errorMsg = "Ocorreu um erro ao cadastrar a instituição. Tente novamente.";
                        }
                    }
                } catch (Exception $e) {
                    $errorMsg = "Erro no servidor de banco de dados: " . $e->getMessage();
                }
            }
        }

        require __DIR__ . '/../views/auth/cadastro.php';
    }
}
