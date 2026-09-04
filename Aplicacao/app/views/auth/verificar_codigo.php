<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle ?? 'Verificar código — ÂNCORA'); ?></title>
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

    <!-- Card Principal -->
    <div class="auth-card">
      <h2 class="auth-card-title">Verificar código</h2>
      <p style="font-size: 0.875rem; color: var(--text-hero-sub); margin-top: -16px; margin-bottom: 20px; line-height: 1.4;">
        Digite o código de 6 dígitos enviado para o e-mail: <strong style="color: var(--text-hero);"><?php echo htmlspecialchars($emailSession ?? ''); ?></strong>
      </p>

      <div style="background: rgba(37, 99, 235, 0.1); border: 1px solid rgba(37, 99, 235, 0.25); border-radius: 10px; padding: 10px 14px; font-size: 0.8rem; color: #38BDF8; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span>O código é válido por 10 minutos.</span>
      </div>

      <!-- Caixas de Alerta -->
      <?php if (!empty($errorMsg)): ?>
        <div class="auth-alert error" style="display: block; margin-bottom: 20px;">
          <?php echo htmlspecialchars($errorMsg); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($successMsg)): ?>
        <div class="auth-alert success" style="display: block; margin-bottom: 20px;">
          <?php echo htmlspecialchars($successMsg); ?>
        </div>
      <?php endif; ?>

      <!-- Formulário de Validação do Código -->
      <form action="<?php echo url('verificar-codigo'); ?>" method="POST" class="auth-form" autocomplete="off">
        <input type="hidden" name="action" value="verificar">

        <div class="form-group">
          <label for="codigo" class="form-label">Código de recuperação (6 dígitos)</label>
          <input 
            type="text" 
            id="codigo" 
            name="codigo" 
            class="form-control" 
            placeholder="123456" 
            maxlength="6" 
            pattern="[0-9]*" 
            inputmode="numeric"
            style="text-align: center; font-size: 1.5rem; letter-spacing: 6px; font-weight: 800; font-family: monospace;"
            required
            autofocus
          >
        </div>

        <button type="submit" class="btn-auth-submit" style="margin-top: 8px;">
          Verificar código
        </button>
      </form>

      <!-- Formulário e Contagem Regressiva de Reenvio do Código -->
      <div style="margin-top: 24px; text-align: center; border-top: 1px solid var(--border-color); padding-top: 18px;">
        <form action="<?php echo url('verificar-codigo'); ?>" method="POST" id="resend-form" style="display: inline;">
          <input type="hidden" name="action" value="reenviar">
          <button 
            type="submit" 
            id="btn-resend-code" 
            class="link-register" 
            style="background: none; border: none; font-size: 0.875rem; cursor: pointer; text-decoration: underline;"
            <?php echo ($cooldownRestante > 0) ? 'disabled' : ''; ?>
          >
            Enviar código novamente
          </button>
        </form>

        <div id="resend-timer-box" style="font-size: 0.8rem; color: var(--text-hero-sub); margin-top: 8px; <?php echo ($cooldownRestante <= 0) ? 'display: none;' : ''; ?>">
          Você poderá solicitar um novo código em <strong id="resend-timer-seconds"><?php echo (int)$cooldownRestante; ?></strong> segundos.
        </div>
      </div>

      <!-- Divisor e Link de Retorno -->
      <div class="auth-divider"></div>

      <div class="auth-footer-links">
        <a href="<?php echo url('recuperar-senha'); ?>" class="link-back-home">← Alterar e-mail</a>
      </div>

    </div>

  </div>

  <script src="<?php echo asset('js/auth.js'); ?>"></script>
  
  <!-- Script de Contagem Regressiva de Reenvio (60s) -->
  <script>
    (function() {
      let seconds = <?php echo (int)$cooldownRestante; ?>;
      const resendBtn = document.getElementById('btn-resend-code');
      const timerBox = document.getElementById('resend-timer-box');
      const timerSecs = document.getElementById('resend-timer-seconds');
      const codigoInput = document.getElementById('codigo');

      // Garante digitação estritamente numérica no campo do código
      if (codigoInput) {
        codigoInput.addEventListener('input', function(e) {
          this.value = this.value.replace(/[^0-9]/g, '');
        });
      }

      if (seconds > 0 && resendBtn && timerBox && timerSecs) {
        resendBtn.disabled = true;
        resendBtn.style.opacity = '0.5';
        resendBtn.style.cursor = 'not-allowed';

        const interval = setInterval(function() {
          seconds--;
          timerSecs.textContent = seconds;

          if (seconds <= 0) {
            clearInterval(interval);
            timerBox.style.display = 'none';
            resendBtn.disabled = false;
            resendBtn.style.opacity = '1';
            resendBtn.style.cursor = 'pointer';
          }
        }, 1000);
      }
    })();
  </script>
</body>
</html>
