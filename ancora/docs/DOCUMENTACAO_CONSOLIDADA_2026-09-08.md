# DOCUMENTAÇÃO CONSOLIDADA DO PROJETO ÂNCORA

> **Data de Atualização**: 2026-09-08  
> **Projeto**: ÂNCORA - Sistema de Gestão Institucional e Escolar  
> **Objetivo**: Registro técnico consolidado do estado real e atualizado do sistema (TCC).

---

## 1. Visão Geral e Arquitetura

O **ÂNCORA** é uma plataforma web para gestão acadêmica e administrativa escolar que integra autenticação multi-perfil, controle de turmas, gestão de usuários com isolamento **Multi-Tenancy**, centro de notificações em tempo real e módulo completo de tarefas acadêmicas.

### Stack Tecnológica
- **Language & Runtime**: PHP 8.2 (Arquitetura MVC com Front Controller centralizado).
- **Banco de Dados**: MySQL (Driver PDO, codificação `utf8mb4`, prepared statements em 100% das queries).
- **Frontend**: HTML5, CSS3 modular (`admin.css`, `auth.css`, `home.css`), Vanilla JavaScript (`admin.js`, `auth.js`, `home.js`).
- **Design System & Temas**: Suporte a temas Claro (Light) e Escuro (Dark) com alternância dinâmica, persistência em `localStorage` e variáveis CSS nativas.
- **Serviços de E-mail**: `MailerService` nativo (envio transacional do código de recuperação via `mail()` e registro de auditoria local em `storage/logs/mail.log`).

---

## 2. Topologia de Arquivos do Projeto

```
ancora/
├── .env                                       # Configurações do ambiente ativo (Local / BD / API / SMTP)
├── .env.example                               # Exemplo de configuração de variáveis
├── .htaccess                                  # Regras de segurança Apache (bloqueio de .env, logs, .sql)
├── index.php                                  # Front Controller principal e roteador MVC
├── .github/
│   └── workflows/
│       └── ci-cd.yml                          # Workflow de CI/CD para GitHub Actions
├── app/                                       # Camada MVC da aplicação (Controllers, Models, Services, Views)
│   ├── controllers/                           # 16 Controladores (Auth, Admin, Turmas, Tarefas, etc.)
│   ├── models/                                # 6 Modelos PDO (Usuario, Turma, Tarefa, Instituicao, etc.)
│   ├── services/                              # Serviços (MailerService, NotificationService)
│   └── views/                                 # Views separadas por módulo e perfil
├── config/                                    # Configurações do ambiente e conexões (PDO, Brevo, OpenSearch)
├── database/                                  # DDL consolidado (ancora.sql) e migrações (001 a 007)
├── docs/                                      # Documentação técnica consolidada
│   └── DOCUMENTACAO_CONSOLIDADA_2026-09-08.md
├── public/                                    # Assets estáticos (CSS, JS, Imagens) e Front Controller público
├── storage/                                   # Armazenamento de logs e uploads de arquivos
└── tests/                                     # Suíte de testes automatizados E2E e validações CI/CD
    ├── README.md                              # Guia completo dos testes e integração CI/CD
    ├── run_all_tests.php                      # Runner central de testes (Exit Code 0/1)
    ├── test_instituicao_format.php            # Teste unitário de código institucional
    ├── test_security_rules.php                # Teste unitário de regras de segurança/BCRYPT
    ├── test_url_helpers.php                   # Teste unitário de roteamento e URLs (url/asset)
    └── validate_syntax.php                    # Validador automatizado de sintaxe PHP (PHP Lint)
```

---

## 3. Matriz de Funcionalidades e Grau de Conclusão

Legenda dos Graus de Conclusão:
- 🟢 **Funcional (100%)**: Implementado, integrado ao banco de dados e validado com testes.
- 🟡 **Em Desenvolvimento**: Interface/estruturas parciais criadas, pendente de finalização dos fluxos ou integrações.
- 🔴 **Ainda Não Implementada**: Rota reservada no roteador principal, pendente de controller e views.

