<?php 
$assetUrl = defined('ASSET_URL') ? ASSET_URL : 'public/';

// Formatação dinâmica da data atual em português
$diasSemana = ['Sunday' => 'Domingo', 'Monday' => 'Segunda-Feira', 'Tuesday' => 'Terça-Feira', 'Wednesday' => 'Quarta-Feira', 'Thursday' => 'Quinta-Feira', 'Friday' => 'Sexta-Feira', 'Saturday' => 'Sábado'];
$mesesAno   = ['January' => 'Janeiro', 'February' => 'Fevereiro', 'March' => 'Março', 'April' => 'Abril', 'May' => 'Maio', 'June' => 'Junho', 'July' => 'Julho', 'August' => 'Agosto', 'September' => 'Setembro', 'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro'];

$diaHojeNome = $diasSemana[date('l')] ?? date('l');
$diaHojeNum  = date('d');
$mesHojeNome = $mesesAno[date('F')] ?? date('F');
$dataExibicao = "{$diaHojeNome}, {$diaHojeNum} De {$mesHojeNome}";
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle ?? 'Dashboard Professor — ÂNCORA'); ?></title>
  
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Stylesheet -->
  <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">
  
  <script>
    (function() {
      const savedTheme = localStorage.getItem('ancora_theme') || 'dark';
      document.documentElement.setAttribute('data-theme', savedTheme);
    })();
  </script>
