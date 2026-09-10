<?php 
$assetUrl = defined('ASSET_URL') ? ASSET_URL : 'public/'; 
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
$openMembrosModalId = $_SESSION['open_membros_modal'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['open_membros_modal']);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? $pageTitle : 'Turmas — ÂNCORA'; ?></title>
  
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
    /* Estilos Customizados Fies ao Protótipo das Turmas */
    .turmas-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap: 20px;
      margin-top: 24px;
    }

    .turma-card {
      background: var(--admin-card-bg);
      border: 1px solid var(--admin-card-border);
      border-radius: 16px;
      padding: 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: all var(--transition-fast);
    }

    .turma-card:hover {
      border-color: var(--admin-card-hover-border);
      box-shadow: var(--shadow-md);
    }

    .turma-card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 14px;
    }

    .turma-card-title {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--admin-text-heading);
      margin: 0 0 4px 0;
    }

    .turma-card-subtitle {
      font-size: 0.825rem;
      color: var(--admin-text-muted);
      margin: 0;
    }

    .turma-card-actions {
      display: flex;
      gap: 8px;
    }

    .btn-icon-action {
      background: transparent;
      border: 1px solid var(--admin-item-row-border);
      border-radius: 8px;
      width: 32px;
      height: 32px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: var(--admin-text-muted);
      cursor: pointer;
      transition: all var(--transition-fast);
    }

    .btn-icon-action:hover {
      background: var(--admin-item-row-bg);
      color: var(--admin-text-heading);
    }

    .btn-icon-action.danger:hover {
      background: rgba(239, 68, 68, 0.15);
      border-color: rgba(239, 68, 68, 0.3);
      color: #EF4444;
    }

    .turma-membros-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 20px;
      min-height: 50px;
    }

    .membro-item-preview {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.85rem;
      color: var(--admin-text-muted);
    }

    .membro-item-preview svg {
      width: 16px;
      height: 16px;
      stroke: #38BDF8;
    }

    .membro-item-preview.aluno svg {
      stroke: #94A3B8;
    }

    .btn-gerenciar-membros {
      width: 100%;
      height: 40px;
      border-radius: 10px;
      border: 1px solid var(--admin-item-row-border);
      background: var(--admin-item-row-bg);
      color: var(--admin-text-heading);
      font-size: 0.875rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: pointer;
      transition: all var(--transition-fast);
    }

    .btn-gerenciar-membros:hover {
      border-color: #2563EB;
      background: rgba(37, 99, 235, 0.1);
      color: #38BDF8;
    }

    /* Modal Estilizado Fiel aos Protótipos */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(2, 6, 23, 0.75);
      backdrop-filter: blur(4px);
      z-index: 999;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .modal-overlay.active {
      display: flex;
    }

    .modal-container {
      background: #0F172A;
      border: 1px solid #1E293B;
      border-radius: 16px;
      width: 100%;
      max-width: 480px;
      padding: 24px;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
      color: #F8FAFC;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .modal-title {
      font-size: 1.15rem;
      font-weight: 700;
      color: #F8FAFC;
      margin: 0;
    }

    .modal-close-btn {
      background: transparent;
      border: none;
      color: #94A3B8;
      font-size: 1.2rem;
      cursor: pointer;
    }

    .modal-close-btn:hover {
      color: #F8FAFC;
    }

    .membros-section-title {
      font-size: 0.9rem;
      font-weight: 700;
      color: #F8FAFC;
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 16px 0 10px 0;
    }

    .membro-badge-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #1E293B;
      border: 1px solid #334155;
      border-radius: 10px;
      padding: 10px 14px;
      margin-bottom: 8px;
      font-size: 0.875rem;
    }

    .btn-remove-badge {
      background: transparent;
      border: none;
      color: #EF4444;
      font-size: 1.1rem;
      cursor: pointer;
      line-height: 1;
    }

    .add-member-form-row {
      display: flex;
      gap: 8px;
      margin-bottom: 16px;
    }

    .add-member-select {
      flex: 1;
      height: 42px;
      background: #1E293B;
      border: 1px solid #334155;
      border-radius: 10px;
      color: #F8FAFC;
      padding: 0 12px;
      font-size: 0.875rem;
    }

    .add-member-input {
      flex: 1;
      height: 42px;
      background: #1E293B;
      border: 1px solid #334155;
      border-radius: 10px;
      color: #F8FAFC;
      padding: 0 12px;
      font-size: 0.875rem;
    }

    .btn-add-member {
      width: 42px;
      height: 42px;
      background: #2563EB;
      border: none;
      border-radius: 10px;
      color: #FFFFFF;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 1.2rem;
      font-weight: bold;
    }

    .btn-add-member:hover {
      background: #1D4ED8;
    }
  </style>