| Módulo / Categoria | Funcionalidade Específica | Rota / Componente | Grau de Conclusão |
| :--- | :--- | :--- | :---: |
| **Autenticação & Segurança** | Autenticação de Usuários (Login BCRYPT / Sessão) | `/login` | 🟢 Funcional (100%) |
| | Auto-cadastro de Instituição e Administrador | `/cadastro` | 🟢 Funcional (100%) |
| | Troca Forçada de Senha Provisória (Primeiro Acesso) | `/primeiro-acesso` | 🟢 Funcional (100%) |
| | Solicitação de Recuperação de Senha por E-mail | `/recuperar-senha` | 🟡 Em Desenvolvimento |
| | Validação de Código de Recuperação de 6 Dígitos | `/verificar-codigo` | 🟡 Em Desenvolvimento |
| | Cadastramento de Nova Senha Pessoal (Redefinição) | `/redefinir-senha` | 🟡 Em Desenvolvimento |
| | Encerramento Seguro de Sessão (Logout) | `/logout` | 🟢 Funcional (100%) |
| **Arquitetura & Core** | Front Controller & MVC Router Centralizado | `index.php` / `public/index.php` | 🟢 Funcional (100%) |
| | Auth Guard Global (Proteção de Rotas & JSON 401 AJAX) | `index.php` | 🟢 Funcional (100%) |
| | Controle de Acesso Baseado em Perfis (RBAC - 4 Perfis) | Controllers / Views | 🟢 Funcional (100%) |
| | Segregação Lógica Multi-Tenancy (`instituicao_id`) | PDO Models | 🟢 Funcional (100%) |
| **Gestão de Usuários** | Listagem de Membros com Paginação e Filtros | `/usuarios` | 🟢 Funcional (100%) |
| | Cadastro de Professores, Alunos e Funcionários | `/usuarios` | 🟢 Funcional (100%) |
| | Edição de Dados, Perfis e Alteração de Status (Ativo/Inativo) | `/usuarios` | 🟢 Funcional (100%) |
| | Trava de Auto-exclusão e Elevação Ilícita de Privilégios | `Usuario.php` | 🟢 Funcional (100%) |
| **Gestão de Turmas** | Criação, Edição e Exclusão de Turmas | `/turmas` | 🟢 Funcional (100%) |
| | Vinculação de Professores e Disciplinas à Turma | `/turmas` | 🟢 Funcional (100%) |
| | Matriculamento e Remoção de Alunos na Turma | `/turmas` | 🟢 Funcional (100%) |
| | Visualização da Lista de Membros da Turma | `/turmas` | 🟢 Funcional (100%) |
| **Configurações Globais** | Visualização de Perfil e Avatar com Iniciais Dinâmicas | `/configuracoes` | 🟢 Funcional (100%) |
| | Alteração de Nome Próprio com Sincronização na Sessão | `/configuracoes` | 🟢 Funcional (100%) |
| | Código da Instituição (`# ANC-0001`) com Cópia para Clipboard | `/configuracoes` | 🟢 Funcional (100%) |
| | Alteração de E-mail com Validação de Duplicidade no BD | `/configuracoes` | 🟢 Funcional (100%) |
| | Alteração Segura de Senha (com validação de senha atual) | `/configuracoes` | 🟢 Funcional (100%) |
| | Alternância de Tema Dark/Light e Persistência Local | Global / `/configuracoes` | 🟢 Funcional (100%) |
| **Central de Notificações** | Geração Automática de Notificações de Atividades e Notas | `NotificationService.php` | 🟢 Funcional (100%) |
| | API de Polling em Tempo Real para Alertas | `/notificacoes/poll` | 🟢 Funcional (100%) |
| | Marcação de Notificações como Lidas / Ações Rápidas | `/notificacoes/action` | 🟢 Funcional (100%) |
| | Painel Geral de Notificações do Usuário | `/notificacoes` | 🟢 Funcional (100%) |
| **Módulo de Tarefas** | Criação e Edição de Tarefas com Descrição e Prazos | `/tarefas/criar`, `/tarefas/editar` | 🟢 Funcional (100%) |
| | Questionários Interativos (Múltipla Escolha, V/F, Discursivas) | `/tarefas` | 🟢 Funcional (100%) |
| | Alocação por Turmas ou Alunos com Deduplicação de Destinatários | `Tarefa.php` | 🟢 Funcional (100%) |
| | Anexo de Materiais Didáticos pelo Criador | `/tarefas/download-material` | 🟢 Funcional (100%) |
| | Submissão de Entregas (Respostas em Texto e Anexos) | `/tarefas/submeter` | 🟢 Funcional (100%) |
| | Bloqueio de Prazo no Servidor (Backend Enforcement) | `Tarefa.php` | 🟢 Funcional (100%) |
| | Auto-correção de Questões Objetivas | `Tarefa.php` | 🟢 Funcional (100%) |
| | Correção Manual, Feedback e Atribuição de Notas pelo Professor | `/tarefas/corrigir` | 🟢 Funcional (100%) |
| | Devolução da Tarefa ao Aluno com Alerta de Nota | `/tarefas/corrigir` | 🟢 Funcional (100%) |
| | Download Seguro de Arquivos Entregues pelos Alunos | `/tarefas/download-entrega` | 🟢 Funcional (100%) |
| **Dashboards por Perfil** | Painel do Administrador (Métricas Institucionais) | `/admin` | 🟢 Funcional (100%) |
| | Painel do Professor (Gestão de Turmas e Atividades) | `/professor` | 🟢 Funcional (100%) |
| | Painel do Aluno (Tarefas Pendentes, Avaliadas e Avisos) | `/aluno` | 🟢 Funcional (100%) |
| | Painel do Funcionário (Visão Geral Operacional) | `/funcionario` | 🟢 Funcional (100%) |
| **Landing Page** | Apresentação Institucional, Tabela de Planos e Tema | `/home` | 🟢 Funcional (100%) |
| | FAQ Interativo em Accordion JS | `/home` | 🟢 Funcional (100%) |
| **Serviços & Infraestrutura** | Disparo Nativo de E-mails Transacionais com Registro em Log | `MailerService.php` | 🟢 Funcional (100%) |
| **Módulos Futuros** | Módulo de Eventos Acadêmicos e Calendário | `/eventos` | 🔴 Ainda Não Implementada |
| | Módulo de Mensagens Internas / Chat | `/mensagens` | 🔴 Ainda Não Implementada |
| | Módulo de Reserva de Espaços e Equipamentos | `/reservas` | 🔴 Ainda Não Implementada |
| | Módulo de Achados e Perdidos no Campus | `/achados-perdidos` | 🔴 Ainda Não Implementada |

