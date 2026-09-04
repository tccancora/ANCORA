<?php 
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * View da Tela de Configurações (/configuracoes)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Interface gráfica universal para consulta e edição de perfil, código da instituição,
 * segurança da conta (e-mail e senha) e preferências visuais de tema.
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
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle ?? 'Configurações — ÂNCORA'); ?></title>
  
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
        <span><?php echo htmlspecialchars($userRoleTitle); ?></span>
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
        
        <a href="<?php echo url('configuracoes'); ?>" class="sidebar-menu-link active">
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
            <span class="user-role-sub"><?php echo htmlspecialchars($userRoleTitle); ?></span>
          </div>
        </div>
        <a href="<?php echo url('logout'); ?>" class="logout-icon-btn" title="Sair do sistema">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>

    </aside>

    <!-- =========================================================================
         2. CONTEÚDO PRINCIPAL (TELA DE CONFIGURAÇÕES)
         ========================================================================= -->
    <main class="admin-main-content">
      
      <!-- Top Header Row -->
      <div class="admin-header-row" style="margin-bottom: 24px;">
        <div>
          <h1 class="header-main-title">Configurações</h1>
          <div class="header-main-sub">Perfil e preferências do sistema</div>
        </div>
      </div>

      <!-- Alertas de Feedback Flash (Sucesso / Erro) -->
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

      <!-- Container Central das Configurações (Fiel às Referências Visuais) -->
      <div class="settings-container">
        
        <!-- =====================================================================
             CARD 1: MEU PERFIL
             ===================================================================== -->
        <div class="settings-card">
          <div class="settings-card-header">
            <h2 class="settings-card-title">Meu Perfil</h2>
            <button type="button" class="btn-settings-edit" id="btn-open-edit-perfil" title="Editar Perfil">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
          </div>

          <!-- Header do Perfil (Avatar + Nome + Cargo) -->
          <div class="profile-header-wrap">
            <div class="profile-avatar-box <?php echo $avatarClass; ?>">
              <span><?php echo htmlspecialchars($userInitials); ?></span>
              <div class="avatar-edit-badge" title="Avatar">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
              </div>
            </div>
            <div class="profile-header-info">
              <div class="profile-name-title"><?php echo htmlspecialchars($userPrefix . $userName); ?></div>
              <span class="badge-role <?php echo htmlspecialchars($perfilSlug); ?>"><?php echo htmlspecialchars($userRoleTitle); ?></span>
            </div>
          </div>

          <!-- Linhas de Dados do Perfil -->
          <div class="settings-info-list">
            
            <!-- Email -->
            <div class="settings-info-row">
              <div class="settings-info-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              </div>
              <div class="settings-info-content">
                <div class="settings-info-label">Email</div>
                <div class="settings-info-value"><?php echo htmlspecialchars($userEmail); ?></div>
              </div>
            </div>

            <!-- Departamento -->
            <div class="settings-info-row">
              <div class="settings-info-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
              </div>
              <div class="settings-info-content">
                <div class="settings-info-label">Departamento</div>
                <div class="settings-info-value"><?php echo htmlspecialchars($userDepartment); ?></div>
              </div>
            </div>

            <!-- Nível de Acesso -->
            <div class="settings-info-row">
              <div class="settings-info-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              </div>
              <div class="settings-info-content">
                <div class="settings-info-label">Nível de Acesso</div>
                <div class="settings-info-value"><?php echo htmlspecialchars($userAccessLevel); ?></div>
              </div>
            </div>

          </div>
        </div>

        <!-- =====================================================================
             CARD 2: CÓDIGO DA INSTITUIÇÃO
             ===================================================================== -->
        <div class="settings-card">
          <div class="instituicao-card-header">
            <div class="instituicao-icon-badge">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div class="instituicao-header-text">
              <h2 class="settings-card-title">Código da Instituição</h2>
              <p class="settings-card-sub">Compartilhe este código internamente para identificar a instituição. Ele não é necessário para login.</p>
            </div>
          </div>

          <!-- Caixa de Exibição do Código com Botão Copiar -->
          <div class="instituicao-code-box">
            <span class="instituicao-code-text"># <?php echo htmlspecialchars($instituicaoCodigo); ?></span>
            <button type="button" class="btn-copy-code" id="btn-copy-instituicao" data-code="<?php echo htmlspecialchars($instituicaoCodigo); ?>" title="Copiar código">
              <svg id="copy-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              <span class="copy-feedback-text" id="copy-feedback">Copiado!</span>
            </button>
          </div>

          <div class="instituicao-footer-name">
            <span>Instituição: </span><strong><?php echo htmlspecialchars($instituicaoNome); ?></strong>
          </div>
        </div>

        <!-- =====================================================================
             CARD 3: SEGURANÇA DA CONTA
             ===================================================================== -->
        <div class="settings-card">
          <h2 class="settings-card-title" style="margin-bottom: 16px;">Segurança da Conta</h2>

          <div class="settings-security-list">
            
            <!-- Linha E-mail com Botão de Edição -->
            <div class="settings-security-row">
              <div class="settings-info-left">
                <div class="settings-info-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <div class="settings-info-content">
                  <div class="settings-info-label">Email</div>
                  <div class="settings-info-value"><?php echo htmlspecialchars($userEmail); ?></div>
                </div>
              </div>
              <button type="button" class="btn-security-edit" id="btn-open-edit-email" title="Alterar Email">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
            </div>

            <!-- Linha Senha com Botão de Edição -->
            <div class="settings-security-row">
              <div class="settings-info-left">
                <div class="settings-info-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <div class="settings-info-content">
                  <div class="settings-info-label">Senha</div>
                  <div class="settings-info-value">••••••••</div>
                </div>
              </div>
              <button type="button" class="btn-security-edit" id="btn-open-edit-senha" title="Alterar Senha">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
            </div>

          </div>
        </div>

        <!-- =====================================================================
             CARD 4: APARÊNCIA (DARK / LIGHT THEME TOGGLE)
             ===================================================================== -->
        <div class="settings-card">
          <h2 class="settings-card-title" style="margin-bottom: 16px;">Aparência</h2>

          <div class="settings-theme-row">
            <div class="settings-theme-left">
              <div class="settings-info-icon" id="settings-theme-icon-box">
                <svg id="settings-theme-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
              </div>
              <div>
                <div class="settings-theme-title" id="settings-theme-label">Tema Escuro ativo</div>
                <div class="settings-theme-desc">Clique para alternar o tema da interface</div>
              </div>
            </div>

            <!-- Toggle Switch Interativo -->
            <label class="theme-switch-control" aria-label="Alternar Tema Claro/Escuro">
              <input type="checkbox" id="settings-theme-checkbox">
              <span class="theme-switch-slider"></span>
            </label>
          </div>
        </div>

        <!-- =====================================================================
             CARD 5: SESSÃO (LOGOUT)
             ===================================================================== -->
        <div class="settings-card">
          <h2 class="settings-card-title" style="margin-bottom: 16px;">Sessão</h2>

          <a href="<?php echo url('logout'); ?>" class="btn-settings-logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span>Sair da Conta</span>
          </a>
        </div>

      </div>

    </main>

  </div>

  <!-- ===========================================================================
       MODAL 1: EDITAR PERFIL (NOME)
       =========================================================================== -->
  <div id="modal-edit-perfil" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title">Editar Meu Perfil</h3>
        <button type="button" class="modal-close-btn" data-close-modal="modal-edit-perfil">&times;</button>
      </div>

      <form action="<?php echo url('configuracoes'); ?>" method="POST">
        <input type="hidden" name="action" value="editar_perfil">

        <div class="form-group" style="margin-bottom: 16px;">
          <label for="perfil_nome" class="form-label">Nome Completo *</label>
          <input type="text" id="perfil_nome" name="nome" class="form-control" value="<?php echo htmlspecialchars($userName); ?>" required minlength="3">
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
          <label class="form-label">Cargo / Função (Definido pelo Sistema)</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($userRoleTitle); ?>" disabled style="opacity: 0.7; cursor: not-allowed;">
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
          <label class="form-label">Departamento</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($userDepartment); ?>" disabled style="opacity: 0.7; cursor: not-allowed;">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" class="btn-modal-cancel" data-close-modal="modal-edit-perfil">Cancelar</button>
          <button type="submit" class="btn-modal-save">Salvar Alterações</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ===========================================================================
       MODAL 2: ALTERAR E-MAIL
       =========================================================================== -->
  <div id="modal-edit-email" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title">Alterar Endereço de E-mail</h3>
        <button type="button" class="modal-close-btn" data-close-modal="modal-edit-email">&times;</button>
      </div>

      <form action="<?php echo url('configuracoes'); ?>" method="POST">
        <input type="hidden" name="action" value="editar_email">

        <div class="form-group" style="margin-bottom: 24px;">
          <label for="novo_email" class="form-label">Novo E-mail Institucional *</label>
          <input type="email" id="novo_email" name="email" class="form-control" value="<?php echo htmlspecialchars($userEmail); ?>" required>
          <small style="color: var(--admin-text-muted); font-size: 0.8rem; margin-top: 6px; display: block;">
            Este e-mail será utilizado para autenticação e recuperação de senha.
          </small>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" class="btn-modal-cancel" data-close-modal="modal-edit-email">Cancelar</button>
          <button type="submit" class="btn-modal-save">Atualizar E-mail</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ===========================================================================
       MODAL 3: ALTERAR SENHA
       =========================================================================== -->
  <div id="modal-edit-senha" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title">Alterar Senha</h3>
        <button type="button" class="modal-close-btn" data-close-modal="modal-edit-senha">&times;</button>
      </div>

      <form action="<?php echo url('configuracoes'); ?>" method="POST">
        <input type="hidden" name="action" value="alterar_senha">

        <div class="form-group" style="margin-bottom: 16px;">
          <label for="senha_atual" class="form-label">Senha Atual *</label>
          <div class="input-password-wrap">
            <input type="password" id="senha_atual" name="senha_atual" class="form-control" placeholder="••••••••" required>
            <button type="button" class="toggle-password-btn" data-target="senha_atual" aria-label="Mostrar/Ocultar Senha">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
          <label for="nova_senha" class="form-label">Nova Senha * (mínimo 6 caracteres)</label>
          <div class="input-password-wrap">
            <input type="password" id="nova_senha" name="nova_senha" class="form-control" placeholder="••••••••" required minlength="6">
            <button type="button" class="toggle-password-btn" data-target="nova_senha" aria-label="Mostrar/Ocultar Senha">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
          <label for="confirmar_senha" class="form-label">Confirmar Nova Senha *</label>
          <div class="input-password-wrap">
            <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" placeholder="••••••••" required minlength="6">
            <button type="button" class="toggle-password-btn" data-target="confirmar_senha" aria-label="Mostrar/Ocultar Senha">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" class="btn-modal-cancel" data-close-modal="modal-edit-senha">Cancelar</button>
          <button type="submit" class="btn-modal-save">Salvar Nova Senha</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts JavaScript -->
  <script src="<?php echo $assetUrl; ?>js/admin.js"></script>
</body>
</html>
