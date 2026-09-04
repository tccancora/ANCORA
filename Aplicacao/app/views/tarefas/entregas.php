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
  <title><?php echo isset($pageTitle) ? $pageTitle : 'Entregas — ÂNCORA'; ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="<?php echo $assetUrl; ?>css/admin.css">
  
  <script>
    (function() {
      const savedTheme = localStorage.getItem('ancora_theme') || 'dark';
      document.documentElement.setAttribute('data-theme', savedTheme);
    })();
  </script>

  <style>
    /* Estilos Refinados do Painel de Entregas e Correção */
    .metrics-summary-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 18px;
      margin-bottom: 26px;
    }

    .metric-card-box {
      background: var(--admin-card-bg);
      border: 1px solid var(--admin-card-border);
      border-radius: 16px;
      padding: 20px 24px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.2s ease;
      position: relative;
      overflow: hidden;
    }

    .metric-card-box:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .metric-card-box.total { border-top: 3px solid #2563EB; }
    .metric-card-box.entregues { border-top: 3px solid #10B981; }
    .metric-card-box.pendentes { border-top: 3px solid #F59E0B; }
    .metric-card-box.corrigidas { border-top: 3px solid #38BDF8; }

    .metric-card-number {
      font-size: 2.2rem;
      font-weight: 800;
      color: var(--admin-text-heading);
      line-height: 1;
      letter-spacing: -0.5px;
    }

    .metric-card-label {
      font-size: 0.8rem;
      color: var(--admin-text-muted);
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .submissions-table-card {
      background: var(--admin-card-bg);
      border: 1px solid var(--admin-card-border);
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .submissions-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
    }

    .submissions-table th {
      background: var(--admin-item-row-bg, #0B1120);
      padding: 16px 22px;
      font-size: 0.8rem;
      font-weight: 700;
      color: var(--admin-text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 1.5px solid var(--admin-card-border);
    }

    .submissions-table td {
      padding: 18px 22px;
      font-size: 0.9rem;
      color: var(--admin-text-heading);
      border-bottom: 1px solid var(--admin-item-row-border);
      vertical-align: middle;
    }

    .submissions-table tr:last-child td {
      border-bottom: none;
    }

    .submissions-table tr:hover td {
      background: rgba(37, 99, 235, 0.04);
    }

    /* Badges de Status Refinados */
    .tarefa-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 5px 12px;
      border-radius: 20px;
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

    .tarefa-badge.devolvida {
      background: rgba(56, 189, 248, 0.15);
      color: #38BDF8;
      border: 1px solid rgba(56, 189, 248, 0.3);
    }

    .tarefa-badge.corrigida {
      background: rgba(168, 85, 247, 0.15);
      color: #A855F7;
      border: 1px solid rgba(168, 85, 247, 0.3);
    }

    /* Botões Modernos */
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
      border: none;
    }

    .btn-card-action.primary {
      background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
      color: #FFFFFF;
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

    .btn-card-action.success {
      background: linear-gradient(135deg, #10B981 0%, #059669 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
    }

    .btn-card-action.success:hover {
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      box-shadow: 0 6px 16px rgba(16, 185, 129, 0.5);
      transform: translateY(-1px);
    }

    /* Modal de Correção Refinado */
    .form-group-row {
      margin-bottom: 18px;
    }

    .form-label-title {
      display: block;
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--admin-text-heading);
      margin-bottom: 8px;
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

    textarea.form-input-control {
      resize: vertical;
      line-height: 1.6;
      min-height: 90px;
    }

    .correction-question-box {
      background: rgba(15, 23, 42, 0.45);
      border: 1px solid var(--admin-card-border);
      border-left: 4px solid #38BDF8;
      border-radius: 14px;
      padding: 18px;
      margin-bottom: 16px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    }

    .material-item-download {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 16px;
      background: var(--admin-item-row-bg, #0B1120);
      border: 1px solid var(--admin-card-border);
      border-radius: 12px;
      margin-bottom: 8px;
      color: var(--admin-text-heading);
      text-decoration: none;
      transition: all 0.2s ease;
    }

    .material-item-download:hover {
      border-color: #38BDF8;
      background: rgba(56, 189, 248, 0.08);
      color: #38BDF8;
      transform: translateY(-1px);
    }
  </style>
</head>
<body>

  <!-- Mobile Toggle Button -->
  <button id="admin-mobile-toggle" class="admin-mobile-toggle" aria-label="Abrir Menu">☰</button>

  <div class="admin-layout-wrapper">

    <!-- SIDEBAR -->
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
      <div class="sidebar-role-badge <?php echo htmlspecialchars($perfilSlug ?? 'professor'); ?>">
        <span class="role-dot"></span>
        <span><?php echo htmlspecialchars($userRoleTitle ?? 'Professor'); ?></span>
      </div>

      <!-- Grupo 1: PRINCIPAL -->
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">PRINCIPAL</span>
        <a href="<?php echo $inicioUrl ?? url('professor'); ?>" class="sidebar-menu-link">
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
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">GESTÃO</span>
        <a href="<?php echo ($user['perfil_id'] == 1 ? url('admin/turmas') : '#'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          <span>Turmas</span>
        </a>
        <?php if ($user['perfil_id'] == 1): ?>
        <a href="<?php echo url('usuarios'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>Usuários</span>
        </a>
        <?php endif; ?>
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
          <div class="user-avatar-circle <?php echo htmlspecialchars($perfilSlug ?? 'professor'); ?>"><?php echo htmlspecialchars($userInitials); ?></div>
          <div class="user-info-text">
            <span class="user-name-title"><?php echo htmlspecialchars($userName); ?></span>
            <span class="user-role-sub"><?php echo htmlspecialchars($userRoleTitle ?? 'Professor'); ?></span>
          </div>
        </div>
        <a href="<?php echo url('logout'); ?>" class="logout-icon-btn" title="Sair do sistema">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="admin-main-content">
      
      <!-- Topbar Header -->
      <header class="main-header-bar" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div>
          <div style="margin-bottom: 8px;">
            <a href="<?php echo url('tarefas'); ?>" style="display: inline-flex; align-items: center; gap: 6px; color: var(--admin-text-muted); text-decoration: none; font-size: 0.85rem; font-weight: 600;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
              Voltar para Tarefas
            </a>
          </div>
          <h1 class="page-title" style="font-size: 1.8rem; font-weight: 800; margin: 0; color: var(--admin-text-heading);">
            Entregas: <?php echo htmlspecialchars($tarefa['titulo']); ?>
          </h1>
          <p class="page-subtitle" style="font-size: 0.9rem; color: var(--admin-text-muted); margin-top: 4px;">
            Prazo: <?php echo htmlspecialchars($tarefa['prazo_formatado']); ?>
            <?php if (!empty($tarefa['disciplina'])): ?> • <?php echo htmlspecialchars($tarefa['disciplina']); ?><?php endif; ?>
          </p>
        </div>
      </header>

      <!-- Mensagens Flash -->
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

      <!-- Cards de Métricas de Entregas -->
      <div class="metrics-summary-grid">
        <div class="metric-card-box total">
          <span class="metric-card-number"><?php echo $resumoEntregas['total_destinatarios']; ?></span>
          <span class="metric-card-label">Destinatários</span>
        </div>

        <div class="metric-card-box entregues">
          <span class="metric-card-number" style="color: #10B981;"><?php echo $resumoEntregas['total_entregues']; ?></span>
          <span class="metric-card-label">Entregues</span>
        </div>

        <div class="metric-card-box pendentes">
          <span class="metric-card-number" style="color: #F59E0B;"><?php echo $resumoEntregas['total_nao_entregues']; ?></span>
          <span class="metric-card-label">Pendentes</span>
        </div>

        <div class="metric-card-box corrigidas">
          <span class="metric-card-number" style="color: #38BDF8;"><?php echo $resumoEntregas['total_devolvidas']; ?></span>
          <span class="metric-card-label">Devolvidas</span>
        </div>

        <div class="metric-card-box corrigidas">
          <span class="metric-card-number" style="color: #A855F7;">
            <?php echo $resumoEntregas['media_notas'] !== null ? number_format($resumoEntregas['media_notas'], 1, ',', '.') : '—'; ?>
          </span>
          <span class="metric-card-label">Média de Notas</span>
        </div>
      </div>

      <!-- Tabela com Lista de Alunos e Status da Entrega -->
      <div class="submissions-table-card">
        <table class="submissions-table">
          <thead>
            <tr>
              <th>Aluno</th>
              <th>Turma</th>
              <th>Status</th>
              <th>Entregue Em</th>
              <th>Nota</th>
              <th style="text-align: right;">Ação</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($listaAlunos)): ?>
              <tr>
                <td colspan="6" style="text-align: center; padding: 32px; color: var(--admin-text-muted);">
                  Nenhum aluno destinatário encontrado para esta tarefa.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($listaAlunos as $aluno): ?>
                <?php 
                  $status = $aluno['status'];
                  $statusClass = 'pendente';
                  $statusLabel = 'Não entregue';

                  if ($status === 'entregue') {
                    $statusClass = 'entregue';
                    $statusLabel = 'Entregue (Aguardando Correção)';
                  } elseif ($status === 'corrigida') {
                    $statusClass = 'corrigida';
                    $statusLabel = 'Corrigida (Rascunho)';
                  } elseif ($status === 'devolvida') {
                    $statusClass = 'devolvida';
                    $statusLabel = 'Devolvida ao Aluno';
                  }
                ?>
                <tr>
                  <td>
                    <strong><?php echo htmlspecialchars($aluno['aluno_nome']); ?></strong><br>
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted);"><?php echo htmlspecialchars($aluno['aluno_email']); ?></span>
                  </td>
                  <td><?php echo htmlspecialchars($aluno['turma_nome']); ?></td>
                  <td>
                    <span class="tarefa-badge <?php echo $statusClass; ?>">
                      <?php echo htmlspecialchars($statusLabel); ?>
                    </span>
                  </td>
                  <td>
                    <?php if (!empty($aluno['entregue_em'])): ?>
                      <?php echo date('d/m/Y \à\s H:i', strtotime($aluno['entregue_em'])); ?>
                    <?php else: ?>
                      <span style="color: var(--admin-text-muted);">—</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($aluno['nota'] !== null): ?>
                      <strong style="color: #10B981;"><?php echo number_format((float)$aluno['nota'], 1, ',', '.'); ?></strong> / 10,0
                    <?php else: ?>
                      <span style="color: var(--admin-text-muted);">—</span>
                    <?php endif; ?>
                  </td>
                  <td style="text-align: right;">
                    <?php if ($aluno['tem_entrega']): ?>
                      <button 
                        type="button" 
                        class="btn-card-action primary btn-abrir-correcao"
                        data-aluno="<?php echo htmlspecialchars(json_encode($aluno)); ?>"
                      >
                        <?php echo ($status === 'devolvida') ? 'Visualizar / Editar' : 'Corrigir e Devolver'; ?>
                      </button>
                    <?php else: ?>
                      <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Aguardando envio</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </main>
  </div>

  <!-- MODAL DE CORREÇÃO E DEVOLUÇÃO -->
  <div class="modal-overlay" id="modal-correcao" style="display:none;">
    <div class="modal-container" style="max-width: 700px; max-height: 90vh; overflow-y: auto;">
      <div class="modal-header">
        <h3 class="modal-title">Correção de Atividade</h3>
        <button type="button" class="modal-close-btn" id="btn-close-modal-correcao">&times;</button>
      </div>

      <div style="background: var(--admin-item-row-bg); border-radius: 10px; padding: 12px 16px; margin-bottom: 18px;">
        <div style="font-size: 1rem; font-weight: 700; color: var(--admin-text-heading);" id="correcao-aluno-nome">Aluno</div>
        <div style="font-size: 0.85rem; color: var(--admin-text-muted);" id="correcao-entrega-data">Entregue em...</div>
      </div>

      <form action="<?php echo url('tarefas/corrigir'); ?>" method="POST" id="form-salvar-correcao">
        <input type="hidden" name="tarefa_id" value="<?php echo (int)$tarefa['id']; ?>">
        <input type="hidden" name="entrega_id" id="input-correcao-entrega-id" value="">
        <input type="hidden" name="action" id="input-correcao-action" value="devolver">

        <!-- Arquivos Anexados pelo Aluno -->
        <div id="correcao-arquivos-container" style="margin-bottom: 18px;">
          <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--admin-text-heading); margin-bottom: 8px;">Arquivos Anexados pelo Aluno</h4>
          <div id="correcao-arquivos-list"></div>
        </div>

        <!-- Respostas do Questionário -->
        <div id="correcao-respostas-container" style="margin-bottom: 18px;">
          <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--admin-text-heading); margin-bottom: 8px;">Respostas do Questionário</h4>
          <div id="correcao-respostas-list"></div>
        </div>

        <!-- Nota Final -->
        <div class="form-group-row">
          <label class="form-label-title">Nota Final (de 0,0 a 10,0) *</label>
          <input type="number" step="0.1" min="0" max="10" name="nota_final" id="input-correcao-nota-final" class="form-input-control" placeholder="Ex: 8.5" style="max-width: 160px;">
        </div>

        <!-- Feedback Geral -->
        <div class="form-group-row">
          <label class="form-label-title">Feedback Geral para o Aluno</label>
          <textarea name="feedback_geral" id="input-correcao-feedback" class="form-input-control" rows="4" placeholder="Escreva observações e orientações pedagógicas para o aluno..."></textarea>
        </div>

        <!-- Ações do Modal -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 22px; border-top: 1px solid var(--admin-item-row-border); padding-top: 18px; flex-wrap: wrap;">
          <button type="button" class="btn-card-action secondary" id="btn-cancel-modal-correcao">Fechar</button>
          <button type="button" class="btn-card-action secondary" id="btn-salvar-rascunho-correcao" style="border-color: rgba(56, 189, 248, 0.4); color: #38BDF8;">Salvar Rascunho</button>
          <button type="button" class="btn-card-action success" id="btn-devolver-atividade">✓ Devolver Atividade ao Aluno</button>
        </div>

      </form>
    </div>
  </div>

  <script src="<?php echo $assetUrl; ?>js/admin.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const modalCorrecao = document.getElementById('modal-correcao');
      const btnCloseCorrecao = document.getElementById('btn-close-modal-correcao');
      const btnCancelCorrecao = document.getElementById('btn-cancel-modal-correcao');

      const elAlunoNome = document.getElementById('correcao-aluno-nome');
      const elEntregaData = document.getElementById('correcao-entrega-data');
      const inputEntregaId = document.getElementById('input-correcao-entrega-id');
      const inputNotaFinal = document.getElementById('input-correcao-nota-final');
      const inputFeedback = document.getElementById('input-correcao-feedback');
      const inputAction = document.getElementById('input-correcao-action');
      const formCorrecao = document.getElementById('form-salvar-correcao');

      const arquivosList = document.getElementById('correcao-arquivos-list');
      const respostasList = document.getElementById('correcao-respostas-list');

      document.querySelectorAll('.btn-abrir-correcao').forEach(btn => {
        btn.addEventListener('click', function() {
          const data = JSON.parse(this.getAttribute('data-aluno'));
          
          inputEntregaId.value = data.entrega_id;
          elAlunoNome.textContent = `${data.aluno_nome} (${data.turma_nome})`;
          elEntregaData.textContent = `Entregue em: ${data.entregue_em || 'N/A'}`;
          inputNotaFinal.value = data.nota !== null ? data.nota : '';
          inputFeedback.value = data.feedback_geral || '';

          // Renderizar arquivos
          arquivosList.innerHTML = '';
          if (data.arquivos && data.arquivos.length > 0) {
            data.arquivos.forEach(arq => {
              const a = document.createElement('a');
              a.className = 'material-item-download';
              a.href = `<?php echo url('tarefas/download-entrega'); ?>&id=${arq.id}`;
              a.innerHTML = `
                <span>📄 ${escapeHtml(arq.nome_original)}</span>
                <span style="font-size: 0.75rem; color: #38BDF8;">Baixar arquivo</span>
              `;
              arquivosList.appendChild(a);
            });
          } else {
            arquivosList.innerHTML = '<span style="font-size: 0.85rem; color: var(--admin-text-muted);">Nenhum arquivo enviado.</span>';
          }

          // Renderizar respostas
          respostasList.innerHTML = '';
          if (data.respostas && data.respostas.length > 0) {
            data.respostas.forEach((r, idx) => {
              const div = document.createElement('div');
              div.className = 'correction-question-box';
              div.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                  <span style="font-size: 0.8rem; font-weight: 700; color: #38BDF8; background: rgba(56, 189, 248, 0.12); padding: 3px 10px; border-radius: 20px;">Questão ${idx + 1} • ${escapeHtml(r.questao_tipo)}</span>
                  <span style="font-size: 0.8rem; color: var(--admin-text-muted); font-weight: 600;">Máx: ${r.questao_pontos} pt(s)</span>
                </div>
                <div style="font-size: 0.925rem; font-weight: 600; color: var(--admin-text-heading); margin-bottom: 10px; line-height: 1.5;">
                  ${escapeHtml(r.enunciado)}
                </div>
                <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(148, 163, 184, 0.15); padding: 12px 14px; border-radius: 10px; font-size: 0.9rem; color: #F8FAFC; margin-bottom: 12px; line-height: 1.5;">
                  <strong style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Resposta do Aluno:</strong>
                  ${escapeHtml(r.resposta_texto || r.resposta_selecao_json || 'Sem resposta')}
                </div>
                <div style="display: grid; grid-template-columns: 120px 1fr; gap: 12px; align-items: center;">
                  <div>
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--admin-text-muted); display: block; margin-bottom: 4px;">Nota Atribuída:</label>
                    <input type="number" step="0.5" min="0" max="${r.questao_pontos}" name="notas_questoes[${r.questao_id}]" class="form-input-control" value="${r.pontos_obtidos !== null ? r.pontos_obtidos : ''}" style="width: 100%;">
                  </div>
                  <div>
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--admin-text-muted); display: block; margin-bottom: 4px;">Comentário do Professor:</label>
                    <input type="text" name="comentarios_questoes[${r.questao_id}]" class="form-input-control" placeholder="Feedback específico desta questão..." value="${escapeHtml(r.comentario_professor || '')}">
                  </div>
                </div>
              `;
              respostasList.appendChild(div);
            });
          } else {
            respostasList.innerHTML = '<span style="font-size: 0.85rem; color: var(--admin-text-muted);">Esta atividade não possuía questionário.</span>';
          }

          modalCorrecao.classList.add('active');
          modalCorrecao.style.display = 'flex';
        });
      });

      if (btnCloseCorrecao) btnCloseCorrecao.addEventListener('click', () => { 
        modalCorrecao.classList.remove('active'); 
        modalCorrecao.style.display = 'none'; 
      });
      if (btnCancelCorrecao) btnCancelCorrecao.addEventListener('click', () => { 
        modalCorrecao.classList.remove('active'); 
        modalCorrecao.style.display = 'none'; 
      });

      document.getElementById('btn-salvar-rascunho-correcao').addEventListener('click', function() {
        inputAction.value = 'corrigir';
        formCorrecao.submit();
      });

      document.getElementById('btn-devolver-atividade').addEventListener('click', function() {
        if (confirm('Deseja realmente devolver esta atividade com nota e feedback para o aluno? Ele receberá uma notificação.')) {
          inputAction.value = 'devolver';
          formCorrecao.submit();
        }
      });

      function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
      }
    });
  </script>

</body>
</html>
