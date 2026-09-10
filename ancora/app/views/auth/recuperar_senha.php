<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle ?? 'Recuperar senha — ÂNCORA'); ?></title>
  <link rel="stylesheet" href="<?php echo asset('css/auth.css'); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!--
    OBJETIVO DIDÁTICO (TCC):
    Preservar o tema do usuário armazenado no localStorage antes da renderização do DOM
    para evitar piscada de tela (FOUC).
  -->
  <script>
    (function() {
      const savedTheme = localStorage.getItem('ancora_theme') || 'dark';
      document.documentElement.setAttribute('data-theme', savedTheme);
    })();
  </script>
</head>
<body>

  <div class="auth-page-wrapper">
    
    <!-- Barra Superior de Alternância de Tema -->
    <div class="auth-top-bar">
      <button id="theme-toggle-btn" class="theme-toggle-btn" aria-label="Alternar Tema" title="Alternar entre tema claro e escuro">
        <svg id="theme-icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="5"></circle>
          <line x1="12" y1="1" x2="12" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="23"></line>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
          <line x1="1" y1="12" x2="3" y2="12"></line>
          <line x1="21" y1="12" x2="23" y2="12"></line>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>
        <span id="theme-toggle-text">Tema Claro</span>
      </button>
    </div>

    <!-- Branding e Logo Oficial ÂNCORA -->
    <div class="auth-brand-header">
      <div class="auth-logo-badge">
        <img src="<?php echo asset('images/logo.png'); ?>" alt="ÂNCORA Logo">
      </div>
      <h1 class="auth-brand-title">ÂNCORA</h1>
      <p class="auth-brand-subtitle">Sistema de Gestão Acadêmica</p>
    </div>

    <!-- Card Principal da Autenticação -->
    <div class="auth-card">
      <h2 class="auth-card-title">Recuperar senha</h2>
      <p style="font-size: 0.875rem; color: var(--text-hero-sub); margin-top: -16px; margin-bottom: 24px; line-height: 1.4;">
        Informe seu e-mail institucional para receber um código de recuperação.
      </p>

      <!-- Caixas de Alerta (Erro ou Informação) -->
      <?php if (!empty($errorMsg)): ?>
        <div class="auth-alert error" style="display: block; margin-bottom: 20px;">
          <?php echo htmlspecialchars($errorMsg); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($infoMsg)): ?>
        <div class="auth-alert success" style="display: block; margin-bottom: 20px;">
          <?php echo htmlspecialchars($infoMsg); ?>
        </div>
      <?php endif; ?>

      <!-- Formulário de Solicitação de Código -->
      <form action="<?php echo url('recuperar-senha'); ?>" method="POST" class="auth-form" autocomplete="off">
        
        <!-- Campo E-mail Institutional -->
        <div class="form-group">
          <label for="email" class="form-label">E-mail institucional</label>
          <input 
            type="email" 
            id="email" 
            name="email" 
            class="form-control" 
            placeholder="seu.email@ancora.edu.br" 
            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
            required
            autofocus
          >
        </div>

        <!-- Botão de Envio -->
        <button type="submit" class="btn-auth-submit" style="margin-top: 8px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
          Enviar código
        </button>
      </form>

      <!-- Divisor e Link de Retorno -->
      <div class="auth-divider"></div>

      <div class="auth-footer-links">
        <a href="<?php echo url('login'); ?>" class="link-back-home">← Voltar para o login</a>
      </div>

    </div>

  </div>

  <!-- Scripts Globais da Aplicação Interna -->
  <script src="<?php echo asset('js/auth.js'); ?>"></script>
</body>
</html>
