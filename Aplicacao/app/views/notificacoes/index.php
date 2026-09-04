<?php 
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * View Central de Notificações em Tempo Real (/notificacoes)
 */

$assetUrl = defined('ASSET_URL') ? ASSET_URL : 'public/'; 

// Determina o link da página inicial do perfil atual para a Sidebar
$inicioUrl = url('admin');
if ($perfilSlug === 'professor') {
    $inicioUrl = url('professor');
} elseif ($perfilSlug === 'aluno') {
    $inicioUrl = url('aluno');
} elseif ($perfilSlug === 'funcionario' || $perfilSlug === 'funcionário') {
    $inicioUrl = url('funcionario');
}

// Determina a classe de cor do avatar conforme o perfil
$avatarClass = 'avatar-red';
if ($perfilSlug === 'professor') {
    $avatarClass = 'avatar-blue';
} elseif ($perfilSlug === 'aluno') {
    $avatarClass = 'avatar-teal';
} elseif ($perfilSlug === 'funcionario' || $perfilSlug === 'funcionário') {
    $avatarClass = 'avatar-amber';
}

$canSendNotice = ($perfilSlug === 'administrador' || $perfilSlug === 'professor' || $perfilSlug === 'funcionario');
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle ?? 'Notificações — ÂNCORA'); ?></title>
  
  <!-- Prevenção contra piscamento (Flicker) de tema ao carregar -->
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
  
  <!-- Stylesheet do Painel Administrativo -->
  <link rel="stylesheet" href="<?php echo $assetUrl; ?>css/admin.css">
