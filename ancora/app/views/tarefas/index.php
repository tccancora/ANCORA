<?php 
$assetUrl = defined('ASSET_URL') ? ASSET_URL : 'public/'; 
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? $pageTitle : 'Tarefas — ÂNCORA'; ?></title>
  
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Stylesheet do Painel Administrativo -->
  <link rel="stylesheet" href="<?php echo $assetUrl; ?>css/admin.css">
  
  <script>
    (function() {
      const savedTheme = localStorage.getItem('ancora_theme') || 'dark';
      document.documentElement.setAttribute('data-theme', savedTheme);
    })();
  </script>

  <style>
    /* Estilos Customizados e Refinados do Módulo de Tarefas ÂNCORA */
    .tarefas-container {
      display: flex;
      flex-direction: column;
      gap: 16px;
      margin-top: 20px;
    }

    .filters-bar-card {
      background: var(--admin-card-bg);
      border: 1px solid var(--admin-card-border);
      border-radius: 16px;
      padding: 18px 22px;
      display: flex;
      flex-direction: column;
      gap: 14px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .filters-title-row {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--admin-text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .filters-inputs-grid {
      display: grid;
      grid-template-columns: 2fr 1.2fr 1.2fr 1fr;
      gap: 12px;
      align-items: center;
    }

    @media (max-width: 900px) {
      .filters-inputs-grid {
        grid-template-columns: 1fr 1fr;
      }
    }

    @media (max-width: 600px) {
      .filters-inputs-grid {
        grid-template-columns: 1fr;
      }
    }

    .filter-select, .filter-search-input {
      width: 100%;
      height: 44px;
      padding: 0 16px;
      border-radius: 12px;
      border: 1.5px solid var(--admin-card-border);
      background: var(--admin-item-row-bg, #0B1120);
      color: var(--admin-text-heading);
      font-family: inherit;
      font-size: 0.875rem;
      outline: none;
      transition: all var(--transition-fast);
      box-sizing: border-box;
    }

    .filter-select:focus, .filter-search-input:focus {
      border-color: #38BDF8;
      box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18);
      background: rgba(15, 23, 42, 0.6);
    }

    .tarefa-card-item {
      background: var(--admin-card-bg);
      border: 1px solid var(--admin-card-border);
      border-radius: 16px;
      padding: 22px 26px;
      display: flex;
      flex-direction: column;
      gap: 14px;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
    }

    .tarefa-card-item:hover {
      border-color: rgba(56, 189, 248, 0.35);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.14);
      transform: translateY(-2px);
    }

    /* Borda de atrasada conforme protótipo */
    .tarefa-card-item.is-atrasada {
      border-color: rgba(239, 68, 68, 0.35);
      background: linear-gradient(180deg, rgba(239, 68, 68, 0.05) 0%, var(--admin-card-bg) 100%);
    }

    .tarefa-header-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 16px;
    }

    .tarefa-title-group {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .tarefa-card-title {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--admin-text-heading);
      margin: 0;
      letter-spacing: -0.2px;
    }

    .tarefa-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 12px;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.3px;
    }

    .tarefa-badge.entregue {
      background: rgba(16, 185, 129, 0.15);
      color: #10B981;
      border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .tarefa-badge.pendente {
      background: rgba(245, 158, 11, 0.15);
      color: #F59E0B;
      border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .tarefa-badge.atrasada {
      background: rgba(239, 68, 68, 0.15);
      color: #EF4444;
      border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .tarefa-badge.devolvida, .tarefa-badge.avaliada, .tarefa-badge.corrigida {
      background: rgba(56, 189, 248, 0.15);
      color: #38BDF8;
      border: 1px solid rgba(56, 189, 248, 0.3);
    }

    .tarefa-badge.disciplina {
      background: rgba(148, 163, 184, 0.12);
      color: #94A3B8;
      border: 1px solid rgba(148, 163, 184, 0.2);
    }

    .tarefa-description-text {
      color: var(--admin-text-muted);
      font-size: 0.925rem;
      line-height: 1.6;
      margin: 0;
    }

    .tarefa-footer-info {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
      padding-top: 12px;
      border-top: 1px solid var(--admin-item-row-border);
    }

    .tarefa-meta-items {
      display: flex;
      align-items: center;
      gap: 20px;
      font-size: 0.85rem;
      color: var(--admin-text-muted);
      flex-wrap: wrap;
    }

    .tarefa-meta-item {
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .tarefa-card-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-card-action {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 9px 18px;
      border-radius: 10px;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s ease;
      box-sizing: border-box;
    }

    .btn-card-action.primary {
      background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
      color: #FFFFFF;
      border: none;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-card-action.primary:hover {
      background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%);
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.45);
      transform: translateY(-1px);
    }

    .btn-card-action.secondary {
      background: rgba(148, 163, 184, 0.08);
      border: 1px solid rgba(148, 163, 184, 0.2);
      color: var(--admin-text-heading);
    }

    .btn-card-action.secondary:hover {
      border-color: #38BDF8;
      color: #38BDF8;
      background: rgba(56, 189, 248, 0.08);
      transform: translateY(-1px);
    }

    .btn-card-action.danger {
      background: rgba(239, 68, 68, 0.08);
      border: 1px solid rgba(239, 68, 68, 0.2);
      color: #EF4444;
      padding: 9px 12px;
    }

    .btn-card-action.danger:hover {
      background: rgba(239, 68, 68, 0.2);
      border-color: #EF4444;
      color: #EF4444;
      transform: translateY(-1px);
    }

    /* Estilização Refinada de Caixas de Texto e Formulários */
    .form-group-row {
      margin-bottom: 18px;
    }

    .form-label-title {
      display: block;
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--admin-text-heading);
      margin-bottom: 8px;
      letter-spacing: -0.1px;
    }

    .form-input-control {
      width: 100%;
      padding: 12px 16px;
      border-radius: 12px;
      border: 1.5px solid var(--admin-card-border);
      background: var(--admin-item-row-bg, #0B1120);
      color: var(--admin-text-heading);
      font-family: inherit;
      font-size: 0.9rem;
      outline: none;
      transition: all 0.2s ease;
      box-sizing: border-box;
    }

    .form-input-control:focus {
      border-color: #38BDF8;
      box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18);
      background: rgba(15, 23, 42, 0.6);
    }

    .form-input-control::placeholder {
      color: var(--admin-text-muted);
      opacity: 0.7;
    }

    textarea.form-input-control {
      resize: vertical;
      line-height: 1.6;
      min-height: 100px;
    }

    .checkboxes-pill-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 10px;
      max-height: 170px;
      overflow-y: auto;
      padding: 10px;
      background: rgba(15, 23, 42, 0.45);
      border: 1.5px solid var(--admin-card-border);
      border-radius: 12px;
    }

    .checkbox-pill-label {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.85rem;
      font-weight: 500;
      color: var(--admin-text-heading);
      cursor: pointer;
      padding: 8px 12px;
      border-radius: 8px;
      user-select: none;
      background: rgba(15, 23, 42, 0.3);
      border: 1px solid rgba(148, 163, 184, 0.15);
      transition: all 0.15s ease;
    }

    .checkbox-pill-label:hover {
      background: rgba(37, 99, 235, 0.12);
      border-color: rgba(56, 189, 248, 0.4);
    }

    .checkbox-pill-label input[type="checkbox"]:checked + span {
      color: #38BDF8;
      font-weight: 600;
    }

    /* Estilos do Criador de Questionários */
    .question-builder-container {
      border: 1.5px solid var(--admin-card-border);
      border-radius: 14px;
      padding: 16px;
      background: rgba(15, 23, 42, 0.45);
      margin-top: 14px;
    }

    .question-item-card {
      background: var(--admin-card-bg);
      border: 1px solid var(--admin-card-border);
      border-left: 3px solid #38BDF8;
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 12px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
  </style>
</head>
<body>

  <!-- Mobile Toggle Button -->
  <button id="admin-mobile-toggle" class="admin-mobile-toggle" aria-label="Abrir Menu">☰</button>

  <div class="admin-layout-wrapper">

    <!-- =========================================================================
         1. SIDEBAR DE NAVEGAÇÃO
         ========================================================================= -->
    <aside class="admin-sidebar" id="admin-sidebar">
      
      <div class="sidebar-brand">
        <div class="sidebar-logo-badge">
          <img src="<?php echo asset('images/logo.png'); ?>" alt="ÂNCORA Logo">
        </div>
        <div class="sidebar-brand-text">
          <span class="sidebar-brand-title">ÂNCORA</span>
          <span class="sidebar-brand-sub">Gestão Institucional</span>
        </div>
      </div>

      <!-- Badge Perfil Dinâmico -->
      <div class="sidebar-role-badge <?php echo htmlspecialchars($perfilSlug ?? 'aluno'); ?>">
        <span class="role-dot"></span>
        <span><?php echo htmlspecialchars($userRoleTitle ?? $userRole ?? 'Usuário'); ?></span>
      </div>

      <!-- Grupo 1: PRINCIPAL -->
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">PRINCIPAL</span>
        
        <a href="<?php echo $inicioUrl ?? url('dashboard'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
          <span>Início</span>
        </a>
        
        <a href="<?php echo url('tarefas'); ?>" class="sidebar-menu-link active">
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
        </a>
        
        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>Mensagens</span>
        </a>
      </div>

      <!-- Grupo 2: GESTÃO -->
      <?php if (!empty($isDocenteOuAdmin)): ?>
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">GESTÃO</span>
        
        <a href="<?php echo ($perfilId === 1 ? url('admin/turmas') : '#'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          <span>Turmas</span>
        </a>
        
        <?php if ($perfilId === 1): ?>
        <a href="<?php echo url('usuarios'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>Usuários</span>
        </a>
        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <span>Perfis</span>
        </a>
        <?php endif; ?>
        
        <a href="<?php echo url('configuracoes'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          <span>Configurações</span>
        </a>
      </div>
      <?php else: ?>
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">GESTÃO</span>
        <a href="<?php echo url('configuracoes'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          <span>Configurações</span>
        </a>
      </div>
      <?php endif; ?>

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
          <div class="user-avatar-circle <?php echo htmlspecialchars($perfilSlug ?? 'aluno'); ?>">
            <?php echo htmlspecialchars($userInitials); ?>
          </div>
          <div class="user-info-text">
            <span class="user-name-title"><?php echo htmlspecialchars($userName); ?></span>
            <span class="user-role-sub"><?php echo htmlspecialchars($userRoleTitle ?? $userRole ?? 'Usuário'); ?></span>
          </div>
        </div>
        <a href="<?php echo url('logout'); ?>" class="logout-icon-btn" title="Sair do sistema">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>

    </aside>

    <!-- =========================================================================
         2. CONTEÚDO PRINCIPAL (MÓDULO DE TAREFAS)
         ========================================================================= -->
    <main class="admin-main-content">
      
      <!-- Topbar Header -->
      <header class="main-header-bar" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div>
          <h1 class="page-title" style="font-size: 1.8rem; font-weight: 800; margin: 0; color: var(--admin-text-heading);">Tarefas</h1>
          <p class="page-subtitle" style="font-size: 0.9rem; color: var(--admin-text-muted); margin-top: 4px;">
            <?php echo $isAluno ? 'Acompanhe e entregue suas tarefas acadêmicas' : 'Gerencie e crie tarefas acadêmicas'; ?>
          </p>
        </div>

        <?php if ($isDocenteOuAdmin): ?>
        <button type="button" class="btn-primary-action" id="btn-open-criar-tarefa" style="display: inline-flex; align-items: center; gap: 8px; background: #2563EB; color: #FFFFFF; border: none; border-radius: 10px; padding: 10px 18px; font-weight: 600; cursor: pointer;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Criar Tarefa
        </button>
        <?php endif; ?>
      </header>

      <!-- Mensagens Flash de Feedback -->
      <?php if (!empty($flashSuccess)): ?>
        <div class="auth-alert success" style="display: block; margin-bottom: 20px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #10B981; padding: 12px 16px; border-radius: 10px;">
          <?php echo htmlspecialchars($flashSuccess); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($flashError)): ?>
        <div class="auth-alert error" style="display: block; margin-bottom: 20px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #EF4444; padding: 12px 16px; border-radius: 10px;">
          <?php echo htmlspecialchars($flashError); ?>
        </div>
      <?php endif; ?>

      <!-- Barra de Filtros e Busca (Fiel ao Protótipo) -->
      <form action="<?php echo url('tarefas'); ?>" method="GET" class="filters-bar-card">
        <input type="hidden" name="route" value="tarefas">
        
        <div class="filters-title-row">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
          <span>Filtros e busca</span>
        </div>

        <div class="filters-inputs-grid">
          <!-- Campo de Busca por Texto -->
          <div style="position: relative;">
            <input 
              type="text" 
              name="busca" 
              placeholder="Buscar tarefa..." 
              value="<?php echo htmlspecialchars($busca); ?>"
              class="filter-search-input"
              style="padding-left: 38px;"
            >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 12px; top: 13px; color: var(--admin-text-muted);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>

          <!-- Dropdown Status -->
          <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="todos" <?php echo ($statusFiltro === 'todos' ? 'selected' : ''); ?>>Todos os status</option>
            <option value="pendente" <?php echo ($statusFiltro === 'pendente' ? 'selected' : ''); ?>>⏳ Pendente</option>
            <option value="entregue" <?php echo ($statusFiltro === 'entregue' ? 'selected' : ''); ?>>✅ Entregue</option>
            <option value="avaliada" <?php echo ($statusFiltro === 'avaliada' ? 'selected' : ''); ?>>🏅 Avaliada</option>
            <option value="atrasada" <?php echo ($statusFiltro === 'atrasada' ? 'selected' : ''); ?>>⚠️ Atrasada</option>
          </select>

          <!-- Dropdown Turmas -->
          <select name="turma" class="filter-select" onchange="this.form.submit()">
            <option value="todas" <?php echo ($turmaFiltro === 'todas' ? 'selected' : ''); ?>>Todas as turmas</option>
            <?php if (!empty($turmasDisponiveis)): ?>
              <?php foreach ($turmasDisponiveis as $td): ?>
                <option value="<?php echo (int)$td['id']; ?>" <?php echo ($turmaFiltro == $td['id'] ? 'selected' : ''); ?>>
                  <?php echo htmlspecialchars($td['nome']); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>

          <!-- Dropdown Ordenação -->
          <select name="ordem" class="filter-select" onchange="this.form.submit()">
            <option value="recentes" <?php echo ($ordenacao === 'recentes' ? 'selected' : ''); ?>>📊 Mais recentes</option>
            <option value="antigas" <?php echo ($ordenacao === 'antigas' ? 'selected' : ''); ?>>📊 Mais antigas</option>
          </select>
        </div>
      </form>

      <!-- Contador de Tarefas Encontradas -->
      <div style="font-size: 0.875rem; color: var(--admin-text-muted); margin: 16px 0 12px 0;">
        <?php echo count($tarefas); ?> tarefa(s) encontrada(s)
      </div>

      <!-- Lista de Cards de Tarefas -->
      <div class="tarefas-container">
        <?php if (empty($tarefas)): ?>
          <div style="background: var(--admin-card-bg); border: 1px solid var(--admin-card-border); border-radius: 16px; padding: 48px; text-align: center; color: var(--admin-text-muted);">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; color: #38BDF8;"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--admin-text-heading); margin-bottom: 6px;">Nenhuma tarefa encontrada</h3>
            <p style="font-size: 0.875rem;">
              <?php echo $isDocenteOuAdmin ? 'Clique em "+ Criar Tarefa" para publicar a primeira atividade.' : 'Você não possui atividades pendentes para este filtro.'; ?>
            </p>
          </div>
        <?php else: ?>
          <?php foreach ($tarefas as $t): ?>
            <?php 
              $isAtrasada = ($t['is_atrasada'] == 1);
              $badgeStatusClass = 'pendente';
              $badgeStatusLabel = 'Pendente';

              if ($isAluno) {
                $statusAluno = $t['status_aluno'] ?? 'pendente';
                if ($statusAluno === 'entregue' || $statusAluno === 'corrigida') {
                  $badgeStatusClass = 'entregue';
                  $badgeStatusLabel = 'Entregue';
                } elseif ($statusAluno === 'devolvida') {
                  $badgeStatusClass = 'devolvida';
                  $notaExibida = ($t['entrega_nota'] !== null) ? number_format((float)$t['entrega_nota'], 1, ',', '.') : '10,0';
                  $badgeStatusLabel = "Nota: {$notaExibida}";
                } elseif ($statusAluno === 'atrasada') {
                  $badgeStatusClass = 'atrasada';
                  $badgeStatusLabel = 'Atrasada';
                }
              } else {
                if ($isAtrasada) {
                  $badgeStatusClass = 'atrasada';
                  $badgeStatusLabel = 'Encerrada / Atrasada';
                } else {
                  $badgeStatusClass = 'entregue';
                  $badgeStatusLabel = 'Aberta';
                }
              }
            ?>
            <div class="tarefa-card-item <?php echo ($isAtrasada && ($isAluno ? ($t['status_aluno'] === 'atrasada') : true)) ? 'is-atrasada' : ''; ?>">
              
              <div class="tarefa-header-row">
                <div>
                  <div class="tarefa-title-group">
                    <h3 class="tarefa-card-title"><?php echo htmlspecialchars($t['titulo']); ?></h3>
                    
                    <span class="tarefa-badge <?php echo $badgeStatusClass; ?>">
                      <?php echo htmlspecialchars($badgeStatusLabel); ?>
                    </span>

                    <?php if (!empty($t['disciplina'])): ?>
                      <span class="tarefa-badge disciplina">
                        📚 <?php echo htmlspecialchars($t['disciplina']); ?>
                      </span>
                    <?php endif; ?>
                  </div>

                  <?php if (!empty($t['descricao'])): ?>
                    <p class="tarefa-description-text" style="margin-top: 8px;">
                      <?php echo nl2br(htmlspecialchars(mb_strimwidth($t['descricao'], 0, 180, '...'))); ?>
                    </p>
                  <?php endif; ?>
                </div>

                <!-- Botões de Ação -->
                <div class="tarefa-card-actions">
                  <?php if ($isAluno): ?>
                    <a href="<?php echo url('tarefas/detalhes', ['id' => $t['id']]); ?>" class="btn-card-action primary">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                      <?php echo ($t['status_aluno'] === 'pendente' || $t['status_aluno'] === 'atrasada') ? 'Fazer Atividade' : 'Ver Atividade'; ?>
                    </a>
                  <?php else: ?>
                    <a href="<?php echo url('tarefas/entregas', ['id' => $t['id']]); ?>" class="btn-card-action secondary" title="Visualizar e corrigir entregas dos alunos">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                      Ver entregas
                    </a>

                    <button type="button" class="btn-card-action secondary danger btn-excluir-tarefa" data-id="<?php echo (int)$t['id']; ?>" data-titulo="<?php echo htmlspecialchars($t['titulo']); ?>" title="Excluir tarefa">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Metadados de Prazo e Destinatários -->
              <div class="tarefa-footer-info">
                <div class="tarefa-meta-items">
                  <div class="tarefa-meta-item" style="<?php echo $isAtrasada ? 'color: #EF4444; font-weight: 600;' : ''; ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Prazo: <?php echo htmlspecialchars($t['prazo_formatado']); ?></span>
                  </div>

                  <div class="tarefa-meta-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                    <span>Destinatários: <?php echo htmlspecialchars($t['destinatarios_resumo']); ?></span>
                  </div>

                  <?php if (!empty($t['materiais_count']) && $t['materiais_count'] > 0): ?>
                    <div class="tarefa-meta-item">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                      <span><?php echo $t['materiais_count']; ?> material(is)</span>
                    </div>
                  <?php endif; ?>

                  <?php if (!empty($t['questoes_count']) && $t['questoes_count'] > 0): ?>
                    <div class="tarefa-meta-item">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                      <span><?php echo $t['questoes_count']; ?> questão(ões)</span>
                    </div>
                  <?php endif; ?>
                </div>

                <?php if ($isDocenteOuAdmin && isset($t['resumo_entregas'])): ?>
                  <div style="font-size: 0.85rem; color: var(--admin-text-muted);">
                    <strong style="color: #38BDF8;"><?php echo $t['resumo_entregas']['total_entregues']; ?></strong> de <strong><?php echo $t['resumo_entregas']['total_destinatarios']; ?></strong> entregaram
                  </div>
                <?php endif; ?>
              </div>

            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </main>
  </div>

  <!-- =========================================================================
       3. MODAIS INTERATIVOS (CRIAR TAREFA E EXCLUIR TAREFA)
       ========================================================================= -->
  <?php if ($isDocenteOuAdmin): ?>
  <!-- MODAL: CRIAR NOVA TAREFA (Fiel à imagem media_1788198099245.png) -->
  <div class="modal-overlay" id="modal-criar-tarefa" style="display:none;">
    <div class="modal-container" style="max-width: 650px; max-height: 90vh; overflow-y: auto;">
      <div class="modal-header">
        <h3 class="modal-title">Criar Nova Tarefa</h3>
        <button type="button" class="modal-close-btn" id="btn-close-criar-tarefa">&times;</button>
      </div>

      <!-- Info Banner Azul Oficial -->
      <div style="background: rgba(37, 99, 235, 0.15); border: 1px solid rgba(37, 99, 235, 0.3); border-radius: 10px; padding: 12px 16px; display: flex; align-items: flex-start; gap: 10px; margin-bottom: 18px; color: #38BDF8; font-size: 0.85rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <div>Preencha os campos abaixo e selecione a(s) turma(s) destinatárias. Campos marcados com * são obrigatórios.</div>
      </div>

      <form action="<?php echo url('tarefas/criar'); ?>" method="POST" enctype="multipart/form-data" id="form-criar-tarefa">
        
        <!-- Título -->
        <div class="form-group-row">
          <label class="form-label-title">Título *</label>
          <input type="text" name="titulo" class="form-input-control" placeholder="Ex: Trabalho de Algoritmos" required>
        </div>

        <!-- Descrição -->
        <div class="form-group-row">
          <label class="form-label-title">Descrição</label>
          <textarea name="descricao" class="form-input-control" rows="3" placeholder="Descreva as orientações da tarefa..."></textarea>
        </div>

        <!-- Disciplina e Tipo de Atividade -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;" class="form-group-row">
          <div>
            <label class="form-label-title">Disciplina</label>
            <input type="text" name="disciplina" class="form-input-control" placeholder="Ex: Matemática, Geografia">
          </div>

          <div>
            <label class="form-label-title">Tipo de Atividade</label>
            <select name="tipo_atividade" id="select-tipo-atividade" class="form-input-control">
              <option value="tradicional">Tradicional (Instruções + Arquivo)</option>
              <option value="questionario">Questionário ÂNCORA</option>
              <option value="hibrida">Híbrida (Questionário + Arquivo)</option>
            </select>
          </div>
        </div>

        <!-- Prazo de Entrega (Data e Horário) -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;" class="form-group-row">
          <div>
            <label class="form-label-title">Data Limite de Entrega *</label>
            <input type="date" name="prazo_data" class="form-input-control" required min="<?php echo date('Y-m-d'); ?>">
          </div>
          <div>
            <label class="form-label-title">Horário Limite *</label>
            <input type="time" name="prazo_hora" class="form-input-control" value="23:59" required>
          </div>
        </div>

        <!-- Destinatários: Turmas -->
        <div class="form-group-row">
          <label class="form-label-title">Turmas * (selecione ao menos uma turma ou aluno)</label>
          <div class="checkboxes-pill-grid">
            <?php if (!empty($turmasDisponiveis)): ?>
              <?php foreach ($turmasDisponiveis as $td): ?>
                <label class="checkbox-pill-label">
                  <input type="checkbox" name="turmas[]" value="<?php echo (int)$td['id']; ?>">
                  <span><?php echo htmlspecialchars($td['nome']); ?></span>
                </label>
              <?php endforeach; ?>
            <?php else: ?>
              <div style="font-size: 0.8rem; color: var(--admin-text-muted); padding: 4px;">Nenhuma turma cadastrada.</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Destinatários Individuais Opcionais (Alunos específicos) -->
        <details style="margin-bottom: 16px;">
          <summary style="cursor: pointer; font-size: 0.85rem; font-weight: 600; color: #38BDF8;">+ Selecionar Alunos Específicos Individualmente</summary>
          <div class="checkboxes-pill-grid" style="margin-top: 8px;">
            <?php if (!empty($alunosDisponiveis)): ?>
              <?php foreach ($alunosDisponiveis as $ad): ?>
                <label class="checkbox-pill-label">
                  <input type="checkbox" name="alunos[]" value="<?php echo (int)$ad['id']; ?>">
                  <span><?php echo htmlspecialchars($ad['nome']); ?></span>
                </label>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </details>

        <!-- Anexo (Materiais de Apoio) -->
        <div class="form-group-row">
          <label class="form-label-title">Materiais de Apoio (opcional)</label>
          <input type="file" name="materiais[]" class="form-input-control" multiple>
          <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Formatos suportados: PDF, DOC, DOCX, PPT, XLS, ZIP, imagens, etc.</span>
        </div>

        <!-- Seção do Criador de Questionários do ÂNCORA (exibido dinamicamente) -->
        <div id="section-questionario-builder" class="question-builder-container" style="display: none;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <strong style="color: var(--admin-text-heading); font-size: 0.95rem;">Perguntas do Questionário</strong>
            <button type="button" id="btn-add-questao" style="background: rgba(37, 99, 235, 0.2); border: 1px solid #2563EB; color: #38BDF8; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
              + Adicionar Pergunta
            </button>
          </div>
          
          <div id="questoes-list-container">
            <!-- Questões inseridas dinamicamente via JS -->
          </div>

          <input type="hidden" name="questoes_json" id="input-questoes-json" value="[]">
        </div>

        <!-- Permissão de anexo pelo aluno -->
        <div class="form-group-row" style="margin-top: 14px;">
          <label class="checkbox-pill-label" style="padding: 0;">
            <input type="checkbox" name="permite_anexo_aluno" value="1" checked>
            <span>Permitir que o aluno envie arquivos na resposta</span>
          </label>
        </div>

        <!-- Botões de Ação do Modal -->
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; border-top: 1px solid var(--admin-item-row-border); padding-top: 16px;">
          <button type="button" class="btn-card-action secondary" id="btn-cancelar-criar-tarefa">Cancelar</button>
          <button type="submit" class="btn-card-action primary">Criar Tarefa</button>
        </div>

      </form>
    </div>
  </div>

  <!-- MODAL: CONFIRMAÇÃO DE EXCLUSÃO -->
  <div class="modal-overlay" id="modal-excluir-tarefa" style="display:none;">
    <div class="modal-container" style="max-width: 440px;">
      <div class="modal-header">
        <h3 class="modal-title" style="color: #EF4444;">Excluir Tarefa</h3>
        <button type="button" class="modal-close-btn" id="btn-close-excluir-tarefa">&times;</button>
      </div>

      <p style="color: var(--admin-text-muted); font-size: 0.9rem; margin-bottom: 20px;">
        Tem certeza de que deseja excluir a tarefa <strong id="excluir-tarefa-titulo" style="color: var(--admin-text-heading);"></strong>?
      </p>

      <form action="<?php echo url('tarefas/excluir'); ?>" method="POST">
        <input type="hidden" name="tarefa_id" id="input-excluir-tarefa-id" value="">
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" class="btn-card-action secondary" id="btn-cancelar-excluir-tarefa">Cancelar</button>
          <button type="submit" class="btn-card-action primary" style="background: #EF4444;">Excluir Tarefa</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <script src="<?php echo $assetUrl; ?>js/admin.js"></script>

  <script>
    // Gerenciamento dos Modais e Criador de Questionários em Vanilla JS
    document.addEventListener('DOMContentLoaded', function() {
      
      const modalCriar = document.getElementById('modal-criar-tarefa');
      const btnOpenCriar = document.getElementById('btn-open-criar-tarefa');
      const btnCloseCriar = document.getElementById('btn-close-criar-tarefa');
      const btnCancelCriar = document.getElementById('btn-cancelar-criar-tarefa');

      if (btnOpenCriar && modalCriar) {
        btnOpenCriar.addEventListener('click', () => { 
          modalCriar.classList.add('active'); 
          modalCriar.style.display = 'flex'; 
        });
        btnCloseCriar.addEventListener('click', () => { 
          modalCriar.classList.remove('active'); 
          modalCriar.style.display = 'none'; 
        });
        btnCancelCriar.addEventListener('click', () => { 
          modalCriar.classList.remove('active'); 
          modalCriar.style.display = 'none'; 
        });
      }

      // Modal de Exclusão
      const modalExcluir = document.getElementById('modal-excluir-tarefa');
      const btnCloseExcluir = document.getElementById('btn-close-excluir-tarefa');
      const btnCancelExcluir = document.getElementById('btn-cancelar-excluir-tarefa');
      const inputExcluirId = document.getElementById('input-excluir-tarefa-id');
      const textExcluirTitulo = document.getElementById('excluir-tarefa-titulo');

      document.querySelectorAll('.btn-excluir-tarefa').forEach(btn => {
        btn.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const titulo = this.getAttribute('data-titulo');
          if (inputExcluirId && textExcluirTitulo && modalExcluir) {
            inputExcluirId.value = id;
            textExcluirTitulo.textContent = `"${titulo}"`;
            modalExcluir.classList.add('active');
            modalExcluir.style.display = 'flex';
          }
        });
      });

      if (modalExcluir) {
        btnCloseExcluir.addEventListener('click', () => { 
          modalExcluir.classList.remove('active'); 
          modalExcluir.style.display = 'none'; 
        });
        btnCancelExcluir.addEventListener('click', () => { 
          modalExcluir.classList.remove('active'); 
          modalExcluir.style.display = 'none'; 
        });
      }

      // Alternância da seção de questionário conforme tipo de atividade selecionado
      const selectTipo = document.getElementById('select-tipo-atividade');
      const sectionQuest = document.getElementById('section-questionario-builder');
      if (selectTipo && sectionQuest) {
        selectTipo.addEventListener('change', function() {
          if (this.value === 'questionario' || this.value === 'hibrida') {
            sectionQuest.style.display = 'block';
          } else {
            sectionQuest.style.display = 'none';
          }
        });
      }

      // Engine do Criador de Questionários
      const questoesList = document.getElementById('questoes-list-container');
      const btnAddQ = document.getElementById('btn-add-questao');
      const inputQuestoesJson = document.getElementById('input-questoes-json');
      let questoes = [];

      function renderQuestoes() {
        if (!questoesList) return;
        questoesList.innerHTML = '';

        if (questoes.length === 0) {
          questoesList.innerHTML = '<div style="font-size: 0.85rem; color: var(--admin-text-muted); text-align: center; padding: 12px;">Nenhuma questão adicionada ainda.</div>';
          if (inputQuestoesJson) inputQuestoesJson.value = '[]';
          return;
        }

        questoes.forEach((q, idx) => {
          const card = document.createElement('div');
          card.className = 'question-item-card';
          card.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 0.85rem; font-weight: 700; color: #38BDF8;">Questão ${idx + 1} (${q.tipo})</span>
              <button type="button" class="btn-remove-q" data-idx="${idx}" style="background: none; border: none; color: #EF4444; cursor: pointer; font-size: 0.85rem;">Remover</button>
            </div>
            <input type="text" class="form-input-control q-enunciado" data-idx="${idx}" placeholder="Enunciado da questão..." value="${escapeHtml(q.enunciado || '')}">
            <div style="display: flex; gap: 10px; align-items: center;">
              <label style="font-size: 0.8rem; color: var(--admin-text-muted);">Pontos:</label>
              <input type="number" step="0.5" class="form-input-control q-pontos" data-idx="${idx}" style="width: 80px;" value="${q.pontos || 1.0}">
            </div>
          `;
          questoesList.appendChild(card);
        });

        // Eventos de inputs
        document.querySelectorAll('.q-enunciado').forEach(inp => {
          inp.addEventListener('input', function() {
            const i = this.getAttribute('data-idx');
            questoes[i].enunciado = this.value;
            if (inputQuestoesJson) inputQuestoesJson.value = JSON.stringify(questoes);
          });
        });

        document.querySelectorAll('.q-pontos').forEach(inp => {
          inp.addEventListener('input', function() {
            const i = this.getAttribute('data-idx');
            questoes[i].pontos = parseFloat(this.value) || 1.0;
            if (inputQuestoesJson) inputQuestoesJson.value = JSON.stringify(questoes);
          });
        });

        document.querySelectorAll('.btn-remove-q').forEach(btn => {
          btn.addEventListener('click', function() {
            const i = parseInt(this.getAttribute('data-idx'));
            questoes.splice(i, 1);
            renderQuestoes();
          });
        });

        if (inputQuestoesJson) inputQuestoesJson.value = JSON.stringify(questoes);
      }

      if (btnAddQ) {
        btnAddQ.addEventListener('click', function() {
          questoes.push({
            enunciado: '',
            tipo: 'discursiva',
            pontos: 1.0,
            obrigatoria: 1,
            alternativas: [],
            resposta_correta: null
          });
          renderQuestoes();
        });
      }

      function escapeHtml(text) {
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
      }
    });
  </script>

</body>
</html>
