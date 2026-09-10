/**
 * ÂNCORA - ADMIN DASHBOARD INTERACTIVE SCRIPT
 * Theme Toggle & Responsive Sidebar
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. THEME SWITCHING (REUSING ANCORA SYSTEM WITH ICON ANIMATIONS)
  const themeToggleBtn = document.getElementById('admin-theme-toggle');
  const themeToggleText = document.getElementById('admin-theme-text');
  const settingsThemeCheckbox = document.getElementById('settings-theme-checkbox');
  const settingsThemeLabel = document.getElementById('settings-theme-label');
  const settingsThemeSvg = document.getElementById('settings-theme-svg');

  const sunIconSVG = `
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
  `;

  const moonIconSVG = `
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
    </svg>
  `;

  function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('ancora_theme', theme);

    if (themeToggleBtn) {
      const svgContainer = themeToggleBtn.querySelector('svg');
      if (svgContainer) {
        svgContainer.outerHTML = theme === 'dark' ? sunIconSVG : moonIconSVG;
      }
    }

    if (themeToggleText) {
      themeToggleText.textContent = theme === 'dark' ? 'Tema Claro' : 'Tema Escuro';
    }

    // Sincronização com o switch da tela de Configurações
    if (settingsThemeCheckbox) {
      settingsThemeCheckbox.checked = theme === 'dark';
    }
    if (settingsThemeLabel) {
      settingsThemeLabel.textContent = theme === 'dark' ? 'Tema Escuro ativo' : 'Tema Claro ativo';
    }
    if (settingsThemeSvg) {
      if (theme === 'dark') {
        settingsThemeSvg.innerHTML = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>';
      } else {
        settingsThemeSvg.innerHTML = '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>';
      }
    }
  }

  // Load Saved Theme
  const savedTheme = localStorage.getItem('ancora_theme') || 'dark';
  setTheme(savedTheme);

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      setTheme(newTheme);
    });
  }

  if (settingsThemeCheckbox) {
    settingsThemeCheckbox.addEventListener('change', () => {
      const newTheme = settingsThemeCheckbox.checked ? 'dark' : 'light';
      setTheme(newTheme);
    });
  }

  // 2. RESPONSIVE SIDEBAR TOGGLE
  const mobileToggleBtn = document.getElementById('admin-mobile-toggle');
  const sidebar = document.getElementById('admin-sidebar');

  if (mobileToggleBtn && sidebar) {
    mobileToggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
      if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !mobileToggleBtn.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  // 3. COPIAR CÓDIGO DA INSTITUIÇÃO COM FEEDBACK VISUAL
  const btnCopyInstituicao = document.getElementById('btn-copy-instituicao');
  if (btnCopyInstituicao) {
    btnCopyInstituicao.addEventListener('click', () => {
      const code = btnCopyInstituicao.getAttribute('data-code') || '';
      if (!code) return;

      navigator.clipboard.writeText(code).then(() => {
        const feedback = document.getElementById('copy-feedback');
        const icon = document.getElementById('copy-icon');

        if (feedback) feedback.style.display = 'inline';
        if (icon) icon.style.display = 'none';

        setTimeout(() => {
          if (feedback) feedback.style.display = 'none';
          if (icon) icon.style.display = 'inline';
        }, 2000);
      }).catch(() => {
        // Fallback em caso de bloqueio de permissão de clipboard
        alert('Código copiado: ' + code);
      });
    });
  }

  // 4. GERENCIAMENTO DE MODAIS (CONFIGURAÇÕES & GESTÃO)
  function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('active');
    }
  }

  function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove('active');
    }
  }

  // Botões de abertura de modal em Configurações
  const btnOpenEditPerfil = document.getElementById('btn-open-edit-perfil');
  if (btnOpenEditPerfil) {
    btnOpenEditPerfil.addEventListener('click', () => openModal('modal-edit-perfil'));
  }

  const btnOpenEditEmail = document.getElementById('btn-open-edit-email');
  if (btnOpenEditEmail) {
    btnOpenEditEmail.addEventListener('click', () => openModal('modal-edit-email'));
  }

  const btnOpenEditSenha = document.getElementById('btn-open-edit-senha');
  if (btnOpenEditSenha) {
    btnOpenEditSenha.addEventListener('click', () => openModal('modal-edit-senha'));
  }

  const btnOpenModalAviso = document.getElementById('btn-open-modal-aviso');
  if (btnOpenModalAviso) {
    btnOpenModalAviso.addEventListener('click', () => openModal('modal-enviar-aviso'));
  }

  // Fechamento genérico de modais
  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      const modalId = btn.getAttribute('data-close-modal');
      closeModal(modalId);
    });
  });

  // Fechar ao clicar no backdrop (overlay)
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        overlay.classList.remove('active');
      }
    });
  });

  // 5. TOGGLE DE VISIBILIDADE DE SENHA EM FORMULÁRIOS
  const togglePassBtns = document.querySelectorAll('.toggle-password-btn');
  togglePassBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-target');
      const input = document.getElementById(targetId);
      if (!input) return;

      if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
      } else {
        input.type = 'password';
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
      }
    });
  });

  // 6. NAVEGAÇÃO DE ABAS NA CENTRAL DE NOTIFICAÇÕES (CENTRAL / CONFIGURAÇÕES)
  const tabBtns = document.querySelectorAll('.notif-tab-btn');
  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetTabId = btn.getAttribute('data-tab');
      if (!targetTabId) return;

      document.querySelectorAll('.notif-tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.notif-tab-content').forEach(c => c.classList.remove('active'));

      btn.classList.add('active');
      const targetContent = document.getElementById(targetTabId);
      if (targetContent) {
        targetContent.classList.add('active');
      }
    });
  });

  // 7. MOTOR DE POLLING AJAX DE NOTIFICAÇÕES EM TEMPO REAL (INFINITYFREE COMPATIBLE)
  let lastNotificationId = 0;
  
  // Descobre a maior ID de notificação existente no DOM ao carregar
  document.querySelectorAll('.notif-card-item[data-id]').forEach(card => {
    const cardId = parseInt(card.getAttribute('data-id'), 10);
    if (cardId > lastNotificationId) {
      lastNotificationId = cardId;
    }
  });

  function updateUnreadCounters(count) {
    const sidebarBadge = document.getElementById('sidebar-unread-count');
    const headerSub = document.getElementById('notificacoes-header-sub');
    const tabBadge = document.getElementById('tab-unread-badge');

    if (sidebarBadge) {
      if (count > 0) {
        sidebarBadge.textContent = count;
        sidebarBadge.style.display = 'inline-block';
      } else {
        sidebarBadge.style.display = 'none';
      }
    }

    if (tabBadge) {
      tabBadge.textContent = count;
    }

    if (headerSub) {
      if (count > 0) {
        headerSub.textContent = `${count} não lida${count > 1 ? 's' : ''}`;
      } else {
        headerSub.textContent = 'Você está em dia!';
      }
    }
  }

  function showToastNotification(item) {
    let container = document.querySelector('.ancora-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'ancora-toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'ancora-toast';
    
    let iconSvg = '🔔';
    if (item.tipo === 'Alerta') iconSvg = '⚠️';
    else if (item.tipo === 'Sucesso') iconSvg = '✅';
    else if (item.tipo === 'Erro') iconSvg = '❌';

    toast.innerHTML = `
      <div style="font-size: 1.2rem; flex-shrink: 0;">${iconSvg}</div>
      <div style="flex: 1;">
        <strong style="display: block; font-size: 0.9rem; color: var(--admin-text-heading);">${escapeHtml(item.titulo)}</strong>
        <span style="display: block; font-size: 0.8rem; color: var(--admin-text-sub); margin-top: 2px;">${escapeHtml(item.mensagem)}</span>
      </div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
      toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(20px)';
      setTimeout(() => toast.remove(), 400);
    }, 5000);
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }

  function pollNotifications() {
    // Determina a URL base dinamicamente (respeitando ambiente local ou produção)
    const baseUrl = window.location.pathname.includes('/public/') ? 'index.php?route=notificacoes/poll' : 'index.php?route=notificacoes/poll';
    const pollUrl = `${baseUrl}&last_id=${lastNotificationId}&_t=${Date.now()}`;

    fetch(pollUrl, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => {
      if (!response.ok) throw new Error('Polling error');
      return response.json();
    })
    .then(data => {
      if (data && data.success) {
        updateUnreadCounters(data.unread_count);

        if (data.max_id && data.max_id > lastNotificationId) {
          lastNotificationId = data.max_id;
        }

        if (data.new_items && data.new_items.length > 0) {
          const listContainer = document.getElementById('notif-list-container');
          
          data.new_items.forEach(item => {
            showToastNotification(item);

            // Se o usuário estiver na tela de notificações, insere o novo card no topo
            if (listContainer) {
              const emptyState = listContainer.querySelector('.notif-empty-box');
              if (emptyState) emptyState.remove();

              let typeClass = 'info';
              let typeLabel = 'Info';
              let iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';

              if (item.tipo === 'Alerta') {
                typeClass = 'alerta'; typeLabel = 'Alerta';
                iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
              } else if (item.tipo === 'Sucesso') {
                typeClass = 'sucesso'; typeLabel = 'Sucesso';
                iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
              } else if (item.tipo === 'Erro') {
                typeClass = 'erro'; typeLabel = 'Erro';
                iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
              }

              const newCard = document.createElement('div');
              newCard.className = 'notif-card-item unread';
              newCard.setAttribute('data-id', item.id);
              newCard.innerHTML = `
                <div class="notif-item-left">
                  <div class="notif-type-icon ${typeClass}">${iconSvg}</div>
                  <div class="notif-item-content">
                    <div class="notif-item-title-row">
                      <span class="notif-item-title">${escapeHtml(item.titulo)}</span>
                      <span class="notif-type-pill ${typeClass}">${typeLabel}</span>
                      <span class="notif-unread-dot" title="Não lida"></span>
                    </div>
                    <div class="notif-item-body">${escapeHtml(item.mensagem).replace(/\n/g, '<br>')}</div>
                    <div class="notif-item-date">${escapeHtml(item.data_formatada)}</div>
                  </div>
                </div>
                <div class="notif-item-actions">
                  <button type="button" class="btn-icon-notif btn-mark-read" data-id="${item.id}" title="Marcar como lida">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                  </button>
                  <button type="button" class="btn-icon-notif danger btn-delete-notif" data-id="${item.id}" title="Excluir notificação">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              `;

              listContainer.insertBefore(newCard, listContainer.firstChild);
              attachCardActionEvents(newCard);
            }
          });
        }
      }
    })
    .catch(() => {
      // Falhas de conexão temporárias são ignoradas silenciosamente no polling
    });
  }

  // 8. EVENTOS DE AÇÃO NOS CARDS DE NOTIFICAÇÃO (MARCAR COMO LIDA E EXCLUIR)
  function attachCardActionEvents(parentContext) {
    const context = parentContext || document;
    
    // Marcar individualmente como lida
    context.querySelectorAll('.btn-mark-read').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const id = btn.getAttribute('data-id');
        const card = btn.closest('.notif-card-item');

        const formData = new FormData();
        formData.append('action', 'marcar_lida');
        formData.append('id', id);

        fetch('index.php?route=notificacoes/action', {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
          if (res.success) {
            if (card) {
              card.classList.remove('unread');
              const dot = card.querySelector('.notif-unread-dot');
              if (dot) dot.remove();
            }
            btn.remove();
            updateUnreadCounters(res.unread_count);
          }
        });
      });
    });

    // Excluir notificação
    context.querySelectorAll('.btn-delete-notif').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const id = btn.getAttribute('data-id');
        const card = btn.closest('.notif-card-item');

        if (!confirm('Deseja realmente excluir esta notificação?')) return;

        const formData = new FormData();
        formData.append('action', 'excluir');
        formData.append('id', id);

        fetch('index.php?route=notificacoes/action', {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
          if (res.success) {
            if (card) {
              card.style.transition = 'all 0.3s ease';
              card.style.opacity = '0';
              card.style.transform = 'scale(0.95)';
              setTimeout(() => card.remove(), 300);
            }
            updateUnreadCounters(res.unread_count);
          }
        });
      });
    });
  }

  // Inicializa manipuladores de eventos de cards existentes
  attachCardActionEvents();

  // Inicia o Polling de Notificações a cada 6 segundos (otimizado para InfinityFree)
  setInterval(pollNotifications, 6000);
});