</head>
<body>

  <!-- Mobile Toggle Button -->
  <button id="admin-mobile-toggle" class="admin-mobile-toggle" aria-label="Abrir Menu">☰</button>

  <div class="admin-layout-wrapper">
    
    <!-- =========================================================================
         1. SIDEBAR LATERAL DINÂMICA
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

      <!-- Badge Perfil Dinâmico -->
      <div class="sidebar-role-badge <?php echo htmlspecialchars($perfilSlug); ?>">
        <span class="role-dot"></span>
        <span><?php echo htmlspecialchars($userRole); ?></span>
      </div>

      <!-- Grupo 1: PRINCIPAL -->
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">PRINCIPAL</span>
        
        <a href="<?php echo $inicioUrl; ?>" class="sidebar-menu-link">
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
        
        <a href="<?php echo url('notificacoes'); ?>" class="sidebar-menu-link active">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span>Notificações</span>
          <?php if ($unreadCount > 0): ?>
            <span class="sidebar-unread-badge" id="sidebar-unread-count"><?php echo $unreadCount; ?></span>
          <?php endif; ?>
        </a>
        
        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>Mensagens</span>
        </a>
      </div>

      <!-- Grupo 2: GESTÃO (Conforme o Perfil) -->
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">GESTÃO</span>
        
        <?php if ($perfilSlug === 'administrador'): ?>
          <a href="<?php echo url('admin/turmas'); ?>" class="sidebar-menu-link">
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
        <?php elseif ($perfilSlug === 'professor'): ?>
          <a href="#" class="sidebar-menu-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            <span>Turmas</span>
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
          <div class="user-avatar-circle <?php echo $avatarClass; ?>"><?php echo htmlspecialchars($userInitials); ?></div>
          <div class="user-info-text">
            <span class="user-name-title"><?php echo htmlspecialchars($userName); ?></span>
            <span class="user-role-sub"><?php echo htmlspecialchars($userRole); ?></span>
          </div>
        </div>
        <a href="<?php echo url('logout'); ?>" class="logout-icon-btn" title="Sair do sistema">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>

    </aside>

    <!-- =========================================================================
         2. CONTEÚDO PRINCIPAL (TELA DE NOTIFICAÇÕES)
         ========================================================================= -->
    <main class="admin-main-content">
      
      <!-- Header Superior com Ações -->
      <div class="notificacoes-header-row">
        <div>
          <h1 class="header-main-title">Notificações</h1>
          <div class="header-main-sub" id="notificacoes-header-sub">
            <?php if ($unreadCount > 0): ?>
              <?php echo $unreadCount; ?> não lida<?php echo $unreadCount > 1 ? 's' : ''; ?>
            <?php else: ?>
              Você está em dia!
            <?php endif; ?>
          </div>
        </div>

        <div class="notificacoes-top-actions">
          <form action="<?php echo url('notificacoes'); ?>" method="POST" style="display: inline;">
            <input type="hidden" name="action" value="marcar_todas_lidas">
            <button type="submit" class="btn-notif-action" id="btn-marcar-todas-lidas">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
              <span>Marcar todas</span>
            </button>
          </form>

          <?php if ($canSendNotice): ?>
            <button type="button" class="btn-primary-blue" id="btn-open-modal-aviso">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              <span>Enviar Aviso</span>
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- Alertas de Feedback Flash -->
      <?php if (!empty($flashSuccess)): ?>
        <div class="settings-alert-box success">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          <span><?php echo htmlspecialchars($flashSuccess); ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($flashError)): ?>
        <div class="settings-alert-box error">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span><?php echo htmlspecialchars($flashError); ?></span>
        </div>
      <?php endif; ?>

      <!-- Navegação por Abas (Central / Configurações) -->
      <div class="notif-tabs-bar">
        <button type="button" class="notif-tab-btn active" data-tab="tab-central">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span>Central</span>
          <span class="notif-tab-badge" id="tab-unread-badge"><?php echo $unreadCount; ?></span>
        </button>

        <button type="button" class="notif-tab-btn" data-tab="tab-configuracoes">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          <span>Configurações</span>
        </button>
      </div>

      <!-- =====================================================================
           ABA 1: CENTRAL DE NOTIFICAÇÕES (LISTAGEM DINÂMICA)
           ===================================================================== -->
      <div id="tab-central" class="notif-tab-content active">
        
        <div class="notif-cards-wrapper" id="notif-list-container">
          
          <?php if (empty($notificacoes)): ?>
            <div class="notif-empty-box">
              <div class="notif-empty-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              </div>
              <h3 class="notif-empty-title">Nenhuma notificação no momento</h3>
              <p class="notif-empty-sub">Você receberá avisos, lembretes de tarefas e novidades por aqui.</p>
            </div>
          <?php else: ?>
            <?php foreach ($notificacoes as $n): ?>
              <?php 
                $isUnread = ((int)$n['lida'] === 0);
                $tipoVal = $n['tipo'] ?? 'Informativo';
                
                // Mapeamento de Cores e Ícones por Tipo
                $typeBadgeClass = 'info';
                $typeLabel = 'Info';
                $iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';

                if ($tipoVal === 'Alerta') {
                    $typeBadgeClass = 'alerta';
                    $typeLabel = 'Alerta';
                    $iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
                } elseif ($tipoVal === 'Sucesso') {
                    $typeBadgeClass = 'sucesso';
                    $typeLabel = 'Sucesso';
                    $iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
                } elseif ($tipoVal === 'Erro') {
                    $typeBadgeClass = 'erro';
                    $typeLabel = 'Erro';
                    $iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
                }
              ?>
              
              <div class="notif-card-item <?php echo $isUnread ? 'unread' : ''; ?>" data-id="<?php echo $n['id']; ?>">
                <div class="notif-item-left">
                  <div class="notif-type-icon <?php echo $typeBadgeClass; ?>">
                    <?php echo $iconSvg; ?>
                  </div>
                  <div class="notif-item-content">
                    <div class="notif-item-title-row">
                      <span class="notif-item-title"><?php echo htmlspecialchars($n['titulo']); ?></span>
                      <span class="notif-type-pill <?php echo $typeBadgeClass; ?>"><?php echo $typeLabel; ?></span>
                      <?php if ($isUnread): ?>
                        <span class="notif-unread-dot" title="Não lida"></span>
                      <?php endif; ?>
                    </div>
                    <div class="notif-item-body">
                      <?php echo nl2br(htmlspecialchars($n['mensagem'])); ?>
                    </div>
                    <div class="notif-item-date">
                      <?php echo htmlspecialchars($n['data_formatada']); ?>
                    </div>
                  </div>
                </div>

                <div class="notif-item-actions">
                  <?php if ($isUnread): ?>
                    <button type="button" class="btn-icon-notif btn-mark-read" data-id="<?php echo $n['id']; ?>" title="Marcar como lida">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                  <?php endif; ?>

                  <button type="button" class="btn-icon-notif danger btn-delete-notif" data-id="<?php echo $n['id']; ?>" title="Excluir notificação">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>

      </div>

      <!-- =====================================================================
           ABA 2: CONFIGURAÇÕES DE NOTIFICAÇÕES (FIEL À IMAGEM 3)
           ===================================================================== -->
      <div id="tab-configuracoes" class="notif-tab-content">
        <div class="notif-settings-container">
          
          <!-- Card 1: Notificações ativas -->
          <div class="settings-card" style="margin-bottom: 20px;">
            <div class="settings-theme-row">
              <div class="settings-theme-left">
                <div>
                  <div class="settings-theme-title">Notificações ativas</div>
                  <div class="settings-theme-desc">Ativar ou desativar todas as notificações</div>
                </div>
              </div>
              <label class="theme-switch-control" aria-label="Alternar Notificações ativas">
                <input type="checkbox" id="cfg-notif-master" checked>
                <span class="theme-switch-slider"></span>
              </label>
            </div>
          </div>

          <!-- Card 2: Tipos de Notificação -->
          <div class="settings-card">
            <h2 class="settings-card-title" style="margin-bottom: 18px; font-size: 0.8rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--admin-text-muted);">
              TIPOS DE NOTIFICAÇÃO
            </h2>

            <div class="settings-security-list">
              
              <!-- Tarefas Acadêmicas -->
              <div class="settings-security-row">
                <div class="settings-info-left">
                  <div class="settings-info-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                  </div>
                  <div>
                    <div class="settings-theme-title">Tarefas acadêmicas</div>
                    <div class="settings-theme-desc">Novas tarefas, prazos e avaliações</div>
                  </div>
                </div>
                <label class="theme-switch-control">
                  <input type="checkbox" id="cfg-type-tarefas" checked>
                  <span class="theme-switch-slider"></span>
                </label>
              </div>

              <!-- Eventos Escolares -->
              <div class="settings-security-row">
                <div class="settings-info-left">
                  <div class="settings-info-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  </div>
                  <div>
                    <div class="settings-theme-title">Eventos escolares</div>
                    <div class="settings-theme-desc">Novos eventos, inscrições e lembretes</div>
                  </div>
                </div>
                <label class="theme-switch-control">
                  <input type="checkbox" id="cfg-type-eventos" checked>
                  <span class="theme-switch-slider"></span>
                </label>
              </div>

              <!-- Reservas de Espaços -->
              <div class="settings-security-row">
                <div class="settings-info-left">
                  <div class="settings-info-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  </div>
                  <div>
                    <div class="settings-theme-title">Reservas de espaços</div>
                    <div class="settings-theme-desc">Confirmações e cancelamentos de reservas</div>
                  </div>
                </div>
                <label class="theme-switch-control">
                  <input type="checkbox" id="cfg-type-reservas" checked>
                  <span class="theme-switch-slider"></span>
                </label>
              </div>

              <!-- Avisos Gerais -->
              <div class="settings-security-row">
                <div class="settings-info-left">
                  <div class="settings-info-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12.01" y2="16"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
                  </div>
                  <div>
                    <div class="settings-theme-title">Avisos gerais</div>
                    <div class="settings-theme-desc">Comunicados da administração</div>
                  </div>
                </div>
                <label class="theme-switch-control">
                  <input type="checkbox" id="cfg-type-avisos" checked>
                  <span class="theme-switch-slider"></span>
                </label>
              </div>

            </div>
          </div>

          <div style="text-align: center; font-size: 0.775rem; color: var(--admin-text-muted); margin-top: 16px;">
            As configurações são salvas localmente neste dispositivo.
          </div>

        </div>
      </div>

    </main>

  </div>

  <!-- ===========================================================================
       MODAL: ENVIAR AVISO / NOTIFICAÇÃO (FIEL ÀS IMAGENS 1, 4 E 5)
       =========================================================================== -->
  <?php if ($canSendNotice): ?>
    <div id="modal-enviar-aviso" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h3 class="modal-title">Enviar Aviso / Notificação</h3>
          <button type="button" class="modal-close-btn" data-close-modal="modal-enviar-aviso">&times;</button>
        </div>

        <form action="<?php echo url('notificacoes'); ?>" method="POST">
          <input type="hidden" name="action" value="criar_aviso">

          <div class="form-group" style="margin-bottom: 16px;">
            <label for="aviso_titulo" class="form-label">Título *</label>
            <input type="text" id="aviso_titulo" name="titulo" class="form-control" placeholder="Ex: Manutenção programada" required>
          </div>

          <div class="form-group" style="margin-bottom: 16px;">
            <label for="aviso_mensagem" class="form-label">Mensagem *</label>
            <textarea id="aviso_mensagem" name="mensagem" class="form-control" rows="4" placeholder="Escreva o aviso..." required style="height: auto; padding: 12px; resize: vertical;"></textarea>
          </div>

          <div class="form-grid-2col" style="margin-bottom: 24px;">
            <div class="form-group">
              <label for="aviso_tipo" class="form-label">Tipo</label>
              <select id="aviso_tipo" name="tipo" class="form-control">
                <option value="Informativo" selected>💭 Informativo</option>
                <option value="Alerta">⚠️ Alerta</option>
                <option value="Sucesso">✅ Sucesso</option>
                <option value="Erro">❌ Erro</option>
              </select>
            </div>

            <div class="form-group">
              <label for="aviso_destinatario" class="form-label">Destinatário</label>
              <select id="aviso_destinatario" name="destinatario" class="form-control">
                <option value="Todos" selected>Todos</option>
                <option value="Alunos">Alunos</option>
                <option value="Professores">Professores</option>
                <option value="Funcionários">Funcionários</option>
                <option value="Administradores">Administradores</option>
              </select>
            </div>
          </div>

          <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn-modal-cancel" data-close-modal="modal-enviar-aviso">Cancelar</button>
            <button type="submit" class="btn-modal-save">Enviar Notificação</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <!-- Scripts JavaScript -->
  <script src="<?php echo $assetUrl; ?>js/admin.js"></script>
</body>
</html>
