<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? $pageTitle : 'ÂNCORA — Gestão Escolar Moderna e Eficiente'; ?></title>
  
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Stylesheet -->
  <?php $assetUrl = defined('ASSET_URL') ? ASSET_URL : 'public/'; ?>
  <link rel="stylesheet" href="<?php echo $assetUrl; ?>css/home.css">
</head>
<body>

  <!-- =========================================================================
       1. NAVBAR
       ========================================================================= -->
  <header class="navbar">
    <div class="container navbar-container">
      <a href="#" class="brand-logo">
        <div class="home-logo-badge">
          <img src="<?php echo $assetUrl; ?>images/logo.png" alt="ÂNCORA Logo">
        </div>
      </a>

      <nav>
        <ul class="nav-links" id="nav-links">
          <li><a href="#funcionalidades" class="nav-link">Funcionalidades</a></li>
          <li><a href="#planos" class="nav-link">Planos</a></li>
          <li><a href="#faq" class="nav-link">FAQ</a></li>
        </ul>
      </nav>

      <div class="nav-actions">
        <!-- Theme Toggle Button (Light / Dark) -->
        <button id="theme-toggle-btn" class="theme-toggle-btn" aria-label="Alternar Tema" title="Alternar Tema Claro/Escuro">
          <span id="theme-icon">
            <!-- Dynamic SVG injected via home.js -->
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
          </span>
        </button>

        <a href="<?php echo url('login'); ?>" class="btn-login">Entrar</a>
        <a href="<?php echo url('cadastro'); ?>" class="btn-primary-cta">Começar agora</a>

        <!-- Mobile Menu Toggle Button -->
        <button class="mobile-toggle" id="mobile-toggle" aria-label="Abrir Menu">
          ☰
        </button>
      </div>
    </div>
  </header>

  <!-- =========================================================================
       2. HERO SECTION
       ========================================================================= -->
  <section class="hero-section">
    <div class="container">
      <div class="hero-grid">
        
        <!-- Left Content -->
        <div class="hero-content">
          <div class="hero-badge">
            <span>✨ Gestão escolar moderna e eficiente</span>
          </div>

          <h1 class="hero-title">
            A plataforma que sua <br>
            <span class="gradient-text">escola</span> estava <br>
            esperando
          </h1>

          <p class="hero-subtitle">
            ÂNCORA reúne tarefas, eventos, comunicação e gestão de espaços em um único sistema intuitivo. Menos burocracia, mais tempo para o que importa.
          </p>

          <div class="hero-cta-group">
            <a href="<?php echo url('cadastro'); ?>" class="btn-hero-primary">
              Criar Instituição grátis
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="<?php echo url('login'); ?>" class="btn-hero-secondary">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Entrar na plataforma
            </a>
          </div>

          <div class="hero-checklist">
            <span>✓ Gratuito para começar</span>
            <span>·</span>
            <span>✓ Sem cartão de crédito</span>
            <span>·</span>
            <span>✓ Configuração em minutos</span>
          </div>

          <div class="hero-stats">
            <div class="stat-item">
              <span class="stat-number">1</span>
              <span class="stat-label">Instituições</span>
            </div>
            <div class="stat-item">
              <span class="stat-number">4</span>
              <span class="stat-label">Usuários</span>
            </div>
            <div class="stat-item">
              <span class="stat-number gradient">99.9%</span>
              <span class="stat-label">Uptime</span>
            </div>
          </div>
        </div>

        <!-- Right Hero Mockup Preview -->
        <div class="hero-mockup-wrapper">
          <div class="mockup-window">
            <div class="mockup-header">
              <div class="window-dots">
                <span class="dot dot-red"></span>
                <span class="dot dot-yellow"></span>
                <span class="dot dot-green"></span>
              </div>
              <div class="mockup-search-bar"></div>
            </div>

            <div class="mockup-body">
              <!-- Sidebar -->
              <div class="mockup-sidebar">
                <div class="sidebar-item active">Dashboard</div>
                <div class="sidebar-item">Tarefas</div>
                <div class="sidebar-item">Eventos</div>
                <div class="sidebar-item">Espaços</div>
                <div class="sidebar-item">Notificações</div>
                <div class="sidebar-item">Turmas</div>
              </div>

              <!-- Main Mockup Dashboard area -->
              <div class="mockup-content">
                <div class="mockup-stats-grid">
                  <div class="mockup-stat-card">
                    <div class="mockup-stat-val">24</div>
                    <div class="mockup-stat-lbl">Tarefas</div>
                  </div>
                  <div class="mockup-stat-card">
                    <div class="mockup-stat-val purple">8</div>
                    <div class="mockup-stat-lbl">Eventos</div>
                  </div>
                  <div class="mockup-stat-card">
                    <div class="mockup-stat-val teal">312</div>
                    <div class="mockup-stat-lbl">Alunos</div>
                  </div>
                </div>

                <!-- Bar Chart -->
                <div class="mockup-chart-card">
                  <div class="chart-title">Atividade da semana</div>
                  <div class="chart-bars">
                    <div class="bar" style="height: 40%;"></div>
                    <div class="bar" style="height: 65%;"></div>
                    <div class="bar" style="height: 45%;"></div>
                    <div class="bar" style="height: 85%;"></div>
                    <div class="bar" style="height: 55%;"></div>
                    <div class="bar" style="height: 95%;"></div>
                    <div class="bar" style="height: 70%;"></div>
                  </div>
                </div>

                <!-- Activity list -->
                <div class="mockup-activity-list">
                  <div class="activity-item">
                    <span class="act-dot dot-blue"></span>
                    <span>Tarefa entregue — Matemática 3A</span>
                  </div>
                  <div class="activity-item">
                    <span class="act-dot dot-purple"></span>
                    <span>Feira de Ciências — 42/50 vagas</span>
                  </div>
                  <div class="activity-item">
                    <span class="act-dot dot-emerald"></span>
                    <span>Quadra reservada — Terça 14h</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- =========================================================================
       3. WHITE CONTAINER SECTION (MÓDULOS, DIFERENCIAIS, PREÇOS, FAQ, CTA)
       ========================================================================= -->
  <div class="container">
    <div class="main-content-card">
      
      <!-- =====================================================================
           SECTION: MÓDULOS
           ===================================================================== -->
      <section id="funcionalidades" class="modules-section">
        <div class="container">
          <div class="section-header">
            <span class="section-tag">MÓDULOS</span>
            <h2 class="section-title">Tudo que sua escola precisa</h2>
            <p class="section-subtitle">Módulos integrados para uma gestão completa e sem fricção.</p>
          </div>

          <div class="modules-grid">
            <!-- Module 1 -->
            <div class="module-card">
              <div class="module-icon-box icon-blue">📖</div>
              <h3>Gestão de Tarefas</h3>
              <p>Crie, distribua e acompanhe atividades acadêmicas por turma com filtros avançados.</p>
            </div>

            <!-- Module 2 -->
            <div class="module-card">
              <div class="module-icon-box icon-purple">📅</div>
              <h3>Eventos Escolares</h3>
              <p>Organize e publique eventos. Alunos podem se inscrever e acompanhar vagas em tempo real.</p>
            </div>

            <!-- Module 3 -->
            <div class="module-card">
              <div class="module-icon-box icon-emerald">📍</div>
              <h3>Reserva de Espaços</h3>
              <p>Reserve salas, quadras e laboratórios por horário. Sem conflitos, sem burocracia.</p>
            </div>

            <!-- Module 4 -->
            <div class="module-card">
              <div class="module-icon-box icon-orange">🔍</div>
              <h3>Achados e Perdidos</h3>
              <p>Central digital para itens perdidos. Registre, filtre e reivindique com facilidade.</p>
            </div>

            <!-- Module 5 -->
            <div class="module-card">
              <div class="module-icon-box icon-pink">🔔</div>
              <h3>Comunicação Escolar</h3>
              <p>Notificações segmentadas por perfil. Mensagens certas para as pessoas certas.</p>
            </div>

            <!-- Module 6 -->
            <div class="module-card">
              <div class="module-icon-box icon-mint">🎓</div>
              <h3>Gestão de Turmas</h3>
              <p>Crie turmas, vincule alunos e professores. Controle acadêmico completo.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- =====================================================================
           SECTION: DIFERENCIAIS
           ===================================================================== -->
      <section class="differentials-section">
        <div class="container">
          <div class="differentials-grid">
            
            <!-- Left Features -->
            <div class="diff-left">
              <div class="diff-header">
                <span class="section-tag purple">DIFERENCIAIS</span>
                <h2 class="section-title">Por que o ÂNCORA é diferente?</h2>
              </div>

              <div class="diff-features-list">
                <!-- Feature 1 -->
                <div class="diff-feature-item">
                  <div class="diff-icon-box">🛡️</div>
                  <div class="diff-feature-content">
                    <h4>Segurança e privacidade</h4>
                    <p>Conformidade com LGPD, criptografia de dados e controle granular de permissões por perfil.</p>
                  </div>
                </div>

                <!-- Feature 2 -->
                <div class="diff-feature-item">
                  <div class="diff-icon-box">⚡</div>
                  <div class="diff-feature-content">
                    <h4>Rápido e intuitivo</h4>
                    <p>Interface pensada para usuários não técnicos. Professores e alunos adotam em minutos.</p>
                  </div>
                </div>

                <!-- Feature 3 -->
                <div class="diff-feature-item">
                  <div class="diff-icon-box">📊</div>
                  <div class="diff-feature-content">
                    <h4>Dados em tempo real</h4>
                    <p>Dashboards com métricas de entregas, presenças e uso de espaços sempre atualizados.</p>
                  </div>
                </div>

                <!-- Feature 4 -->
                <div class="diff-feature-item">
                  <div class="diff-icon-box">📋</div>
                  <div class="diff-feature-content">
                    <h4>Perfis especializados</h4>
                    <p>Coordenador, responsável pela biblioteca, eventos e espaços — cada um com acesso certo.</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Demo Float Cards -->
            <div class="diff-demo-cards">
              <div class="demo-card">
                <div class="demo-card-left">
                  <div class="demo-badge-icon badge-green">✓</div>
                  <div class="demo-card-text">
                    <h5>Tarefa entregue</h5>
                    <p>Matemática — 3º Ano A · agora mesmo</p>
                  </div>
                </div>
                <span class="status-dot status-green"></span>
              </div>

              <div class="demo-card">
                <div class="demo-card-left">
                  <div class="demo-badge-icon badge-blue">📅</div>
                  <div class="demo-card-text">
                    <h5>Feira de Ciências</h5>
                    <p>42/50 vagas · 15 de junho</p>
                  </div>
                </div>
                <span class="status-dot status-blue"></span>
              </div>

              <div class="demo-card">
                <div class="demo-card-left">
                  <div class="demo-badge-icon badge-orange">📍</div>
                  <div class="demo-card-text">
                    <h5>Quadra reservada</h5>
                    <p>Terça 14h–16h · Prof. Carlos</p>
                  </div>
                </div>
                <span class="status-dot status-orange"></span>
              </div>
            </div>

          </div>
        </div>
      </section>

      <!-- =====================================================================
           SECTION: PREÇOS
           ===================================================================== -->
      <section id="planos" class="pricing-section">
        <div class="container">
          <div class="section-header">
            <span class="section-tag teal">PREÇOS</span>
            <h2 class="section-title">Planos para cada necessidade</h2>
            <p class="section-subtitle">Comece grátis e escale conforme sua instituição crescer.</p>
          </div>

          <div class="pricing-grid">
            <!-- Card 1: Básico -->
            <div class="pricing-card">
              <h3 class="plan-name">Básico</h3>
              <p class="plan-desc">Ideal para começar</p>
              
              <div class="plan-price-wrap">
                <span class="plan-price">R$ 0</span>
                <span class="plan-period">grátis para sempre</span>
              </div>

              <ul class="plan-features">
                <li>Até 50 usuários</li>
                <li>2 turmas</li>
                <li>Tarefas e eventos</li>
                <li>Achados e perdidos</li>
                <li>Notificações básicas</li>
              </ul>

              <a href="<?php echo url('cadastro'); ?>" class="btn-plan-outline" style="display:block; text-align:center; text-decoration:none;">Começar grátis</a>
            </div>

            <!-- Card 2: Escolar (Popular Highlighted) -->
            <div class="pricing-card popular">
              <span class="popular-badge">Mais popular</span>
              <h3 class="plan-name">Escolar</h3>
              <p class="plan-desc">Para escolas de médio porte</p>

              <div class="plan-price-wrap">
                <span class="plan-price">R$ 149</span>
                <span class="plan-period">/mês</span>
              </div>

              <ul class="plan-features">
                <li>Até 500 usuários</li>
                <li>Turmas ilimitadas</li>
                <li>Todos os módulos</li>
                <li>Reserva de espaços</li>
                <li>Suporte prioritário</li>
                <li>Relatórios detalhados</li>
              </ul>

              <a href="<?php echo url('cadastro'); ?>" class="btn-plan-filled" style="display:block; text-align:center; text-decoration:none;">Assinar agora</a>
            </div>

            <!-- Card 3: Premium -->
            <div class="pricing-card">
              <h3 class="plan-name">Premium</h3>
              <p class="plan-desc">Para grandes instituições</p>

              <div class="plan-price-wrap">
                <span class="plan-price">R$ 349</span>
                <span class="plan-period">/mês</span>
              </div>

              <ul class="plan-features">
                <li>Usuários ilimitados</li>
                <li>Múltiplas unidades</li>
                <li>API personalizada</li>
                <li>Integração com sistemas</li>
                <li>SLA garantido</li>
                <li>Treinamento incluso</li>
              </ul>

              <a href="<?php echo url('cadastro'); ?>" class="btn-plan-outline" style="display:block; text-align:center; text-decoration:none;">Falar com vendas</a>
            </div>
          </div>
        </div>
      </section>

      <!-- =====================================================================
           SECTION: FAQ
           ===================================================================== -->
      <section id="faq" class="faq-section">
        <div class="container">
          <div class="section-header">
            <span class="section-tag">FAQ</span>
            <h2 class="section-title">Perguntas frequentes</h2>
            <p class="section-subtitle">Tire suas dúvidas sobre o ÂNCORA.</p>
          </div>

          <div class="faq-list">
            <!-- FAQ 1 -->
            <div class="faq-item">
              <button class="faq-question">
                <span>O ÂNCORA funciona em dispositivos móveis?</span>
                <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
              </button>
              <div class="faq-answer">
                <p>Sim! O ÂNCORA é uma plataforma web 100% responsiva que se adapta perfeitamente a smartphones, tablets e computadores.</p>
              </div>
            </div>

            <!-- FAQ 2 -->
            <div class="faq-item">
              <button class="faq-question">
                <span>Preciso instalar algum software?</span>
                <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
              </button>
              <div class="faq-answer">
                <p>Não. O sistema é totalmente online e roda direto no seu navegador de preferência, sem necessidade de nenhuma instalação.</p>
              </div>
            </div>

            <!-- FAQ 3 -->
            <div class="faq-item">
              <button class="faq-question">
                <span>Os dados ficam seguros?</span>
                <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
              </button>
              <div class="faq-answer">
                <p>Sim. Utilizamos criptografia de dados de ponta a ponta e estamos em conformidade total com as normas da LGPD.</p>
              </div>
            </div>

            <!-- FAQ 4 -->
            <div class="faq-item">
              <button class="faq-question">
                <span>Posso testar antes de assinar?</span>
                <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
              </button>
              <div class="faq-answer">
                <p>Com certeza! O plano Básico é 100% gratuito para sempre, permitindo testar todas as funcionalidades essenciais antes de realizar qualquer upgrade.</p>
              </div>
            </div>
          </div>

          <!-- =================================================================
               CTA BANNER CARD
               ================================================================= -->
          <div class="cta-banner-container">
            <div class="cta-banner-card">
              <div class="cta-badge-icon">⚓</div>
              <h3>Pronto para transformar sua escola?</h3>
              <p>Junte-se a centenas de instituições que já modernizaram sua gestão com o ÂNCORA.</p>

              <div class="cta-banner-buttons">
                <a href="<?php echo url('cadastro'); ?>" class="btn-hero-primary">
                  Criar Instituição grátis
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="<?php echo url('cadastro'); ?>" class="btn-hero-secondary">
                  Solicitar demonstração
                </a>
              </div>
            </div>
          </div>

        </div>
      </section>

    </div>
  </div>

  <!-- =========================================================================
       4. FOOTER
       ========================================================================= -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        
        <!-- Brand Column -->
        <div class="footer-brand">
          <a href="#" class="brand-logo">
            <img src="<?php echo $assetUrl; ?>images/logo.png" alt="ÂNCORA Logo">
          </a>
          <p>Ambiente de Navegação, Controle e Organização de Recursos Acadêmicos.</p>
        </div>

        <!-- Col 2: Plataforma -->
        <div>
          <h4 class="footer-col-title">Plataforma</h4>
          <ul class="footer-links">
            <li><a href="#funcionalidades">Funcionalidades</a></li>
            <li><a href="#planos">Planos e preços</a></li>
            <li><a href="#faq">FAQ</a></li>
          </ul>
        </div>

        <!-- Col 3: Acesso -->
        <div>
          <h4 class="footer-col-title">Acesso</h4>
          <ul class="footer-links">
            <li><a href="<?php echo url('login'); ?>">Entrar</a></li>
            <li><a href="<?php echo url('cadastro'); ?>">Criar Instituição</a></li>
          </ul>
        </div>

      </div>

      <!-- Copyright Bottom Bar -->
      <div class="footer-bottom">
        <span>© 2026 ÂNCORA. Todos os direitos reservados.</span>
        <span>Feito com ❤️ para educação brasileira</span>
      </div>
    </div>
  </footer>

  <!-- Script JS -->
  <script src="<?php echo $assetUrl; ?>js/home.js"></script>
</body>
</html>
