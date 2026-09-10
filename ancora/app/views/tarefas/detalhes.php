<?php 
$assetUrl = defined('ASSET_URL') ? ASSET_URL : 'public/'; 
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$isEncerrada = (!empty($tarefa['prazo_entrega']) && strtotime('now') > strtotime($tarefa['prazo_entrega']));
$isEntregue = ($entrega !== null);
$isDevolvida = ($isEntregue && $entrega['status'] === 'devolvida');
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($tarefa['titulo']); ?> — ÂNCORA</title>
  
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
    /* Estilos Elegantes para Detalhes de Tarefa e Questionário */
    .task-detail-card {
      background: var(--admin-card-bg);
      border: 1px solid var(--admin-card-border);
      border-radius: 18px;
      padding: 26px;
      margin-bottom: 22px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.07);
    }

    .countdown-banner {
      background: rgba(37, 99, 235, 0.08);
      border: 1.5px solid rgba(37, 99, 235, 0.25);
      border-radius: 14px;
      padding: 16px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      margin-bottom: 22px;
      color: var(--admin-text-heading);
    }

    .countdown-banner.expired {
      background: rgba(239, 68, 68, 0.08);
      border-color: rgba(239, 68, 68, 0.3);
      color: #EF4444;
    }

    .question-block {
      background: var(--admin-item-row-bg, #0B1120);
      border: 1px solid var(--admin-item-row-border);
      border-left: 4px solid #38BDF8;
      border-radius: 14px;
      padding: 22px;
      margin-bottom: 18px;
      transition: all 0.2s ease;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .question-block:hover {
      border-color: rgba(56, 189, 248, 0.4);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    .question-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
    }

    .question-badge-num {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(56, 189, 248, 0.12);
      color: #38BDF8;
      font-size: 0.825rem;
      font-weight: 700;
      padding: 4px 12px;
      border-radius: 20px;
      letter-spacing: 0.3px;
    }

    .question-badge-pts {
      font-size: 0.8rem;
      color: var(--admin-text-muted);
      font-weight: 600;
      background: rgba(148, 163, 184, 0.1);
      padding: 3px 10px;
      border-radius: 8px;
    }

    .question-enunciado {
      color: var(--admin-text-heading);
      font-size: 1rem;
      font-weight: 600;
      line-height: 1.6;
      margin: 0 0 16px 0;
    }

    /* Caixas de Texto Refinadas */
    .form-input-control {
      width: 100%;
      padding: 13px 16px;
      border-radius: 12px;
      border: 1.5px solid var(--admin-card-border);
      background: rgba(15, 23, 42, 0.5);
      color: var(--admin-text-heading);
      font-family: inherit;
      font-size: 0.925rem;
      outline: none;
      transition: all 0.2s ease;
      box-sizing: border-box;
    }

    .form-input-control:focus {
      border-color: #38BDF8;
      box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18);
      background: rgba(15, 23, 42, 0.7);
    }

    .form-input-control::placeholder {
      color: var(--admin-text-muted);
      opacity: 0.7;
    }

    textarea.form-input-control {
      resize: vertical;
      line-height: 1.6;
      min-height: 110px;
    }

    /* Alternativas Elegantes em Formato de Card Selecionável */
    .options-list-grid {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .question-option-card {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px 18px;
      border-radius: 12px;
      background: rgba(15, 23, 42, 0.35);
      border: 1.5px solid rgba(148, 163, 184, 0.15);
      color: var(--admin-text-heading);
      font-size: 0.95rem;
      cursor: pointer;
      transition: all 0.2s ease;
      user-select: none;
    }

    .question-option-card:hover {
      border-color: #38BDF8;
      background: rgba(37, 99, 235, 0.08);
      transform: translateX(3px);
    }

    .question-option-card:has(input[type="radio"]:checked) {
      border-color: #2563EB;
      background: rgba(37, 99, 235, 0.16);
      box-shadow: 0 0 0 1px #2563EB;
      font-weight: 600;
    }

    .question-option-card input[type="radio"] {
      width: 18px;
      height: 18px;
      accent-color: #2563EB;
      cursor: pointer;
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

    .grade-display-card {
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(56, 189, 248, 0.15) 100%);
      border: 1px solid rgba(16, 185, 129, 0.3);
      border-radius: 18px;
      padding: 26px;
      text-align: center;
      margin-bottom: 22px;
      box-shadow: 0 4px 20px rgba(16, 185, 129, 0.1);
    }

    .grade-number-highlight {
      font-size: 3rem;
      font-weight: 800;
      color: #10B981;
      line-height: 1;
      margin: 10px 0;
    }

    .task-submit-bar {
      background: var(--admin-card-bg);
      border: 1px solid var(--admin-card-border);
      border-radius: 18px;
      padding: 22px 28px;
      margin-top: 26px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 18px;
      flex-wrap: wrap;
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
    }

    .btn-enviar-tarefa {
      background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
      color: #FFFFFF !important;
      border: none !important;
      border-radius: 12px !important;
      padding: 14px 34px !important;
      font-size: 1.05rem !important;
      font-weight: 700 !important;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 4px 16px rgba(37, 99, 235, 0.4) !important;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .btn-enviar-tarefa:hover {
      background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%) !important;
      transform: translateY(-2px);
      box-shadow: 0 6px 22px rgba(37, 99, 235, 0.55) !important;
    }

    .btn-enviar-tarefa:active {
      transform: translateY(0);
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
      <div class="sidebar-role-badge <?php echo htmlspecialchars($perfilSlug ?? 'aluno'); ?>">
        <span class="role-dot"></span>
        <span><?php echo htmlspecialchars($userRoleTitle ?? 'Aluno'); ?></span>
      </div>

      <!-- Grupo 1: PRINCIPAL -->
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">PRINCIPAL</span>
        <a href="<?php echo $inicioUrl ?? url('aluno'); ?>" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
          <span>Início</span>
        </a>
        <a href="<?php echo url('tarefas'); ?>" class="sidebar-menu-link active">
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
        </a>
        <a href="#" class="sidebar-menu-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>Mensagens</span>
        </a>
      </div>

      <!-- Grupo 2: GESTÃO -->
      <div class="sidebar-nav-group">
        <span class="sidebar-group-title">GESTÃO</span>
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
          <div class="user-avatar-circle <?php echo htmlspecialchars($perfilSlug ?? 'aluno'); ?>"><?php echo htmlspecialchars($userInitials); ?></div>
          <div class="user-info-text">
            <span class="user-name-title"><?php echo htmlspecialchars($userName); ?></span>
            <span class="user-role-sub"><?php echo htmlspecialchars($userRoleTitle ?? 'Aluno'); ?></span>
          </div>
        </div>
        <a href="<?php echo url('logout'); ?>" class="logout-icon-btn" title="Sair do sistema">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="admin-main-content">
      
      <!-- Botão Voltar -->
      <div style="margin-bottom: 16px;">
        <a href="<?php echo url('tarefas'); ?>" style="display: inline-flex; align-items: center; gap: 6px; color: var(--admin-text-muted); text-decoration: none; font-size: 0.875rem; font-weight: 600;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Voltar para Tarefas
        </a>
      </div>

      <!-- Alertas -->
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

      <!-- Card da Nota / Feedback se Devolvida -->
      <?php if ($isDevolvida): ?>
        <div class="grade-display-card">
          <span style="font-size: 0.9rem; font-weight: 600; color: var(--admin-text-muted); text-transform: uppercase;">Atividade Corrigida e Devolvida</span>
          <div class="grade-number-highlight">
            <?php echo number_format((float)($entrega['nota'] ?? 0), 1, ',', '.'); ?> <span style="font-size: 1.2rem; color: var(--admin-text-muted);">/ 10,0</span>
          </div>
          <?php if (!empty($entrega['feedback_geral'])): ?>
            <div style="margin-top: 14px; padding: 12px; background: rgba(15, 23, 42, 0.4); border-radius: 10px; color: var(--admin-text-heading); font-size: 0.9rem; text-align: left;">
              <strong>Feedback do Professor:</strong><br>
              <?php echo nl2br(htmlspecialchars($entrega['feedback_geral'])); ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Banner de Prazo & Contagem Regressiva -->
      <div class="countdown-banner <?php echo $isEncerrada ? 'expired' : ''; ?>">
        <div style="display: flex; align-items: center; gap: 10px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <div>
            <strong>Prazo de Entrega:</strong> <?php echo htmlspecialchars($tarefa['prazo_formatado']); ?>
          </div>
        </div>
        <div id="live-countdown-text" style="font-weight: 700; font-size: 0.9rem;" data-deadline="<?php echo $tarefa['prazo_entrega']; ?>">
          <!-- Preenchido via JS -->
        </div>
      </div>

      <!-- Card de Informações da Tarefa -->
      <div class="task-detail-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 14px;">
          <div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--admin-text-heading); margin: 0 0 6px 0;">
              <?php echo htmlspecialchars($tarefa['titulo']); ?>
            </h1>
            <div style="font-size: 0.85rem; color: var(--admin-text-muted);">
              Prof. <?php echo htmlspecialchars($tarefa['criador_nome']); ?> 
              <?php if (!empty($tarefa['disciplina'])): ?>
                • <span style="color: #38BDF8; font-weight: 600;"><?php echo htmlspecialchars($tarefa['disciplina']); ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div>
            <?php if ($isDevolvida): ?>
              <span class="tarefa-badge devolvida" style="font-size: 0.85rem; padding: 6px 14px;">Devolvida com Nota</span>
            <?php elseif ($isEntregue): ?>
              <span class="tarefa-badge entregue" style="font-size: 0.85rem; padding: 6px 14px;">Entregue</span>
            <?php elseif ($isEncerrada): ?>
              <span class="tarefa-badge atrasada" style="font-size: 0.85rem; padding: 6px 14px;">Prazo Encerrado</span>
            <?php else: ?>
              <span class="tarefa-badge pendente" style="font-size: 0.85rem; padding: 6px 14px;">Pendente de Envio</span>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($tarefa['descricao'])): ?>
          <div style="color: var(--admin-text-heading); font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px; white-space: pre-line;">
            <?php echo htmlspecialchars($tarefa['descricao']); ?>
          </div>
        <?php endif; ?>

        <!-- Materiais de Apoio -->
        <?php if (!empty($tarefa['materiais'])): ?>
          <div style="margin-top: 20px; border-top: 1px solid var(--admin-item-row-border); padding-top: 16px;">
            <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--admin-text-heading); margin-bottom: 12px;">Materiais de Apoio</h4>
            <?php foreach ($tarefa['materiais'] as $mat): ?>
              <a href="<?php echo url('tarefas/download-material', ['id' => $mat['id']]); ?>" class="material-item-download">
                <div style="display: flex; align-items: center; gap: 10px;">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  <span><?php echo htmlspecialchars($mat['nome_original']); ?></span>
                </div>
                <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Baixar arquivo</span>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Formulário de Realização da Atividade / Envio -->
      <form action="<?php echo url('tarefas/submeter'); ?>" method="POST" enctype="multipart/form-data" id="form-submeter-atividade" onsubmit="return confirmarEnvioTarefa(event);">
        <input type="hidden" name="tarefa_id" value="<?php echo (int)$tarefa['id']; ?>">

        <!-- Bloco de Questões (se houver) -->
        <?php if (!empty($tarefa['questoes'])): ?>
          <div class="task-detail-card">
            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--admin-text-heading); margin-bottom: 16px;">
              Questionário
            </h3>

            <?php foreach ($tarefa['questoes'] as $idx => $q): ?>
              <?php 
                $qId = (int)$q['id'];
                // Se já entregou, buscar resposta anterior
                $respSalva = null;
                if ($isEntregue && !empty($entrega['respostas'])) {
                  foreach ($entrega['respostas'] as $r) {
                    if ((int)$r['questao_id'] === $qId) {
                      $respSalva = $r;
                      break;
                    }
                  }
                }
              ?>
              <div class="question-block">
                <div class="question-header">
                  <span class="question-badge-num">Questão <?php echo $idx + 1; ?></span>
                  <span class="question-badge-pts"><?php echo $q['pontos']; ?> pt(s)</span>
                </div>

                <div class="question-enunciado">
                  <?php echo htmlspecialchars($q['enunciado']); ?>
                </div>

                <!-- Input conforme o tipo de questão -->
                <?php if ($q['tipo'] === 'discursiva'): ?>
                  <textarea 
                    name="respostas[<?php echo $qId; ?>]" 
                    rows="5" 
                    class="form-input-control" 
                    placeholder="Digite sua resposta detalhada para esta questão..."
                    <?php echo ($isEncerrada || $isDevolvida) ? 'readonly' : ''; ?>
                  ><?php echo htmlspecialchars($respSalva['resposta_texto'] ?? ''); ?></textarea>

                <?php elseif ($q['tipo'] === 'resposta_curta'): ?>
                  <input 
                    type="text" 
                    name="respostas[<?php echo $qId; ?>]" 
                    class="form-input-control" 
                    placeholder="Digite sua resposta curta..."
                    value="<?php echo htmlspecialchars($respSalva['resposta_texto'] ?? ''); ?>"
                    <?php echo ($isEncerrada || $isDevolvida) ? 'readonly' : ''; ?>
                  >

                <?php elseif ($q['tipo'] === 'multipla_escolha' || $q['tipo'] === 'verdadeiro_falso'): ?>
                  <?php 
                    $alts = !empty($q['alternativas_json']) ? json_decode($q['alternativas_json'], true) : [];
                    if ($q['tipo'] === 'verdadeiro_falso' && empty($alts)) {
                      $alts = ['Verdadeiro', 'Falso'];
                    }
                  ?>
                  <div class="options-list-grid">
                    <?php foreach ($alts as $alt): ?>
                      <label class="question-option-card">
                        <input 
                          type="radio" 
                          name="respostas[<?php echo $qId; ?>]" 
                          value="<?php echo htmlspecialchars($alt); ?>"
                          <?php echo (($respSalva['resposta_texto'] ?? '') === $alt) ? 'checked' : ''; ?>
                          <?php echo ($isEncerrada || $isDevolvida) ? 'disabled' : ''; ?>
                        >
                        <span><?php echo htmlspecialchars($alt); ?></span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <?php if ($isDevolvida && isset($respSalva['pontos_obtidos'])): ?>
                  <div style="margin-top: 10px; font-size: 0.85rem; color: #10B981; font-weight: 600;">
                    Nota obtida nesta questão: <?php echo $respSalva['pontos_obtidos']; ?> / <?php echo $q['pontos']; ?>
                    <?php if (!empty($respSalva['comentario_professor'])): ?>
                      <div style="color: var(--admin-text-muted); font-weight: normal; margin-top: 4px;">
                        <em>Comentário: <?php echo htmlspecialchars($respSalva['comentario_professor']); ?></em>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Anexo de Resposta pelo Aluno -->
        <?php 
          $temQuestionario = !empty($tarefa['questoes']);
          $permiteAnexo = !empty($tarefa['permite_anexo_aluno']);
          // Se não houver questionário, anexo é a única forma de entrega
          $mostrarBlocoAnexo = $permiteAnexo || !$temQuestionario;
        ?>

        <?php if ($mostrarBlocoAnexo): ?>
          <div class="task-detail-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
              <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--admin-text-heading); margin: 0;">
                Anexar Arquivo(s) de Resposta
              </h3>
              <?php if ($temQuestionario): ?>
                <span style="font-size: 0.75rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; background: rgba(56, 189, 248, 0.15); color: #38BDF8; text-transform: uppercase; letter-spacing: 0.5px;">
                  Opcional
                </span>
              <?php else: ?>
                <span style="font-size: 0.75rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; background: rgba(239, 68, 68, 0.15); color: #EF4444; text-transform: uppercase; letter-spacing: 0.5px;">
                  Obrigatório
                </span>
              <?php endif; ?>
            </div>

            <?php if ($temQuestionario): ?>
              <div style="background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 10px; padding: 12px 14px; margin-bottom: 14px; color: var(--admin-text-heading); font-size: 0.85rem; display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span>Como esta tarefa possui um questionário, <strong>o anexo de arquivos é opcional</strong>. Você pode enviar a tarefa apenas com as questões preenchidas acima.</span>
              </div>
            <?php else: ?>
              <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; padding: 12px 14px; margin-bottom: 14px; color: var(--admin-text-heading); font-size: 0.85rem; display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>Esta tarefa não possui questionário. <strong>Anexe o(s) seu(s) arquivo(s) de entrega</strong> abaixo para envio ao professor.</span>
              </div>
            <?php endif; ?>

            <?php if (!$isEncerrada && !$isDevolvida): ?>
              <input 
                type="file" 
                name="arquivos_entrega[]" 
                class="form-input-control" 
                multiple 
                <?php echo (!$temQuestionario && empty($entrega['arquivos'])) ? 'required' : ''; ?>
              >
              <span style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 6px; display: block;">
                Formatos permitidos: PDF, DOC, DOCX, ZIP, imagens, códigos-fonte, etc. (Máx 30MB)
              </span>
            <?php endif; ?>

            <?php if ($isEntregue && !empty($entrega['arquivos'])): ?>
              <div style="margin-top: 14px;">
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--admin-text-muted);">Arquivos já enviados:</span>
                <?php foreach ($entrega['arquivos'] as $ea): ?>
                  <a href="<?php echo url('tarefas/download-entrega', ['id' => $ea['id']]); ?>" class="material-item-download" style="margin-top: 6px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                      <span><?php echo htmlspecialchars($ea['nome_original']); ?></span>
                    </div>
                    <span style="font-size: 0.75rem; color: #38BDF8;">Baixar meu anexo</span>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- Barra Principal de Envio da Tarefa -->
        <?php if (!$isEncerrada && !$isDevolvida): ?>
          <div class="task-submit-bar" style="background: var(--admin-card-bg); border: 1px solid var(--admin-card-border); border-radius: 16px; padding: 22px 26px; margin-top: 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
            <div>
              <div style="font-size: 1.05rem; font-weight: 700; color: var(--admin-text-heading);">
                <?php echo $isEntregue ? 'Atividade enviada anteriormente' : 'Pronto para enviar sua atividade?'; ?>
              </div>
              <p style="font-size: 0.85rem; color: var(--admin-text-muted); margin: 4px 0 0 0;">
                <?php echo $isEntregue 
                  ? 'Você pode atualizar suas respostas ou anexos e reenviar até o prazo final.' 
                  : 'Ao clicar no botão ao lado, suas respostas e eventuais arquivos serão enviados para o professor.'; ?>
              </p>
            </div>

            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
              <a href="<?php echo url('tarefas'); ?>" class="btn-card-action secondary" style="padding: 12px 20px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                Voltar
              </a>
              
              <button 
                type="submit" 
                class="btn-primary-action btn-enviar-tarefa" 
                id="btn-enviar-tarefa" 
                style="background: #2563EB; color: #FFFFFF; border: none; border-radius: 10px; padding: 13px 32px; font-size: 1.05rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4); transition: all 0.2s;"
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                <span><?php echo $isEntregue ? 'Atualizar e Enviar Tarefa' : 'Enviar Tarefa'; ?></span>
              </button>
            </div>
          </div>
        <?php else: ?>
          <div style="background: var(--admin-card-bg); border: 1px solid var(--admin-card-border); border-radius: 16px; padding: 22px; text-align: center; color: var(--admin-text-muted); font-size: 0.95rem; margin-top: 24px;">
            <?php if ($isDevolvida): ?>
              <span style="color: #10B981; font-weight: 700;">✓ Esta atividade já foi avaliada e finalizada pelo professor. Nota final: <?php echo number_format((float)($entrega['nota'] ?? 0), 1, ',', '.'); ?>/10,0</span>
            <?php else: ?>
              <span style="color: #EF4444; font-weight: 700;">🚨 O prazo para envio desta tarefa foi encerrado em <?php echo htmlspecialchars($tarefa['prazo_formatado']); ?>. Não são permitidos novos envios.</span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      </form>

    </main>
  </div>

  <script src="<?php echo $assetUrl; ?>js/admin.js"></script>

  <script>
    // Live Countdown Timer no Frontend
    document.addEventListener('DOMContentLoaded', function() {
      const countdownEl = document.getElementById('live-countdown-text');
      if (countdownEl) {
        const deadlineStr = countdownEl.getAttribute('data-deadline');
        const deadline = new Date(deadlineStr.replace(/-/g, '/')).getTime();

        function updateCountdown() {
          const now = new Date().getTime();
          const diff = deadline - now;

          if (diff <= 0) {
            countdownEl.textContent = '🚨 Prazo Encerrado';
            return;
          }

          const dias = Math.floor(diff / (1000 * 60 * 60 * 24));
          const horas = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

          if (dias > 0) {
            countdownEl.textContent = `⏱ Termina em ${dias} dia(s), ${horas}h e ${mins}min`;
          } else {
            countdownEl.textContent = `⏱ Termina hoje em ${horas}h e ${mins}min`;
          }
        }

        updateCountdown();
        setInterval(updateCountdown, 60000);
      }

      // Confirmação e Prevenção de Duplo Envio da Tarefa
      window.confirmarEnvioTarefa = function(e) {
        if (!confirm('Deseja realmente enviar sua tarefa agora? Suas respostas serão registradas no sistema.')) {
          if (e) e.preventDefault();
          return false;
        }
        const btn = document.getElementById('btn-enviar-tarefa');
        if (btn) {
          btn.disabled = true;
          btn.innerHTML = '<span style="display:inline-flex; align-items:center; gap:8px;">⏳ Enviando Tarefa...</span>';
        }
        return true;
      };
    });
  </script>

</body>
</html>
