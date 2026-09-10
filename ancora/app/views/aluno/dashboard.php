<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle ?? 'Dashboard Aluno — ÂNCORA'); ?></title>
  <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">
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
<body class="admin-body">

  <div class="admin-layout">
    
    <!-- Mobile Toggle Header -->
    <div class="admin-mobile-header">
      <div class="brand-logo-mobile">
        <div class="logo-symbol-sm">⚓</div>
        <span class="brand-title-sm">ÂNCORA</span>
      </div>
      <button id="admin-mobile-toggle" class="btn-mobile-toggle" aria-label="Abrir Menu">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>

    <!-- =========================================================================
         1. SIDEBAR NAVEGAÇÃO DO ALUNO
         ========================================================================= -->
    <aside id="admin-sidebar" class="admin-sidebar">
      
      <!-- Brand Logo Header -->
      <div class="sidebar-brand">
        <div class="sidebar-logo-badge">
          <img src="<?php echo asset('images/logo.png'); ?>" alt="ÂNCORA Logo" class="brand-logo-img">
        </div>
        <div class="sidebar-brand-text">
          <span class="sidebar-brand-title">ÂNCORA</span>
          <span class="sidebar-brand-sub">Gestão Institucional</span>
        </div>
      </div>

      <!-- Tag de Perfil Aluno -->
      <div style="padding: 0 20px 12px 20px;">
        <span class="badge-role aluno" style="padding: 5px 12px; font-size: 0.75rem; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px;">
          <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span> Aluno
        </span>
      </div>

      <!-- Menus da Sidebar -->
      <div class="sidebar-menu-wrapper">
        <div class="menu-section-label">PRINCIPAL</div>

        <a href="<?php echo url('aluno'); ?>" class="sidebar-menu-link active">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
          <span>Início</span>
        </a>

        <a href="<?php echo url('tarefas'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
          <span>Tarefas</span>
        </a>

        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <span>Eventos</span>
        </a>

        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <span>Achados e Perdidos</span>
        </a>

        <a href="<?php echo url('notificacoes'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span>Notificações</span>
          <span class="sidebar-unread-badge" id="sidebar-unread-count" style="display:none;"></span>
        </a>

        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>Mensagens</span>
        </a>

        <div class="menu-section-label" style="margin-top: 20px;">GESTÃO</div>

        <a href="<?php echo url('configuracoes'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          <span>Configurações</span>
        </a>
      </div>

      <!-- Bottom Actions -->
      <div class="sidebar-bottom-actions">
        <button id="admin-theme-toggle" class="sidebar-action-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <span id="admin-theme-text">Tema Claro</span>
        </button>
        
        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <span>Central de Ajuda</span>
        </a>
      </div>

      <!-- Rodapé Usuário Logado + Logout -->
      <div class="sidebar-user-footer">
        <div class="user-footer-profile">
          <div class="user-avatar-circle avatar-teal"><?php echo htmlspecialchars($userInitials ?? 'AB'); ?></div>
          <div class="user-info-text">
            <span class="user-name-title"><?php echo htmlspecialchars($userName ?? 'Ana Beatriz Silva'); ?></span>
            <span class="user-role-sub"><?php echo htmlspecialchars($userRole ?? 'Aluno'); ?></span>
          </div>
        </div>
        <a href="<?php echo url('logout'); ?>" class="logout-icon-btn" title="Sair do sistema">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>

    </aside>

    <!-- =========================================================================
         2. CONTEÚDO PRINCIPAL (DASHBOARD DO ALUNO)
         ========================================================================= -->
    <main class="admin-main-content">
      
      <!-- Top Header Row -->
      <div class="admin-header-row">
        <div>
          <?php
            setlocale(LC_TIME, 'pt_BR.utf-8', 'pt_BR', 'portuguese');
            $dataAtualFormatada = ucfirst(mb_strtolower(date('l, d \d\e F', time())));
            $dataAtualFormatada = str_replace(
              ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
              ['Segunda-Feira', 'Terça-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'Sábado', 'Domingo', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
              $dataAtualFormatada
            );
          ?>
          <div class="header-date-text"><?php echo htmlspecialchars($dataAtualFormatada); ?></div>
          <h1 class="header-welcome-title">Olá, <?php echo htmlspecialchars($userName ?? 'Ana Beatriz Silva'); ?> 👋</h1>
          <div class="header-subtitle">Aluno · <?php echo htmlspecialchars($userCourse ?? 'Ciência da Computação'); ?></div>
        </div>

        <div class="system-status-badge">
          <span class="status-dot-pulse"></span>
          <span>Sistema operacional</span>
        </div>
      </div>

      <!-- 4 Cards de Métricas do Aluno -->
      <div class="metrics-cards-grid">
        <div class="metric-card">
          <div class="metric-info">
            <span class="metric-label">Tarefas Pendentes</span>
            <span class="metric-value"><?php echo $tarefasPendentes ?? 0; ?></span>
          </div>
          <div class="metric-icon-box amber">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
          </div>
        </div>

        <div class="metric-card">
          <div class="metric-info">
            <span class="metric-label">Próximos Eventos</span>
            <span class="metric-value">2</span>
          </div>
          <div class="metric-icon-box blue">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
        </div>

        <div class="metric-card">
          <div class="metric-info">
            <span class="metric-label">Itens Encontrados</span>
            <span class="metric-value">0</span>
          </div>
          <div class="metric-icon-box pink">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>
        </div>

        <div class="metric-card">
          <div class="metric-info">
            <span class="metric-label">Notificações</span>
            <span class="metric-value">0</span>
          </div>
          <div class="metric-icon-box teal">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </div>
        </div>
      </div>

      <!-- Card Resumo -->
      <div class="resumo-card" style="background: var(--admin-card-bg); border: 1px solid var(--admin-card-border); border-radius: 16px; padding: 20px 24px; margin-bottom: 32px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
        <div style="font-size: 1rem; font-weight: 700; color: var(--admin-text-heading); margin-bottom: 12px;">Resumo</div>
        <div style="display: flex; align-items: center; gap: 24px; font-size: 0.875rem; color: var(--admin-text-sub); flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981;"></span>
            <span>Tarefas entregues <strong style="color: var(--admin-text-heading);">2</strong></span>
          </div>
          <span style="color: var(--admin-card-border);">•</span>
          <div>Tarefas avaliadas <strong style="color: var(--admin-text-heading);">0</strong></div>
          <span style="color: var(--admin-card-border);">•</span>
          <div>Eventos ativos <strong style="color: var(--admin-text-heading);">2</strong></div>
        </div>
      </div>

      <!-- Seção Acesso Rápido -->
      <div class="section-label-ar">ACESSO RÁPIDO</div>

      <div class="acesso-rapido-grid">
        <div class="acesso-card">
          <div class="acesso-icon-wrap blue">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
          </div>
          <div>
            <div style="font-size: 0.95rem; font-weight: 700; color: var(--admin-text-heading);">Tarefas</div>
            <div style="font-size: 0.775rem; color: var(--admin-text-muted);">Minhas atividades</div>
          </div>
        </div>

        <div class="acesso-card">
          <div class="acesso-icon-wrap amber">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div>
            <div style="font-size: 0.95rem; font-weight: 700; color: var(--admin-text-heading);">Eventos</div>
            <div style="font-size: 0.775rem; color: var(--admin-text-muted);">Agenda institucional</div>
          </div>
        </div>

        <div class="acesso-card">
          <div class="acesso-icon-wrap teal">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>
          <div>
            <div style="font-size: 0.95rem; font-weight: 700; color: var(--admin-text-heading);">Achados</div>
            <div style="font-size: 0.775rem; color: var(--admin-text-muted);">Itens perdidos</div>
          </div>
        </div>

        <div class="acesso-card">
          <div class="acesso-icon-wrap pink">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </div>
          <div>
            <div style="font-size: 0.95rem; font-weight: 700; color: var(--admin-text-heading);">Notificações</div>
            <div style="font-size: 0.775rem; color: var(--admin-text-muted);">Avisos recentes</div>
          </div>
        </div>
      </div>

      <!-- Seção Inferior: Próximos Eventos & Notificações Recentes -->
      <div class="bottom-sections-grid">
        
        <!-- Box Próximos Eventos -->
        <div class="dashboard-section-card">
          <div class="section-card-header">
            <h2 class="section-card-title">Próximos Eventos</h2>
            <a href="#" class="section-card-link">Ver todos &rarr;</a>
          </div>

          <div style="display: flex; flex-direction: column; gap: 14px;">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: var(--admin-bg-main); border-radius: 12px; border: 1px solid var(--admin-card-border);">
              <div style="display: flex; align-items: center; gap: 14px;">
                <div style="text-align: center; line-height: 1.1;">
                  <span style="font-size: 0.7rem; font-weight: 800; color: #2563EB; display: block;">OUT</span>
                  <span style="font-size: 1.1rem; font-weight: 800; color: var(--admin-text-heading);">01</span>
                </div>
                <div>
                  <div style="font-size: 0.9rem; font-weight: 700; color: var(--admin-text-heading);">jogar free fire</div>
                  <div style="font-size: 0.75rem; color: var(--admin-text-muted);">13:30</div>
                </div>
              </div>
              <span class="badge-role funcionario" style="font-size: 0.7rem; padding: 3px 8px; border-radius: 6px;">Esportivo</span>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: var(--admin-bg-main); border-radius: 12px; border: 1px solid var(--admin-card-border);">
              <div style="display: flex; align-items: center; gap: 14px;">
                <div style="text-align: center; line-height: 1.1;">
                  <span style="font-size: 0.7rem; font-weight: 800; color: #2563EB; display: block;">JUL</span>
                  <span style="font-size: 1.1rem; font-weight: 800; color: var(--admin-text-heading);">05</span>
                </div>
                <div>
                  <div style="font-size: 0.9rem; font-weight: 700; color: var(--admin-text-heading);">farmar aura</div>
                  <div style="font-size: 0.75rem; color: var(--admin-text-muted);">06:07</div>
                </div>
              </div>
              <span class="badge-role professor" style="font-size: 0.7rem; padding: 3px 8px; border-radius: 6px;">Acadêmico</span>
            </div>
          </div>
        </div>

        <!-- Box Notificações Recentes -->
        <div class="dashboard-section-card">
          <div class="section-card-header">
            <h2 class="section-card-title">Notificações Recentes</h2>
            <a href="#" class="section-card-link">Ver todas &rarr;</a>
          </div>

          <div style="display: flex; flex-direction: column; gap: 14px;">
            <div style="display: flex; align-items: flex-start; gap: 12px; padding: 12px 14px; background: var(--admin-bg-main); border-radius: 12px; border: 1px solid var(--admin-card-border);">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #2563EB; margin-top: 2px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              <div>
                <div style="font-size: 0.875rem; font-weight: 700; color: var(--admin-text-heading);">farmar aura no patio</div>
                <div style="font-size: 0.775rem; color: var(--admin-text-muted);">farmar aura com 67 no patio</div>
              </div>
            </div>

            <div style="display: flex; align-items: flex-start; gap: 12px; padding: 12px 14px; background: var(--admin-bg-main); border-radius: 12px; border: 1px solid var(--admin-card-border);">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #2563EB; margin-top: 2px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              <div>
                <div style="font-size: 0.875rem; font-weight: 700; color: var(--admin-text-heading);">campeonato de free fire</div>
                <div style="font-size: 0.775rem; color: var(--admin-text-muted);">todos comparecer ao laboratorio 19 para jogar</div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </main>

  </div>

  <script src="<?php echo asset('js/admin.js'); ?>"></script>
</body>
</html>