</head>
<body>

  <!-- Mobile Toggle Button -->
  <button id="admin-mobile-toggle" class="admin-mobile-toggle" aria-label="Abrir Menu">☰</button>

  <div class="admin-layout-wrapper">
    
    <!-- =========================================================================
         1. SIDEBAR LATERAL DO PROFESSOR
         ========================================================================= -->
    <aside id="admin-sidebar" class="admin-sidebar">
      
      <!-- Logo Brand -->
      <div class="sidebar-brand">
        <div class="sidebar-logo-badge">
          <img src="<?php echo asset('images/logo.png'); ?>" alt="ÂNCORA Logo">
        </div>
        <div class="sidebar-brand-text">
          <span class="sidebar-brand-title">ÂNCORA</span>
          <span class="sidebar-brand-sub">Gestão Institucional</span>
        </div>
      </div>

      <!-- Badge Perfil Professor -->
      <div class="sidebar-role-badge professor">
        <span class="role-dot"></span>
        <span>Professor</span>
      </div>

      <!-- Grupo 1: PRINCIPAL -->
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">PRINCIPAL</span>
        
        <a href="<?php echo url('professor'); ?>" class="sidebar-menu-link active">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
          <span>Início</span>
        </a>
        
        <a href="<?php echo url('tarefas'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
          <span>Tarefas</span>
        </a>
        
        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <span>Reserva de Espaços</span>
        </a>
        
        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <span>Eventos</span>
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
      </div>

      <!-- Grupo 2: GESTÃO -->
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">GESTÃO</span>
        
        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          <span>Turmas</span>
        </a>
        
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
          <div class="user-avatar-circle professor"><?php echo htmlspecialchars($userInitials ?? 'CM'); ?></div>
          <div class="user-info-text">
            <span class="user-name-title"><?php echo htmlspecialchars($userName ?? 'Carlos Mendes'); ?></span>
            <span class="user-role-sub"><?php echo htmlspecialchars($userRole ?? 'Professor'); ?></span>
          </div>
        </div>
        <a href="<?php echo url('logout'); ?>" class="logout-icon-btn" title="Sair do sistema">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>

    </aside>

    <!-- =========================================================================
         2. CONTEÚDO PRINCIPAL (DASHBOARD DO PROFESSOR)
         ========================================================================= -->
    <main class="admin-main-content">
      
      <!-- Top Header Row -->
      <div class="admin-header-row">
        <div>
          <div class="header-date-text"><?php echo htmlspecialchars($dataExibicao); ?></div>
          <h1 class="header-main-title">Olá, <?php echo htmlspecialchars($userName); ?> 👋</h1>
          <div class="header-main-sub"><?php echo htmlspecialchars($userRole); ?> · <?php echo htmlspecialchars($userDepartment); ?></div>
        </div>

        <div class="status-badge-op">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          <span>Sistema operacional</span>
        </div>
      </div>

      <!-- =======================================================================
           3. CARDS SUPERIORES DE MÉTRICAS (4 CARDS)
           ======================================================================= -->
      <div class="metrics-cards-grid cols-4">
        
        <!-- Card 1: Tarefas Pendentes -->
        <div class="metric-card">
          <span class="metric-card-title">Tarefas Pendentes</span>
          <div class="metric-card-val"><?php echo $tarefasPendentes ?? 0; ?></div>
          <div class="metric-card-icon icon-gold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
          </div>
        </div>

        <!-- Card 2: Próximos Eventos -->
        <div class="metric-card">
          <span class="metric-card-title">Próximos Eventos</span>
          <div class="metric-card-val">2</div>
          <div class="metric-card-icon icon-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
        </div>

        <!-- Card 3: Reservas Ativas -->
        <div class="metric-card">
          <span class="metric-card-title">Reservas Ativas</span>
          <div class="metric-card-val">2</div>
          <div class="metric-card-icon icon-teal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </div>
        </div>

        <!-- Card 4: Notificações -->
        <div class="metric-card">
          <span class="metric-card-title">Notificações</span>
          <div class="metric-card-val">1</div>
          <div class="metric-card-icon icon-indigo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </div>
        </div>

      </div>

      <!-- =======================================================================
           4. CARD RESUMO
           ======================================================================= -->
      <div class="resumo-card">
        <h3 class="resumo-card-title">Resumo</h3>
        
        <div class="resumo-items-row">
          <div class="resumo-item">
            <span class="resumo-dot green"></span>
            <span>Tarefas entregues</span>
            <span class="resumo-val">2</span>
          </div>

          <div class="resumo-item">
            <span class="resumo-dot blue"></span>
            <span>Tarefas avaliadas</span>
            <span class="resumo-val">0</span>
          </div>

          <div class="resumo-item">
            <span class="resumo-dot purple"></span>
            <span>Eventos ativos</span>
            <span class="resumo-val">2</span>
          </div>
        </div>
      </div>

      <!-- =======================================================================
           5. SEÇÃO ACESSO RÁPIDO (4 CARDS)
           ======================================================================= -->
      <div class="section-label-ar">ACESSO RÁPIDO</div>
      
      <div class="acesso-rapido-grid">
        <!-- Card 1: Tarefas -->
        <div class="acesso-card">
          <div class="acesso-icon-wrap blue">📖</div>
          <div>
            <div class="acesso-info-title">Tarefas</div>
            <div class="acesso-info-sub">Nova tarefa para alunos</div>
          </div>
        </div>

        <!-- Card 2: Espaços -->
        <div class="acesso-card">
          <div class="acesso-icon-wrap teal">📍</div>
          <div>
            <div class="acesso-info-title">Espaços</div>
            <div class="acesso-info-sub">Sala, lab ou auditório</div>
          </div>
        </div>

        <!-- Card 3: Eventos -->
        <div class="acesso-card">
          <div class="acesso-icon-wrap amber">📅</div>
          <div>
            <div class="acesso-info-title">Eventos</div>
            <div class="acesso-info-sub">Agenda institucional</div>
          </div>
        </div>

        <!-- Card 4: Notificações -->
        <div class="acesso-card">
          <div class="acesso-icon-wrap indigo">🔔</div>
          <div>
            <div class="acesso-info-title">Notificações</div>
            <div class="acesso-info-sub">Avisos recentes</div>
          </div>
        </div>
      </div>

      <!-- =======================================================================
           6. SEÇÕES INFERIORES (2 COLUNAS)
           ======================================================================= -->
      <div class="bottom-sections-grid">
        
        <!-- Coluna Esquerda: Tarefas Recentes & Notificações Recentes -->
        <div style="display:flex; flex-direction:column; gap:20px;">
          
          <!-- Card: Tarefas Recentes -->
          <div class="dashboard-section-card">
            <div class="card-header-flex">
              <h3 class="card-header-title">Tarefas Recentes</h3>
              <a href="#" class="card-header-link">Ver todas →</a>
            </div>

            <div class="items-list-vertical">
              <!-- Item 1 -->
              <div class="task-item-row">
                <div class="task-item-info">
                  <span class="task-item-title">gsetgdrsfg</span>
                  <span class="task-item-date">🕒 10 nov</span>
                </div>
                <span class="badge-status-pill entregue">Entregue</span>
              </div>

              <!-- Item 2 -->
              <div class="task-item-row">
                <div class="task-item-info">
                  <span class="task-item-title">geologia</span>
                  <span class="task-item-date">🕒 14 jul</span>
                </div>
                <span class="badge-status-pill pendente">Pendente</span>
              </div>

              <!-- Item 3 -->
              <div class="task-item-row">
                <div class="task-item-info">
                  <span class="task-item-title">monografia</span>
                  <span class="task-item-date">🕒 01 out</span>
                </div>
                <span class="badge-status-pill entregue">Entregue</span>
              </div>

              <!-- Item 4 -->
              <div class="task-item-row">
                <div class="task-item-info">
                  <span class="task-item-title">Trabalho de tcc</span>
                  <span class="task-item-date">🕒 09 dez</span>
                </div>
                <span class="badge-status-pill pendente">Pendente</span>
              </div>
            </div>
          </div>

          <!-- Card: Notificações Recentes -->
          <div class="dashboard-section-card">
            <div class="card-header-flex">
              <h3 class="card-header-title">Notificações Recentes</h3>
              <a href="#" class="card-header-link">Ver todas →</a>
            </div>

            <div class="items-list-vertical">
              <!-- Item 1 -->
              <div class="notification-item-row">
                <div class="notification-icon-wrap">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                <div class="notification-item-content">
                  <span class="notification-item-title">farmar aura no patio</span>
                  <span class="notification-item-sub">farmar aura com 67 no patio</span>
                </div>
              </div>

              <!-- Item 2 -->
              <div class="notification-item-row">
                <div class="notification-icon-wrap">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                <div class="notification-item-content">
                  <span class="notification-item-title">campeonato de free fire</span>
                  <span class="notification-item-sub">todos comparecer ao laboratorio 19 para jogar</span>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Coluna Direita: Próximos Eventos -->
        <div style="display:flex; flex-direction:column; gap:20px;">
          
          <!-- Card: Próximos Eventos -->
          <div class="dashboard-section-card">
            <div class="card-header-flex">
              <h3 class="card-header-title">Próximos Eventos</h3>
              <a href="#" class="card-header-link">Ver todos →</a>
            </div>

            <div class="items-list-vertical">
              <!-- Event 1 -->
              <div class="event-item-row">
                <div class="event-left-wrap">
                  <div class="event-date-box blue">
                    <span class="event-date-month">OUT</span>
                    <span class="event-date-day">01</span>
                  </div>
                  <div class="event-info-text">
                    <span class="event-title">jogar free fire</span>
                    <span class="event-time">13:30</span>
                  </div>
                </div>
                <span class="badge-status-pill esportivo">Esportivo</span>
              </div>

              <!-- Event 2 -->
              <div class="event-item-row">
                <div class="event-left-wrap">
                  <div class="event-date-box purple">
                    <span class="event-date-month">JUL</span>
                    <span class="event-date-day">05</span>
                  </div>
                  <div class="event-info-text">
                    <span class="event-title">farmar aura</span>
                    <span class="event-time">06:07</span>
                  </div>
                </div>
                <span class="badge-status-pill academico">Acadêmico</span>
              </div>
            </div>
          </div>

        </div>

      </div>

    </main>

  </div>

  <!-- Script JS -->
  <script src="<?php echo asset('js/admin.js'); ?>"></script>
</body>
</html>
