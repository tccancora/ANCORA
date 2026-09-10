# Estrutura do Banco de Dados - ÂNCORA

Este diretório contém os scripts de criação e a documentação do banco de dados MySQL para o sistema **ÂNCORA**.

---

## 1. Informações Gerais

- **Nome do Banco:** `ancora`
- **Engine:** `InnoDB`
- **Charset:** `utf8mb4`
- **Collation:** `utf8mb4_unicode_ci`

---

## 2. Como Importar no HeidiSQL

1. Abra o **HeidiSQL** e conecte-se ao seu servidor MySQL local (ex: XAMPP).
2. Clique em **Arquivo** > **Carregar arquivo SQL...** (ou pressione `Ctrl + O`).
3. Selecione o arquivo `database/ancora.sql`.
4. Pressione `F9` ou clique no botão **Executar SQL** para rodar a criação do banco e das tabelas.
5. Atualize a exibição do HeidiSQL (`F5`) para confirmar a criação da base `ancora`.

---

## 3. Credenciais Padrão (XAMPP)

- **Host:** `localhost`
- **Porta:** `3306`
- **Usuário:** `root`
- **Senha:** *(vazia por padrão no XAMPP)*

---

## 4. Estrutura das Tabelas

### `perfis`
Armazena os perfis de acesso ao sistema.

| Campo | Tipo | Nulo | Chave | Descrição |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | Não | PRIMARY | Identificador único (Auto Increment) |
| `nome` | VARCHAR(50) | Não | UNIQUE | Nome único do perfil |
| `descricao` | VARCHAR(255) | Sim | | Descrição das permissões do perfil |
| `created_at` | TIMESTAMP | Sim | | Data/hora de criação |
| `updated_at` | TIMESTAMP | Sim | | Data/hora da última atualização |

**Perfis inseridos por padrão:**
1. `Administrador` (Representa o cargo de **Diretor** no ÂNCORA)
2. `Professor`
3. `Aluno`
4. `Funcionario`

---

### `usuarios`
Armazena as contas dos usuários do sistema.

| Campo | Tipo | Nulo | Chave | Descrição |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | Não | PRIMARY | Identificador único (Auto Increment) |
| `nome` | VARCHAR(150) | Não | | Nome completo do usuário |
| `email` | VARCHAR(150) | Não | UNIQUE | E-mail do usuário |
| `senha` | VARCHAR(255) | Não | | Hash da senha (gerada via `password_hash`) |
| `perfil_id` | BIGINT UNSIGNED | Não | FOREIGN | FK referenciando `perfis(id)` |
| `instituicao_id` | BIGINT UNSIGNED | Não | FOREIGN | FK referenciando `instituicoes(id)` |
| `status` | ENUM('ativo','inativo') | Não | | Status de acesso da conta |
| `primeiro_acesso` | TINYINT(1) | Não | | Flag de primeiro acesso para troca de senha |
| `created_at` | TIMESTAMP | Sim | | Data/hora de criação |
| `updated_at` | TIMESTAMP | Sim | | Data/hora da última atualização |

---

### Módulo de Tarefas e Atividades

O banco de dados conta com a estrutura completa para o módulo de tarefas, suportando tarefas tradicionais, questionários interativos e materiais de apoio.

1. **`tarefas`**: Armazena as tarefas e atividades criadas por professores/administradores (título, descrição, prazo, tipo de atividade).
2. **`tarefa_destinatarios`**: Vincula a tarefa a turmas específicas e/ou alunos individuais.
3. **`tarefa_materiais`**: Armazena os anexos e materiais de apoio carregados pelos docentes.
4. **`tarefa_questoes`**: Guarda as questões do questionário ÂNCORA (múltipla escolha, V/F, discursiva, etc.) em formato JSON.
5. **`tarefa_entregas`**: Registra as entregas feitas pelos alunos, notas atribuídas, status (pendente, entregue, corrigida, devolvida) e feedbacks.
6. **`tarefa_entrega_arquivos`**: Armazena arquivos anexados pelos alunos na entrega da tarefa.
7. **`tarefa_entrega_respostas`**: Armazena as respostas fornecidas pelos alunos às questões do questionário e os pontos obtidos.

---

## 5. Relacionamento

- **PERFIS (1) : (N) USUARIOS**: Um perfil possui múltiplos usuários.
- **INSTITUICOES (1) : (N) USUARIOS / TURMAS / TAREFAS**: Segregação lógica por instituição (Multi-Tenancy).
- **TAREFAS (1) : (N) DESTINATARIOS / MATERIAIS / QUESTOES / ENTREGAS**: Estrutura relacional do módulo acadêmico de tarefas.

---

## 6. Testando a Conexão PHP (PDO)

A conexão está configurada de forma centralizada em [`config/database.php`](../config/database.php).

Exemplo de utilização em PHP:

```php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDatabaseConnection();
    echo "Conexão com o banco 'ancora' estabelecida com sucesso!";
} catch (Exception $e) {
    echo "Erro de Conexão: " . $e->getMessage();
}
```
