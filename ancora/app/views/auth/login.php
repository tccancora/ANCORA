<?php $assetUrl = defined('ASSET_URL') ? ASSET_URL : 'public/'; ?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? $pageTitle : 'Entrar no sistema — ÂNCORA'; ?></title>
  
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="<?php echo $assetUrl; ?>css/home.css">
  <link rel="stylesheet" href="<?php echo $assetUrl; ?>css/auth.css">
</head>
<body>

  <div class="auth-page-wrapper">
    
    <!-- Top Action Bar (Theme Toggle) -->
    <div class="auth-top-bar">
      <button id="theme-toggle-btn" class="theme-toggle-btn" aria-label="Alternar Tema" title="Alternar Tema Claro/Escuro">
        <span id="theme-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
        </span>
      </button>
    </div>

    <!-- Brand Header -->
    <div class="auth-brand-header">
      <div class="auth-logo-badge">
        <img src="<?php echo $assetUrl; ?>images/logo.png" alt="ÂNCORA Logo">
      </div>
      <h1 class="auth-brand-title">ÂNCORA</h1>
      <p class="auth-brand-subtitle">Sistema de Gestão Institucional</p>
    </div>

    <!-- Auth Card -->
    <div class="auth-card">
      <h2 class="auth-card-title">Entrar no sistema</h2>

      <!-- Validation Alert Box -->
      <?php if (!empty($errorMsg)): ?>
        <div id="auth-alert" class="auth-alert error" style="display: block;">
          <?php echo htmlspecialchars($errorMsg); ?>
        </div>
      <?php else: ?>
        <div id="auth-alert" class="auth-alert"></div>
      <?php endif; ?>

      <!-- Real HTML Form -->
      <form id="form-login" class="auth-form" action="<?php echo url('login'); ?>" method="POST" novalidate>
        <!-- Email Field -->
        <div class="form-group">
          <label for="email" class="form-label">Email institucional</label>
          <input 
            type="email" 
            id="email" 
            name="email" 
            class="form-control" 
            placeholder="usuario@ancora.edu.br" 
            value="<?php echo htmlspecialchars($email ?? ''); ?>"
            required
            autocomplete="email"
          >
        </div>

        <!-- Password Field with Toggle Eye Icon -->
        <div class="form-group">
          <label for="senha" class="form-label">Senha</label>
          <div class="input-password-wrap">
            <input 
              type="password" 
              id="senha" 
              name="senha" 
              class="form-control" 
              placeholder="••••••••" 
              required
              autocomplete="current-password"
            >
            <button 
              type="button" 
              class="toggle-password-btn" 
              data-target="senha" 
              aria-label="Mostrar ou Ocultar Senha"
              title="Mostrar/Ocultar Senha"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
        </div>

        <!-- Links Row -->
        <div class="form-links-row">
          <a href="<?php echo url('recuperar-senha'); ?>" class="link-forgot">🔑 Esqueci minha senha</a>
          <a href="<?php echo url('primeiro-acesso'); ?>" class="link-register">Primeiro acesso</a>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-auth-submit">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
          Entrar
        </button>
      </form>

      <!-- Divider -->
      <div class="auth-divider"></div>

      <!-- Footer Links -->
      <div class="auth-footer-links">
        <a href="<?php echo url(); ?>" class="link-back-home">← Voltar ao início</a>
      </div>
    </div>

  </div>

  <!-- Script JS -->
  <script src="<?php echo $assetUrl; ?>js/auth.js"></script>
</body>
</html>
