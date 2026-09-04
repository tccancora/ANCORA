<?php $assetUrl = defined('ASSET_URL') ? ASSET_URL : 'public/'; ?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? $pageTitle : 'Gestão de Usuários — ÂNCORA'; ?></title>
  
  <!-- Inline Theme Restoration to prevent flicker -->
  <script>
    (function() {
      var savedTheme = localStorage.getItem('ancora_theme') || 'dark';
      document.documentElement.setAttribute('data-theme', savedTheme);
    })();
  </script>
  
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Stylesheet -->
  <link rel="stylesheet" href="<?php echo $assetUrl; ?>css/admin.css">
  
  <style>
    /* Estilos específicos para a tela Gestão de Usuários */
    .user-cards-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 28px;
    }

    .user-stat-card {
      background: var(--admin-card-bg);
      border: 1px solid var(--admin-card-border);
      border-radius: 16px;
      padding: 20px;
      box-shadow: var(--admin-card-shadow, 0 4px 16px rgba(0, 0, 0, 0.03));
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .user-stat-count {
      font-size: 1.8rem;
      font-weight: 800;
      color: var(--admin-text-heading);
    }

    .user-stat-label {
      font-size: 0.825rem;
      font-weight: 600;
      color: var(--admin-text-muted);
    }

    .users-list-card {
      background: var(--admin-card-bg);
      border: 1px solid var(--admin-card-border);
      border-radius: 16px;
      padding: 24px;
      box-shadow: var(--admin-card-shadow, 0 4px 16px rgba(0, 0, 0, 0.03));
    }

    .users-list-header {
      font-size: 1rem;
      font-weight: 700;
      color: var(--admin-text-heading);
      margin-bottom: 20px;
    }

    .users-rows-wrap {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .user-row-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px;
      border-radius: 14px;
      background: var(--admin-item-row-bg);
      border: 1px solid var(--admin-item-row-border);
      transition: all var(--transition-fast);
    }

    .user-row-item:hover {
      border-color: var(--admin-card-hover-border);
    }

    .user-row-left {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .user-avatar-badge {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.9rem;
      font-weight: 800;
      color: #FFFFFF;
      flex-shrink: 0;
    }

    .avatar-red    { background: linear-gradient(135deg, #E11D48, #BE123C); }
    .avatar-blue   { background: linear-gradient(135deg, #2563EB, #1D4ED8); }
    .avatar-amber  { background: linear-gradient(135deg, #D97706, #B45309); }
    .avatar-teal   { background: linear-gradient(135deg, #0D9488, #0F766E); }

    .user-row-info {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .user-name-line {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .user-row-name {
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--admin-text-heading);
    }

    .badge-role {
      font-size: 0.7rem;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 12px;
    }

    .badge-role.administrador { background: rgba(225, 29, 72, 0.15); color: #E11D48; border: 1px solid rgba(225, 29, 72, 0.3); }
    .badge-role.professor     { background: rgba(37, 99, 235, 0.15); color: #2563EB; border: 1px solid rgba(37, 99, 235, 0.3); }
    .badge-role.funcionario   { background: rgba(217, 119, 6, 0.15); color: #D97706; border: 1px solid rgba(217, 119, 6, 0.3); }
    .badge-role.aluno         { background: rgba(13, 148, 136, 0.15); color: #0D9488; border: 1px solid rgba(13, 148, 136, 0.3); }

    [data-theme="dark"] .badge-role.administrador { color: #F43F5E; }
    [data-theme="dark"] .badge-role.professor     { color: #60A5FA; }
    [data-theme="dark"] .badge-role.funcionario   { color: #FBBF24; }
    [data-theme="dark"] .badge-role.aluno         { color: #2DD4BF; }

    .badge-status-inativo {
      font-size: 0.68rem;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 12px;
      background: rgba(239, 68, 68, 0.15);
      color: #EF4444;
      border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .user-row-email {
      font-size: 0.8rem;
      color: var(--admin-text-muted);
    }

    .user-row-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-action-icon {
      background: transparent;
      border: 1px solid var(--admin-card-border);
      color: var(--admin-text-sub);
      width: 34px;
      height: 34px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all var(--transition-fast);
      text-decoration: none;
    }

    .btn-action-icon:hover {
      background: var(--admin-sidebar-hover);
      color: var(--admin-text-heading);
    }

    .btn-action-icon.danger:hover {
      background: rgba(239, 68, 68, 0.15);
      color: #EF4444;
      border-color: rgba(239, 68, 68, 0.3);
    }

    .btn-action-icon.success:hover {
      background: rgba(16, 185, 129, 0.15);
      color: #10B981;
      border-color: rgba(16, 185, 129, 0.3);
    }

    .btn-permissions {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--admin-text-muted);
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 6px 12px;
      border-radius: 8px;
      border: 1px solid var(--admin-card-border);
      background: transparent;
      cursor: default;
    }

    /* Modal Styling */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.65);
      backdrop-filter: blur(4px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transition: all var(--transition-normal);
    }

    .modal-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .modal-container {
      width: 100%;
      max-width: 520px;
      background: var(--admin-card-bg);
      border: 1px solid var(--admin-card-border);
      border-radius: 20px;
      padding: 28px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
      position: relative;
    }

    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .modal-title {
      font-size: 1.25rem;
      font-weight: 800;
      color: var(--admin-text-heading);
    }

    .modal-close-btn {
      background: transparent;
      border: none;
      color: var(--admin-text-muted);
      font-size: 1.25rem;
      cursor: pointer;
      padding: 4px;
      line-height: 1;
    }

    .modal-body-form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .form-group-modal {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .form-label-modal {
      font-size: 0.825rem;
      font-weight: 700;
      color: var(--admin-text-heading);
    }

    .form-input-modal, .form-select-modal {
      width: 100%;
      height: 44px;
      padding: 0 14px;
      border-radius: 10px;
      border: 1px solid var(--admin-card-border);
      background: var(--admin-item-row-bg);
      color: var(--admin-text-heading);
      font-size: 0.9rem;
      font-family: inherit;
    }

    .form-select-modal option,
    .filter-select option {
      background-color: #0F172A !important;
      color: #F8FAFC !important;
      padding: 10px;
    }

    .form-input-modal:focus, .form-select-modal:focus {
      outline: none;
      border-color: #2563EB;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .form-grid-2col {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .permissions-tags-box {
      background: var(--admin-item-row-bg);
      border: 1px solid var(--admin-item-row-border);
      border-radius: 12px;
      padding: 12px;
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 4px;
    }

    .perm-tag {
      font-size: 0.68rem;
      font-weight: 600;
      padding: 3px 8px;
      border-radius: 6px;
      background: var(--admin-card-bg);
      border: 1px solid var(--admin-card-border);
      color: var(--admin-text-sub);
    }

    .modal-footer-btns {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 24px;
    }

    .btn-modal-cancel {
      background: transparent;
      border: 1px solid var(--admin-card-border);
      color: var(--admin-text-sub);
      padding: 10px 18px;
      border-radius: 10px;
      font-size: 0.875rem;
      font-weight: 700;
      cursor: pointer;
    }

    .btn-modal-submit {
      background: #2563EB;
      border: none;
      color: #FFFFFF;
      padding: 10px 20px;
      border-radius: 10px;
      font-size: 0.875rem;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-modal-submit:hover {
      background: #1D4ED8;
    }

    .alert-banner-local {
      padding: 12px 16px;
      border-radius: 12px;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 20px;
    }
    .alert-banner-local.error { background: rgba(239, 68, 68, 0.15); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.3); }
    .alert-banner-local.success { background: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3); }
  </style>
</head>
<body>

  <!-- Mobile Toggle Button -->
  <button id="admin-mobile-toggle" class="admin-mobile-toggle" aria-label="Abrir Menu">☰</button>

  <div class="admin-layout-wrapper">
    
    <!-- =========================================================================
         1. SIDEBAR LATERAL
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

      <!-- Badge Perfil Administrador -->
      <div class="sidebar-role-badge">
        <span class="role-dot"></span>
        <span>Administrador</span>
      </div>

      <!-- Grupo 1: PRINCIPAL -->
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">PRINCIPAL</span>
        
        <a href="<?php echo url('admin'); ?>" class="sidebar-menu-link">
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
      </div>

      <!-- Grupo 2: GESTÃO -->
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">GESTÃO</span>
        
        <a href="<?php echo url('admin/turmas'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          <span>Turmas</span>
        </a>
        
        <a href="<?php echo url('usuarios'); ?>" class="sidebar-menu-link active">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>Usuários</span>
        </a>
        
        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <span>Perfis</span>
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
          <div class="user-avatar-circle"><?php echo htmlspecialchars($userInitials ?? 'AD'); ?></div>
          <div class="user-info-text">
            <span class="user-name-title"><?php echo htmlspecialchars($userName ?? 'Administrador'); ?></span>
            <span class="user-role-sub"><?php echo htmlspecialchars($userRole ?? 'Administrador'); ?></span>
          </div>
        </div>
        <a href="<?php echo url('logout'); ?>" class="logout-icon-btn" title="Sair do sistema">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>

    </aside>

    <!-- =========================================================================
         2. CONTEÚDO PRINCIPAL (GESTÃO DE USUÁRIOS)
         ========================================================================= -->
    <main class="admin-main-content">
      
      <!-- Top Header Row -->
      <div class="admin-header-row">
        <div>
          <h1 class="header-main-title">Gestão de Usuários</h1>
          <div class="header-main-sub">Cadastre e gerencie alunos, professores e funcionários</div>
        </div>

        <button id="btn-open-modal-novo" class="btn-modal-submit" style="display:inline-flex; align-items:center; gap:8px;">
          <span>+</span> Novo Usuário
        </button>
      </div>

      <!-- Alert Banners -->
      <?php if (!empty($errorMsg)): ?>
        <div class="alert-banner-local error"><?php echo htmlspecialchars($errorMsg); ?></div>
      <?php elseif (!empty($successMsg)): ?>
        <div class="alert-banner-local success"><?php echo htmlspecialchars($successMsg); ?></div>
      <?php endif; ?>

      <!-- 4 Cards de Quantidade por Perfil -->
      <div class="user-cards-grid">
        <div class="user-stat-card">
          <span class="user-stat-count"><?php echo $counts['Administrador'] ?? 0; ?></span>
          <span class="user-stat-label">Administrador</span>
        </div>

        <div class="user-stat-card">
          <span class="user-stat-count"><?php echo $counts['Professor'] ?? 0; ?></span>
          <span class="user-stat-label">Professor</span>
        </div>

        <div class="user-stat-card">
          <span class="user-stat-count"><?php echo $counts['Funcionario'] ?? 0; ?></span>
          <span class="user-stat-label">Funcionário</span>
        </div>

        <div class="user-stat-card">
          <span class="user-stat-count"><?php echo $counts['Aluno'] ?? 0; ?></span>
          <span class="user-stat-label">Aluno</span>
        </div>
      </div>

      <!-- Barra de Busca e Filtros Organizada -->
      <div class="users-filter-bar">
        <div class="search-input-group">
          <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" id="user-search-input" class="filter-input-search" placeholder="Buscar usuário..." autocomplete="off">
        </div>

        <div class="filter-controls-group">
          <div class="filter-select-wrapper">
            <select id="filter-status-select" class="filter-select">
              <option value="todos">Status: Todos</option>
              <option value="ativo">Ativos</option>
              <option value="inativo">Inativos</option>
            </select>
          </div>

          <div class="filter-select-wrapper">
            <select id="filter-perfil-select" class="filter-select">
              <option value="todos">Perfil: Todos</option>
              <option value="administrador">Administrador</option>
              <option value="professor">Professor</option>
              <option value="aluno">Aluno</option>
              <option value="funcionario">Funcionário</option>
            </select>
          </div>

          <button type="button" id="btn-clear-filters" class="btn-clear-filters">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            Limpar filtros
          </button>
        </div>
      </div>

      <!-- Lista de Usuários Reais do MySQL -->
      <div class="users-list-card">
        <div class="users-list-header" id="users-count-header">
          <?php echo count($usuarios); ?> usuário<?php echo count($usuarios) !== 1 ? 's' : ''; ?>
        </div>

        <div class="users-rows-wrap">
          <!-- Card de Estado Vazio -->
          <div id="users-empty-state" class="users-empty-state" style="display: none;">
            <div class="empty-state-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
            </div>
            <h3 class="empty-state-title">Nenhum usuário encontrado.</h3>
            <p class="empty-state-sub">Tente alterar os filtros ou o termo de busca.</p>
          </div>

          <?php if (empty($usuarios)): ?>
            <div style="padding: 24px; text-align: center; color: var(--admin-text-muted);">
              Nenhum usuário cadastrado até o momento.
            </div>
          <?php else: ?>
            <?php foreach ($usuarios as $u): ?>
              <?php
                // Iniciais do Usuário (ex: Ana Beatriz Silva -> AB)
                $partes = explode(' ', trim($u['nome']));
                $iniciais = !empty($partes[0]) ? mb_strtoupper(mb_substr($partes[0], 0, 1)) : 'U';
                if (isset($partes[1]) && !empty($partes[1])) {
                    $iniciais .= mb_strtoupper(mb_substr($partes[1], 0, 1));
                }

                // Classe de Avatar por Perfil
                $perfilClass = strtolower($u['perfil_nome']);
                $avatarClass = 'avatar-teal';
                if ($perfilClass === 'administrador') $avatarClass = 'avatar-red';
                elseif ($perfilClass === 'professor') $avatarClass = 'avatar-blue';
                elseif ($perfilClass === 'funcionario') $avatarClass = 'avatar-amber';

                $isInativo = (isset($u['status']) && $u['status'] === 'inativo');
                $userStatusVal = $isInativo ? 'inativo' : 'ativo';
              ?>
              <div 
                class="user-row-item"
                data-nome="<?php echo htmlspecialchars(mb_strtolower($u['nome'])); ?>"
                data-email="<?php echo htmlspecialchars(mb_strtolower($u['email'])); ?>"
                data-perfil="<?php echo htmlspecialchars($perfilClass); ?>"
                data-status="<?php echo htmlspecialchars($userStatusVal); ?>"
              >
                <div class="user-row-left">
                  <div class="user-avatar-badge <?php echo $avatarClass; ?>">
                    <?php echo htmlspecialchars($iniciais); ?>
                  </div>
                  <div class="user-row-info">
                    <div class="user-name-line">
                      <span class="user-row-name"><?php echo htmlspecialchars($u['nome']); ?></span>
                      <span class="badge-role <?php echo $perfilClass; ?>">
                        <?php echo htmlspecialchars($u['perfil_nome']); ?>
                      </span>
                      <?php if ($isInativo): ?>
                        <span class="badge-status-inativo">Inativo</span>
                      <?php endif; ?>
                    </div>
                    <span class="user-row-email">✉ <?php echo htmlspecialchars($u['email']); ?></span>
                  </div>
                </div>

                <div class="user-row-actions">
                  <span class="btn-permissions">🛡 Permissões</span>

                  <!-- Botão Editar -->
                  <button 
                    type="button" 
                    class="btn-action-icon btn-editar-user" 
                    title="Editar Usuário"
                    data-id="<?php echo $u['id']; ?>"
                    data-nome="<?php echo htmlspecialchars($u['nome']); ?>"
                    data-email="<?php echo htmlspecialchars($u['email']); ?>"
                    data-perfil="<?php echo htmlspecialchars($u['perfil_nome']); ?>"
                    data-status="<?php echo htmlspecialchars($u['status'] ?? 'ativo'); ?>"
                  >
                    ✏️
                  </button>

                  <!-- Botão Alternar Status (Ativar / Desativar) -->
                  <?php if ((int)$u['id'] !== (int)$_SESSION['user']['id']): ?>
                    <form action="<?php echo url('usuarios'); ?>" method="POST" style="display:inline;" onsubmit="return confirm('Deseja realmente <?php echo $isInativo ? 'reativar' : 'desativar'; ?> este usuário?');">
                      <input type="hidden" name="action" value="toggle_status">
                      <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                      <input type="hidden" name="status" value="<?php echo $isInativo ? 'ativo' : 'inativo'; ?>">
                      <button type="submit" class="btn-action-icon <?php echo $isInativo ? 'success' : 'danger'; ?>" title="<?php echo $isInativo ? 'Reativar Usuário' : 'Desativar Usuário'; ?>">
                        <?php echo $isInativo ? '✔' : '🗑'; ?>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </main>

  </div>

  <!-- =========================================================================
       MODAL 1: CADASTRAR NOVO USUÁRIO
       ========================================================================= -->
  <div id="modal-novo-usuario" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h2 class="modal-title">Cadastrar Novo Usuário</h2>
        <button id="btn-close-modal-novo" class="modal-close-btn">✕</button>
      </div>

      <form action="<?php echo url('usuarios'); ?>" method="POST" class="modal-body-form">
        <input type="hidden" name="action" value="criar">

        <div class="form-group-modal">
          <label for="nome" class="form-label-modal">Nome completo *</label>
          <input type="text" id="nome" name="nome" class="form-input-modal" placeholder="Nome completo" required>
        </div>

        <div class="form-group-modal">
          <label for="email" class="form-label-modal">Email institucional *</label>
          <input type="email" id="email" name="email" class="form-input-modal" placeholder="usuario@ancora.edu.br" required>
        </div>

        <div class="form-group-modal">
          <label for="senha" class="form-label-modal">Senha inicial *</label>
          <input type="password" id="senha" name="senha" class="form-input-modal" placeholder="Mínimo 6 caracteres" required>
        </div>

        <div class="form-grid-2col">
          <div class="form-group-modal">
            <label for="perfil_nome" class="form-label-modal">Perfil *</label>
            <select id="perfil_nome" name="perfil_nome" class="form-select-modal" required>
              <option value="Aluno">Aluno</option>
              <option value="Professor">Professor</option>
              <option value="Funcionario">Funcionário</option>
            </select>
          </div>

          <div class="form-group-modal">
            <label class="form-label-modal">Departamento</label>
            <input type="text" class="form-input-modal" placeholder="Ex: TI" disabled title="Campo ilustrativo">
          </div>
        </div>

        <div class="form-group-modal">
          <label class="form-label-modal">🛡 Permissões do perfil selecionado:</label>
          <div id="permissions-preview-box" class="permissions-tags-box">
            <span class="perm-tag">Visualizar dashboard</span>
            <span class="perm-tag">Entregar tarefas</span>
            <span class="perm-tag">Inscrever-se em eventos</span>
          </div>
        </div>

        <div class="modal-footer-btns">
          <button type="button" id="btn-cancel-modal-novo" class="btn-modal-cancel">Cancelar</button>
          <button type="submit" class="btn-modal-submit">Cadastrar Usuário</button>
        </div>
      </form>
    </div>
  </div>

  <!-- =========================================================================
       MODAL 2: EDITAR USUÁRIO
       ========================================================================= -->
  <div id="modal-editar-usuario" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h2 class="modal-title">Editar Usuário</h2>
        <button id="btn-close-modal-editar" class="modal-close-btn">✕</button>
      </div>

      <form action="<?php echo url('usuarios'); ?>" method="POST" class="modal-body-form">
        <input type="hidden" name="action" value="editar">
        <input type="hidden" id="edit-id" name="id" value="">

        <div class="form-group-modal">
          <label for="edit-nome" class="form-label-modal">Nome completo *</label>
          <input type="text" id="edit-nome" name="nome" class="form-input-modal" required>
        </div>

        <div class="form-group-modal">
          <label for="edit-email" class="form-label-modal">Email institucional *</label>
          <input type="email" id="edit-email" name="email" class="form-input-modal" required>
        </div>

        <div class="form-grid-2col">
          <div class="form-group-modal">
            <label for="edit-perfil" class="form-label-modal">Perfil *</label>
            <select id="edit-perfil" name="perfil_nome" class="form-select-modal" required>
              <option value="Aluno">Aluno</option>
              <option value="Professor">Professor</option>
              <option value="Funcionario">Funcionário</option>
              <option value="Administrador" id="option-admin-locked" style="display:none;">Administrador</option>
            </select>
          </div>

          <div class="form-group-modal">
            <label for="edit-status" class="form-label-modal">Status *</label>
            <select id="edit-status" name="status" class="form-select-modal" required>
              <option value="ativo">Ativo</option>
              <option value="inativo">Inativo</option>
            </select>
          </div>
        </div>

        <div class="modal-footer-btns">
          <button type="button" id="btn-cancel-modal-editar" class="btn-modal-cancel">Cancelar</button>
          <button type="submit" class="btn-modal-submit">Salvar Alterações</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts JS -->
  <script src="<?php echo $assetUrl; ?>js/admin.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Modais
      const modalNovo = document.getElementById('modal-novo-usuario');
      const btnOpenNovo = document.getElementById('btn-open-modal-novo');
      const btnCloseNovo = document.getElementById('btn-close-modal-novo');
      const btnCancelNovo = document.getElementById('btn-cancel-modal-novo');

      const modalEditar = document.getElementById('modal-editar-usuario');
      const btnCloseEditar = document.getElementById('btn-close-modal-editar');
      const btnCancelEditar = document.getElementById('btn-cancel-modal-editar');

      // Abrir Modal Novo
      if (btnOpenNovo && modalNovo) {
        btnOpenNovo.addEventListener('click', () => modalNovo.classList.add('active'));
      }
      if (btnCloseNovo && modalNovo) {
        btnCloseNovo.addEventListener('click', () => modalNovo.classList.remove('active'));
      }
      if (btnCancelNovo && modalNovo) {
        btnCancelNovo.addEventListener('click', () => modalNovo.classList.remove('active'));
      }

      // Fechar Modal Editar
      if (btnCloseEditar && modalEditar) {
        btnCloseEditar.addEventListener('click', () => modalEditar.classList.remove('active'));
      }
      if (btnCancelEditar && modalEditar) {
        btnCancelEditar.addEventListener('click', () => modalEditar.classList.remove('active'));
      }

      // Preencher Modal Editar no clique dos botões lápis
      const editBtns = document.querySelectorAll('.btn-editar-user');
      editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-id');
          const nome = btn.getAttribute('data-nome');
          const email = btn.getAttribute('data-email');
          const perfil = btn.getAttribute('data-perfil');
          const status = btn.getAttribute('data-status');

          document.getElementById('edit-id').value = id;
          document.getElementById('edit-nome').value = nome;
          document.getElementById('edit-email').value = email;

          const editPerfilSelect = document.getElementById('edit-perfil');
          const optionAdmin = document.getElementById('option-admin-locked');

          if (perfil.toLowerCase() === 'administrador') {
            optionAdmin.style.display = 'block';
            editPerfilSelect.value = 'Administrador';
            editPerfilSelect.disabled = true; // Impede que o Admin altere seu próprio perfil via UI
          } else {
            optionAdmin.style.display = 'none';
            editPerfilSelect.disabled = false;
            editPerfilSelect.value = perfil;
          }

          document.getElementById('edit-status').value = status;
          modalEditar.classList.add('active');
        });
      });

      // Permissões ilustrativas dinâmicas no Modal Novo
      const selectPerfilNovo = document.getElementById('perfil_nome');
      const permBox = document.getElementById('permissions-preview-box');

      const permMap = {
        'Aluno': ['Visualizar dashboard e estatísticas', 'Visualizar tarefas', 'Entregar tarefas (aluno)', 'Visualizar eventos', 'Inscrever-se em eventos', 'Visualizar itens encontrados', 'Reivindicar item perdido'],
        'Professor': ['Visualizar dashboard e estatísticas', 'Criar e gerenciar tarefas', 'Avaliar entregas', 'Criar eventos acadêmicos', 'Visualizar avisos e turmas'],
        'Funcionario': ['Visualizar dashboard', 'Gerenciar reservas de espaços', 'Gerenciar achados e perdidos', 'Emitir avisos institucionais']
      };

      function updatePermPreview() {
        const val = selectPerfilNovo.value;
        const tags = permMap[val] || permMap['Aluno'];
        permBox.innerHTML = tags.map(t => `<span class="perm-tag">${t}</span>`).join('');
      }

      if (selectPerfilNovo && permBox) {
        selectPerfilNovo.addEventListener('change', updatePermPreview);
        updatePermPreview();
      }

      // =========================================================================
      // SISTEMA DE BUSCA E FILTROS EM TEMPO REAL
      // =========================================================================
      const searchInput = document.getElementById('user-search-input');
      const filterStatus = document.getElementById('filter-status-select');
      const filterPerfil = document.getElementById('filter-perfil-select');
      const btnClearFilters = document.getElementById('btn-clear-filters');
      const countHeader = document.getElementById('users-count-header');
      const emptyState = document.getElementById('users-empty-state');
      const userRows = document.querySelectorAll('.user-row-item');

      function applyUserFilters() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const statusVal = filterStatus ? filterStatus.value.toLowerCase() : 'todos';
        const perfilVal = filterPerfil ? filterPerfil.value.toLowerCase() : 'todos';

        let visibleCount = 0;

        userRows.forEach(row => {
          const name = row.getAttribute('data-nome') || '';
          const email = row.getAttribute('data-email') || '';
          const perfil = row.getAttribute('data-perfil') || '';
          const status = row.getAttribute('data-status') || 'ativo';

          // 1. Busca Textual (Nome, E-mail ou Perfil)
          const matchesSearch = !query || name.includes(query) || email.includes(query) || perfil.includes(query);

          // 2. Filtro por Status
          const matchesStatus = (statusVal === 'todos') || (status === statusVal);

          // 3. Filtro por Perfil
          const matchesPerfil = (perfilVal === 'todos') || (perfil === perfilVal);

          if (matchesSearch && matchesStatus && matchesPerfil) {
            row.style.display = 'flex';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });

        // Atualizar Contador com singular/plural
        if (countHeader) {
          countHeader.textContent = visibleCount + (visibleCount === 1 ? ' usuário' : ' usuários');
        }

        // Exibir ou Ocultar Estado Vazio
        if (emptyState) {
          emptyState.style.display = (visibleCount === 0 && userRows.length > 0) ? 'block' : 'none';
        }
      }

      if (searchInput) searchInput.addEventListener('input', applyUserFilters);
      if (filterStatus) filterStatus.addEventListener('change', applyUserFilters);
      if (filterPerfil) filterPerfil.addEventListener('change', applyUserFilters);

      if (btnClearFilters) {
        btnClearFilters.addEventListener('click', () => {
          if (searchInput) searchInput.value = '';
          if (filterStatus) filterStatus.value = 'todos';
          if (filterPerfil) filterPerfil.value = 'todos';
          applyUserFilters();
        });
      }
    });
  </script>
</body>
</html>
