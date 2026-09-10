<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle ?? 'Primeiro Acesso — ÂNCORA'); ?></title>
  <link rel="stylesheet" href="<?php echo asset('css/auth.css'); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script>
    (function() {
      const savedTheme = localStorage.getItem('ancora_theme') || 'dark';
      document.documentElement.setAttribute('data-theme', savedTheme);
    })();
  </script>
</head>
<body>

  <div class="auth-page-wrapper">
    
    <!-- Top Action Bar (Theme Toggle) -->
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

    <!-- Header / Branding Above Card -->
    <div class="auth-brand-header">
      <div class="auth-logo-badge">
        <img src="<?php echo asset('images/logo.png'); ?>" alt="ÂNCORA Logo">
      </div>
      <h1 class="auth-brand-title">ÂNCORA</h1>
      <p class="auth-brand-subtitle">Sistema de Gestão Acadêmica</p>
    </div>

    <!-- Auth Card Container -->
    <div class="auth-card wide">
      
      <?php if (!empty($sucesso) && $sucesso === true): ?>
        
        <!-- Tela de Sucesso -->
        <div style="text-align: center; padding: 12px 0;">
          <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: #10B981; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <h2 class="auth-card-title" style="margin-bottom: 8px; color: #10B981;">Senha cadastrada com sucesso!</h2>
          <p style="font-size: 0.95rem; color: var(--text-hero-sub); margin-bottom: 28px; line-height: 1.5;">
            Seu primeiro acesso foi concluído. Agora você pode entrar no ÂNCORA utilizando sua nova senha.
          </p>

          <a href="<?php echo url('login'); ?>" class="btn-auth-submit" style="text-decoration: none;">
            Voltar para Login
          </a>
        </div>

      <?php else: ?>

        <h2 class="auth-card-title">Primeiro Acesso</h2>
        <p style="font-size: 0.875rem; color: var(--text-hero-sub); margin-top: -16px; margin-bottom: 24px;">
          Confirme seus dados e cadastre sua nova senha pessoal.
        </p>

        <!-- Alert Error Box -->
        <?php if (!empty($errorMsg)): ?>
          <div class="auth-alert error" style="display: block; margin-bottom: 20px;">
            <?php echo htmlspecialchars($errorMsg); ?>
            <?php if (strpos($errorMsg, 'já realizou o primeiro acesso') !== false): ?>
              <div style="margin-top: 14px;">
                <a href="<?php echo url('login'); ?>" class="btn-auth-submit" style="text-decoration: none; display: inline-flex;">Voltar para Login</a>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- Formulario Primeiro Acesso -->
        <form action="<?php echo url('primeiro-acesso'); ?>" method="POST" class="auth-form" autocomplete="off">
          
          <!-- Field E-mail -->
          <div class="form-group">
            <label for="email" class="form-label">E-mail cadastrado</label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              class="form-control" 
              placeholder="seu.email@exemplo.com" 
              value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
              required
            >
          </div>

          <!-- Field Senha Inicial -->
          <div class="form-group">
            <label for="senha_inicial" class="form-label">Senha inicial fornecida</label>
            <div class="input-password-wrap">
              <input 
                type="password" 
                id="senha_inicial" 
                name="senha_inicial" 
                class="form-control" 
                placeholder="••••••••" 
                required
              >
              <button 
                type="button" 
                class="toggle-password-btn" 
                data-target="senha_inicial" 
                title="Mostrar/Ocultar Senha"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              </button>
            </div>
          </div>

          <!-- Field Nova Senha -->
          <div class="form-group">
            <label for="nova_senha" class="form-label">Nova senha pessoal (mínimo 6 caracteres)</label>
            <div class="input-password-wrap">
              <input 
                type="password" 
                id="nova_senha" 
                name="nova_senha" 
                class="form-control" 
                placeholder="••••••••" 
                required
              >
              <button 
                type="button" 
                class="toggle-password-btn" 
                data-target="nova_senha" 
                title="Mostrar/Ocultar Senha"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              </button>
            </div>
          </div>

          <!-- Field Confirmar Nova Senha -->
          <div class="form-group">
            <label for="confirmar_senha" class="form-label">Confirmar nova senha</label>
            <div class="input-password-wrap">
              <input 
                type="password" 
                id="confirmar_senha" 
                name="confirmar_senha" 
                class="form-control" 
                placeholder="••••••••" 
                required
              >
              <button 
                type="button" 
                class="toggle-password-btn" 
                data-target="confirmar_senha" 
                title="Mostrar/Ocultar Senha"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn-auth-submit" style="margin-top: 12px;">
            Confirmar Primeiro Acesso
          </button>
        </form>

        <!-- Divider -->
        <div class="auth-divider"></div>

        <!-- Footer Links -->
        <div class="auth-footer-links">
          <a href="<?php echo url('login'); ?>" class="link-back-home">← Voltar ao Login</a>
        </div>

      <?php endif; ?>

    </div>

  </div>

  <!-- Script JS -->
  <script src="<?php echo asset('js/auth.js'); ?>"></script>
</body>
</html>
