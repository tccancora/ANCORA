# ÂNCORA - Estrutura de Testes Automatizados e Validação para CI/CD

Este diretório contém a suíte de testes de validação automatizada e verificação de sintaxe PHP do **Projeto ÂNCORA**, projetada especialmente para execução em ambientes de Integração Contínua (CI) via **GitHub Actions**.

---

## 🎯 Para que servem os testes?

Os testes garantem a integridade das regras de negócio, funções de suporte e segurança do sistema sem dependências externas (como bancos de dados MySQL ativos ou servidores web), garantindo que alterações no código não introduzam regressões nem erros de sintaxe.

---

## 🛠️ Como executar os testes localmente?

Você pode executar os testes diretamente pelo interpretador PHP no terminal (Windows, Linux ou macOS):

### 1. Validação de Sintaxe PHP (PHP Lint)
Verifica 100% dos arquivos `.php` do projeto buscando erros de sintaxe, chaves ou parênteses ausentes.

```bash
php tests/validate_syntax.php
```

### 2. Suíte de Testes Funcionais e Regras de Negócio
Executa os testes de unidade e validação das regras de negócio do ÂNCORA.

```bash
php tests/run_all_tests.php
```

---

## 📋 O que é testado?

1. **Helper de Roteamento e URLs (`test_url_helpers.php`)**:
   - Geração de URLs MVC (`index.php?route=...`).
   - Fusão de parâmetros GET via array.
   - Preservação de query strings existentes.
   - Geração de caminhos de assets estáticos (`asset()`).

2. **Formatação de Código Institucional (`test_instituicao_format.php`)**:
   - Validação da regra de negócio `Instituicao::formatarCodigo(int $id)`.
   - Garantia do padrão de preenchimento (`# ANC-0001`, `# ANC-0042`, `# ANC-1005`).

3. **Regras de Segurança, BCRYPT e Sanitização (`test_security_rules.php`)**:
   - Algoritmo de hashing criptográfico BCRYPT (`password_hash` e `password_verify`).
   - Rejeição de senhas incorretas.
   - Sanitização de códigos numéricos de recuperação de 6 dígitos.
   - Validação de formato de e-mails (`FILTER_VALIDATE_EMAIL`).
   - Mapeamento dos 4 perfis RBAC do sistema (Admin, Professor, Aluno, Funcionário).

---

## ✅ O que significa uma execução bem-sucedida?

- **Mensagem**: `STATUS: SUCESSO (CI PASSED)`.
- **Exit Code**: `0`.
- **Significado**: Todos os arquivos PHP do projeto são sintaticamente válidos e todas as 17 validações de regras de negócio foram aprovadas. O pipeline de CI continuará para as próximas etapas (ex: empacotamento do artefato para CD).

---

## ❌ O que acontece quando um teste falha?

- **Mensagem**: `STATUS: FALHA (CI FAILED)`.
- **Exit Code**: `1`.
- **Significado**: O interpretador PHP encerra o processo com código de saída diferente de zero. O **GitHub Actions** detecta esse código de erro e interrompe imediatamente o workflow de CI/CD, impedindo que o deploy ou empacotamento de uma versão com defeito ocorra.

---

## 🤖 Como os testes serão utilizados no GitHub Actions?

No workflow `.github/workflows/ci-cd.yml`, a esteira executará automaticamente:

```yaml
steps:
  - name: Checkout do Código
    uses: actions/checkout@v4

  - name: Configurar ambiente PHP
    uses: shivammathur/setup-php@v2
    with:
      php-version: '8.2'

  - name: Validar Sintaxe PHP
    run: php tests/validate_syntax.php

  - name: Executar Testes Automatizados
    run: php tests/run_all_tests.php
```