</head>
<body>

  <!-- Mobile Toggle Button -->
  <button id="admin-mobile-toggle" class="admin-mobile-toggle" aria-label="Abrir Menu">☰</button>

  <div class="admin-layout-wrapper">
    
    <!-- =========================================================================
         1. SIDEBAR LATERAL DA APLICAÇÃO
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
        
        <a href="<?php echo url('admin/turmas'); ?>" class="sidebar-menu-link active">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          <span>Turmas</span>
        </a>
        
        <a href="<?php echo url('usuarios'); ?>" class="sidebar-menu-link">
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

      <!-- Rodapé da Sidebar -->
      <div class="sidebar-footer">
        <button id="theme-toggle-btn" class="theme-toggle-btn">
          <svg id="theme-icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <span id="theme-toggle-text">Tema Claro</span>
        </button>
        
        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <span>Central de Ajuda</span>
        </a>

        <!-- User Profile Card -->
        <div class="user-profile-badge">
          <div class="user-avatar-circle">
            <?php echo htmlspecialchars($userInitials); ?>
          </div>
          <div class="user-info-text">
            <span class="user-name-title"><?php echo htmlspecialchars($userName); ?></span>
            <span class="user-role-sub"><?php echo htmlspecialchars($userRole); ?></span>
          </div>
          <a href="<?php echo url('logout'); ?>" class="btn-logout-icon" title="Sair do sistema">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </a>
        </div>
      </div>

    </aside>

    <!-- =========================================================================
         2. CONTEÚDO PRINCIPAL (MÓDULO DE TURMAS)
         ========================================================================= -->
    <main class="admin-main-content">
      
      <!-- Topbar Header com Título e Botão Nova Turma -->
      <header class="main-header-bar" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
        <div>
          <h1 class="page-title" style="font-size: 1.8rem; font-weight: 800; margin: 0; color: var(--admin-text-heading);">Turmas</h1>
          <p class="page-subtitle" style="font-size: 0.9rem; color: var(--admin-text-muted); margin-top: 4px;">Gerencie turmas e vínculos de alunos e professores</p>
        </div>

        <button type="button" class="btn-primary-action" id="btn-open-nova-turma" style="display: inline-flex; align-items: center; gap: 8px; background: #2563EB; color: #FFFFFF; border: none; border-radius: 10px; padding: 10px 18px; font-weight: 600; cursor: pointer;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nova Turma
        </button>
      </header>

      <!-- Caixas de Alerta (Sucesso ou Erro) -->
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

      <!-- Barra de Busca de Turmas -->
      <form action="<?php echo url('admin/turmas'); ?>" method="GET" style="margin-bottom: 20px;">
        <input type="hidden" name="route" value="admin/turmas">
        <div style="position: relative; max-width: 400px;">
          <input 
            type="text" 
            name="busca" 
            placeholder="Buscar turma por nome..." 
            value="<?php echo htmlspecialchars($busca ?? ''); ?>"
            style="width: 100%; height: 42px; padding: 0 16px 0 40px; border-radius: 10px; border: 1px solid var(--admin-card-border); background: var(--admin-card-bg); color: var(--admin-text-heading); font-family: inherit; font-size: 0.875rem;"
          >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 14px; top: 12px; color: var(--admin-text-muted);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
      </form>

      <!-- Grid dos Cards de Turmas -->
      <?php if (empty($turmas)): ?>
        <div style="background: var(--admin-card-bg); border: 1px solid var(--admin-card-border); border-radius: 16px; padding: 48px; text-align: center; color: var(--admin-text-muted);">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; color: #38BDF8;"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--admin-text-heading); margin-bottom: 6px;">Nenhuma turma encontrada</h3>
          <p style="font-size: 0.875rem; margin-bottom: 20px;">Clique em "+ Nova Turma" para cadastrar a primeira turma da sua instituição.</p>
        </div>
      <?php else: ?>
        <div class="turmas-grid">
          <?php foreach ($turmas as $t): ?>
            <?php 
              $qtdAlunos = (int)$t['total_alunos'];
              $qtdProfs  = (int)$t['total_professores'];
              $labelAlunos = $qtdAlunos === 1 ? '1 aluno' : "{$qtdAlunos} alunos";
              $labelProfs  = $qtdProfs === 1 ? '1 professor' : "{$qtdProfs} professores";
            ?>
            <div class="turma-card">
              <div>
                <!-- Header do Card -->
                <div class="turma-card-header">
                  <div>
                    <h3 class="turma-card-title"><?php echo htmlspecialchars($t['nome']); ?></h3>
                    <p class="turma-card-subtitle"><?php echo "{$labelAlunos} · {$labelProfs}"; ?></p>
                  </div>
                  
                  <!-- Ações do Card (Editar / Excluir) -->
                  <div class="turma-card-actions">
                    <button 
                      type="button" 
                      class="btn-icon-action btn-edit-turma" 
                      data-id="<?php echo $t['id']; ?>" 
                      data-nome="<?php echo htmlspecialchars($t['nome']); ?>" 
                      title="Editar nome da turma"
                    >
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </button>
                    
                    <button 
                      type="button" 
                      class="btn-icon-action danger btn-delete-turma" 
                      data-id="<?php echo $t['id']; ?>" 
                      data-nome="<?php echo htmlspecialchars($t['nome']); ?>" 
                      title="Excluir turma"
                    >
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </button>
                  </div>
                </div>

                <!-- Lista Prévia de Membros -->
                <div class="turma-membros-list">
                  <?php if (!empty($t['professores'])): ?>
                    <?php foreach ($t['professores'] as $prof): ?>
                      <div class="membro-item-preview professor">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        <span>
                          <?php 
                            $discText = !empty($prof['disciplina']) ? " - " . htmlspecialchars($prof['disciplina']) : "";
                            echo "Prof. " . htmlspecialchars($prof['nome']) . $discText; 
                          ?>
                        </span>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>

                  <?php if (!empty($t['alunos'])): ?>
                    <?php foreach ($t['alunos'] as $aluno): ?>
                      <div class="membro-item-preview aluno">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        <span><?php echo htmlspecialchars($aluno['nome']); ?></span>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>

                  <?php if (empty($t['professores']) && empty($t['alunos'])): ?>
                    <span style="font-size: 0.8rem; color: var(--admin-text-muted); font-style: italic;">Nenhum membro vinculado ainda.</span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Botão do Rodapé do Card: Gerenciar Membros -->
              <button 
                type="button" 
                class="btn-gerenciar-membros btn-open-membros"
                data-id="<?php echo $t['id']; ?>"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="17" y1="11" x2="23" y2="11"/></svg>
                <span>Gerenciar membros</span>
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </main>

  </div>

  <!-- =========================================================================
       3. MODAIS INTERATIVOS FIÉIS ÀS FOTOS
       ========================================================================= -->

  <!-- MODAL 1: Nova Turma / Editar Turma (Imagem 3) -->
  <div id="modal-turma-form" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h3 id="modal-turma-title" class="modal-title">Nova Turma</h3>
        <button type="button" class="modal-close-btn btn-close-modal">✕</button>
      </div>

      <form action="<?php echo url('admin/turmas'); ?>" method="POST" id="form-turma-crud">
        <input type="hidden" name="action" id="turma-form-action" value="criar">
        <input type="hidden" name="turma_id" id="turma-form-id" value="">

        <div style="margin-bottom: 24px;">
          <label for="nome_turma" style="display: block; font-size: 0.85rem; font-weight: 700; color: #F8FAFC; margin-bottom: 8px;">Nome da turma *</label>
          <input 
            type="text" 
            id="nome_turma" 
            name="nome_turma" 
            placeholder="Ex: 1º Ano A" 
            required 
            style="width: 100%; height: 44px; background: #1E293B; border: 1px solid #334155; border-radius: 10px; color: #F8FAFC; padding: 0 14px; font-size: 0.9rem;"
          >
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
          <button type="button" class="btn-close-modal" style="height: 42px; padding: 0 20px; background: transparent; border: 1px solid #334155; border-radius: 10px; color: #F8FAFC; font-weight: 600; cursor: pointer;">Cancelar</button>
          <button type="submit" id="modal-turma-btn-submit" style="height: 42px; padding: 0 20px; background: #2563EB; border: none; border-radius: 10px; color: #FFFFFF; font-weight: 600; cursor: pointer;">Criar Turma</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL 2: Gerenciar Membros (Imagem 2) -->
  <?php foreach ($turmas as $t): ?>
    <div id="modal-membros-<?php echo $t['id']; ?>" class="modal-overlay modal-membros-item">
      <div class="modal-container">
        <div class="modal-header">
          <h3 class="modal-title">Membros — <?php echo htmlspecialchars($t['nome']); ?></h3>
          <button type="button" class="modal-close-btn btn-close-modal">✕</button>
        </div>

        <!-- SEÇÃO 1: PROFESSORES -->
        <div class="membros-section-title">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          <span>Professores</span>
        </div>

        <!-- Lista dos professores já vinculados -->
        <div style="margin-bottom: 12px;">
          <?php if (!empty($t['professores'])): ?>
            <?php foreach ($t['professores'] as $prof): ?>
              <div class="membro-badge-row">
                <span>
                  <?php 
                    $discText = !empty($prof['disciplina']) ? " - " . htmlspecialchars($prof['disciplina']) : "";
                    echo "Prof. " . htmlspecialchars($prof['nome']) . $discText; 
                  ?>
                </span>
                <form action="<?php echo url('admin/turmas'); ?>" method="POST" style="display: inline;">
                  <input type="hidden" name="action" value="remover_professor">
                  <input type="hidden" name="turma_id" value="<?php echo $t['id']; ?>">
                  <input type="hidden" name="professor_id" value="<?php echo $prof['professor_id']; ?>">
                  <input type="hidden" name="vinculo_id" value="<?php echo $prof['vinculo_id']; ?>">
                  <button type="submit" class="btn-remove-badge" title="Remover professor da turma">✕</button>
                </form>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="font-size: 0.8rem; color: #94A3B8; margin-bottom: 12px; font-style: italic;">Nenhum professor vinculado.</p>
          <?php endif; ?>
        </div>

        <!-- Formulário para adicionar professor -->
        <form action="<?php echo url('admin/turmas'); ?>" method="POST" class="add-member-form-row">
          <input type="hidden" name="action" value="adicionar_professor">
          <input type="hidden" name="turma_id" value="<?php echo $t['id']; ?>">

          <select name="professor_id" class="add-member-select" required>
            <option value="">Selecionar professor...</option>
            <?php foreach ($professoresDisponiveis as $p): ?>
              <option value="<?php echo $p['id']; ?>"><?php echo "Prof. " . htmlspecialchars($p['nome']); ?></option>
            <?php endforeach; ?>
          </select>

          <input type="text" name="disciplina" class="add-member-input" placeholder="Disciplina (ex: Matemática)">
          
          <button type="submit" class="btn-add-member" title="Adicionar Professor">+</button>
        </form>

        <!-- SEÇÃO 2: ALUNOS -->
        <div class="membros-section-title" style="margin-top: 24px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          <span>Alunos</span>
        </div>

        <!-- Lista dos alunos já vinculados -->
        <div style="margin-bottom: 12px;">
          <?php if (!empty($t['alunos'])): ?>
            <?php foreach ($t['alunos'] as $aluno): ?>
              <div class="membro-badge-row">
                <span><?php echo htmlspecialchars($aluno['nome']); ?></span>
                <form action="<?php echo url('admin/turmas'); ?>" method="POST" style="display: inline;">
                  <input type="hidden" name="action" value="remover_aluno">
                  <input type="hidden" name="turma_id" value="<?php echo $t['id']; ?>">
                  <input type="hidden" name="aluno_id" value="<?php echo $aluno['aluno_id']; ?>">
                  <button type="submit" class="btn-remove-badge" title="Remover aluno da turma">✕</button>
                </form>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="font-size: 0.8rem; color: #94A3B8; margin-bottom: 12px; font-style: italic;">Nenhum aluno vinculado.</p>
          <?php endif; ?>
        </div>

        <!-- Formulário para adicionar aluno -->
        <form action="<?php echo url('admin/turmas'); ?>" method="POST" class="add-member-form-row">
          <input type="hidden" name="action" value="adicionar_aluno">
          <input type="hidden" name="turma_id" value="<?php echo $t['id']; ?>">

          <select name="aluno_id" class="add-member-select" required>
            <option value="">Selecionar aluno...</option>
            <?php foreach ($alunosDisponiveis as $a): ?>
              <option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['nome']); ?></option>
            <?php endforeach; ?>
          </select>
          
          <button type="submit" class="btn-add-member" title="Adicionar Aluno">+</button>
        </form>

      </div>
    </div>
  <?php endforeach; ?>

  <!-- MODAL 3: Confirmar Exclusão de Turma -->
  <div id="modal-excluir-turma" class="modal-overlay">
    <div class="modal-container" style="max-width: 400px; text-align: center;">
      <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); color: #EF4444; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
      </div>

      <h3 style="font-size: 1.15rem; font-weight: 700; color: #F8FAFC; margin-bottom: 8px;">Excluir Turma</h3>
      <p style="font-size: 0.875rem; color: #94A3B8; margin-bottom: 24px; line-height: 1.4;">
        Tem certeza que deseja excluir a turma <strong id="delete-turma-name" style="color: #F8FAFC;"></strong>? Todos os vínculos de membros serão removidos.
      </p>

      <form action="<?php echo url('admin/turmas'); ?>" method="POST">
        <input type="hidden" name="action" value="excluir">
        <input type="hidden" name="turma_id" id="delete-turma-id" value="">

        <div style="display: flex; gap: 12px; justify-content: center;">
          <button type="button" class="btn-close-modal" style="height: 42px; padding: 0 20px; background: transparent; border: 1px solid #334155; border-radius: 10px; color: #F8FAFC; font-weight: 600; cursor: pointer;">Cancelar</button>
          <button type="submit" style="height: 42px; padding: 0 20px; background: #EF4444; border: none; border-radius: 10px; color: #FFFFFF; font-weight: 600; cursor: pointer;">Sim, Excluir</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts JS para Controle de Interatividade -->
  <script src="<?php echo asset('js/auth.js'); ?>"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const modalForm = document.getElementById('modal-turma-form');
      const modalFormTitle = document.getElementById('modal-turma-title');
      const modalFormAction = document.getElementById('turma-form-action');
      const modalFormId = document.getElementById('turma-form-id');
      const modalFormNameInput = document.getElementById('nome_turma');
      const modalFormBtnSubmit = document.getElementById('modal-turma-btn-submit');

      const modalExcluir = document.getElementById('modal-excluir-turma');
      const deleteTurmaIdInput = document.getElementById('delete-turma-id');
      const deleteTurmaNameSpan = document.getElementById('delete-turma-name');

      // 1. Abrir Modal de Nova Turma
      const btnNovaTurma = document.getElementById('btn-open-nova-turma');
      if (btnNovaTurma) {
        btnNovaTurma.addEventListener('click', function() {
          modalFormTitle.textContent = 'Nova Turma';
          modalFormAction.value = 'criar';
          modalFormId.value = '';
          modalFormNameInput.value = '';
          modalFormBtnSubmit.textContent = 'Criar Turma';
          modalForm.classList.add('active');
        });
      }

      // 2. Abrir Modal de Editar Turma
      document.querySelectorAll('.btn-edit-turma').forEach(function(btn) {
        btn.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const nome = this.getAttribute('data-nome');
          
          modalFormTitle.textContent = 'Editar Turma';
          modalFormAction.value = 'editar';
          modalFormId.value = id;
          modalFormNameInput.value = nome;
          modalFormBtnSubmit.textContent = 'Salvar Alterações';
          modalForm.classList.add('active');
        });
      });

      // 3. Abrir Modal de Excluir Turma
      document.querySelectorAll('.btn-delete-turma').forEach(function(btn) {
        btn.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const nome = this.getAttribute('data-nome');

          deleteTurmaIdInput.value = id;
          deleteTurmaNameSpan.textContent = nome;
          modalExcluir.classList.add('active');
        });
      });

      // 4. Abrir Modal de Gerenciar Membros
      document.querySelectorAll('.btn-open-membros').forEach(function(btn) {
        btn.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const modalMembros = document.getElementById('modal-membros-' + id);
          if (modalMembros) {
            modalMembros.classList.add('active');
          }
        });
      });

      // Fechar Modais ao clicar nos botões '✕' ou 'Cancelar'
      document.querySelectorAll('.btn-close-modal').forEach(function(btn) {
        btn.addEventListener('click', function() {
          document.querySelectorAll('.modal-overlay').forEach(function(modal) {
            modal.classList.remove('active');
          });
        });
      });

      // Fechar Modais ao clicar no fundo escuro (overlay)
      document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
          if (e.target === this) {
            this.classList.remove('active');
          }
        });
      });

      // Reabrir Modal de Membros se houve submissão prévia (ex: ao adicionar/remover um membro)
      <?php if (!empty($openMembrosModalId)): ?>
        const autoOpenModal = document.getElementById('modal-membros-<?php echo (int)$openMembrosModalId; ?>');
        if (autoOpenModal) {
          autoOpenModal.classList.add('active');
        }
      <?php endif; ?>
    });
  </script>
</body>
</html>