---

## 4. Esquema de Banco de Dados e Migrações

A estrutura de tabelas no MySQL está consolidada com 7 migrações ativas:
1. `001_add_primeiro_acesso_to_usuarios.sql`: Controle de senha provisória.
2. `002_create_instituicoes_table.sql`: Cadastro de instituições e código formatado.
3. `003_add_instituicao_id_to_usuarios.sql`: Suporte a Multi-Tenancy.
4. `004_create_password_resets_table.sql`: Tokens de recuperação de senha.
5. `005_create_turmas_tables.sql`: Estrutura de turmas, docentes e discentes.
6. `006_create_notificacoes_table.sql`: Tabela de notificações e alertas.
7. `007_create_tarefas_tables.sql`: Tabelas de tarefas, perguntas, entregas e respostas.

---

## 5. Suíte de Testes Automatizados para CI/CD (Cobria Total dos Módulos)

- `php tests/validate_syntax.php`: Validação automatizada de sintaxe PHP (PHP Lint) em 100% dos arquivos `.php` (58 arquivos analisados).
- `php tests/run_all_tests.php`: Runner central que executa todas as suítes de validação de regras de negócio dos 8 módulos do sistema (51 validações ativas, Exit Code 0/1).
- `php tests/test_auth_rules.php`: Validação dos fluxos de Login, Cadastro, Primeiro Acesso, Tokens de 6 dígitos e BCRYPT.
- `php tests/test_rbac_multitenancy.php`: Validação dos 4 perfis de acesso RBAC (Admin, Prof, Aluno, Func) e isolamento Multi-Tenancy.
- `php tests/test_usuarios_turmas.php`: Validação de travas de segurança (auto-exclusão, auto-elevação) e alocação de turmas/disciplinas.
- `php tests/test_configuracoes_notificacoes.php`: Validação do gerador de iniciais de avatar, temas Dark/Light e payload de notificações.
- `php tests/test_tarefas_module.php`: Validação de questionários (múltipla escolha, V/F, discursivas), auto-correção, bloqueio de prazo e deduplicação.
- `php tests/test_url_helpers.php`: Validação do gerador de URLs e roteamento MVC (`url()` e `asset()`).
- `php tests/test_instituicao_format.php`: Validação da regra de código institucional (`Instituicao::formatarCodigo()`).
- `php tests/test_security_rules.php`: Validação do algoritmo BCRYPT, sanitização de números e regex de e-mails.

---
*Documentação gerada automaticamente para manutenção da rastreabilidade técnica do TCC ÂNCORA.*
