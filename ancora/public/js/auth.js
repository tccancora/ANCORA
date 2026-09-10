/**
 * ÂNCORA - AUTH PAGES INTERACTIVE SCRIPT (LOGIN & CADASTRO)
 * Handles Theme Sync, Password Visibility Toggle, and Form Validations
 */

document.addEventListener('DOMContentLoaded', () => {
  // ------------------------------------------------------------------------
  // 1. THEME SWITCHING SYSTEM (SHARED WITH HOME)
  // ------------------------------------------------------------------------
  const themeToggleBtn = document.getElementById('theme-toggle-btn');
  const themeIcon = document.getElementById('theme-icon');

  const sunIconSVG = `
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
    </svg>
  `;

  function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('ancora_theme', theme);
    if (themeIcon) {
      themeIcon.innerHTML = theme === 'dark' ? sunIconSVG : moonIconSVG;
    }
  }

  const savedTheme = localStorage.getItem('ancora_theme') || 'dark';
  setTheme(savedTheme);

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      setTheme(newTheme);
    });
  }

  // ------------------------------------------------------------------------
  // 2. TOGGLE PASSWORD VISIBILITY
  // ------------------------------------------------------------------------
  const togglePasswordBtns = document.querySelectorAll('.toggle-password-btn');

  const eyeOpenSVG = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
      <circle cx="12" cy="12" r="3"></circle>
    </svg>
  `;

  const eyeOffSVG = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
      <line x1="1" y1="1" x2="23" y2="23"></line>
    </svg>
  `;

  togglePasswordBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-target');
      const input = document.getElementById(targetId);
      if (!input) return;

      if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = eyeOffSVG;
      } else {
        input.type = 'password';
        btn.innerHTML = eyeOpenSVG;
      }
    });
  });

  // ------------------------------------------------------------------------
  // 3. FORM VALIDATION
  // ------------------------------------------------------------------------
  const alertBox = document.getElementById('auth-alert');

  function showAlert(msg) {
    if (alertBox) {
      alertBox.textContent = msg;
      alertBox.classList.add('error');
      alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  function hideAlert() {
    if (alertBox) {
      alertBox.classList.remove('error');
      alertBox.textContent = '';
    }
  }

  // Validate Login Form
  const formLogin = document.getElementById('form-login');
  if (formLogin) {
    formLogin.addEventListener('submit', (e) => {
      hideAlert();
      const email = document.getElementById('email')?.value.trim();
      const senha = document.getElementById('senha')?.value.trim();

      if (!email || !senha) {
        e.preventDefault();
        showAlert('Por favor, preencha todos os campos obrigatórios.');
        return;
      }

      if (!email.includes('@') || !email.includes('.')) {
        e.preventDefault();
        showAlert('Por favor, informe um endereço de e-mail válido.');
        return;
      }
    });
  }

  // Validate Cadastro Form
  const formCadastro = document.getElementById('form-cadastro');
  if (formCadastro) {
    formCadastro.addEventListener('submit', (e) => {
      hideAlert();
      const instituicao = document.getElementById('nome_instituicao')?.value.trim();
      const responsavel = document.getElementById('nome_responsavel')?.value.trim();
      const email = document.getElementById('email')?.value.trim();
      const senha = document.getElementById('senha')?.value.trim();
      const confirmarSenha = document.getElementById('confirmar_senha')?.value.trim();

      if (!instituicao || !responsavel || !email || !senha || !confirmarSenha) {
        e.preventDefault();
        showAlert('Por favor, preencha todos os campos obrigatórios.');
        return;
      }

      if (!email.includes('@') || !email.includes('.')) {
        e.preventDefault();
        showAlert('Por favor, informe um e-mail válido.');
        return;
      }

      if (senha.length < 6) {
        e.preventDefault();
        showAlert('A senha deve possuir no mínimo 6 caracteres.');
        return;
      }

      if (senha !== confirmarSenha) {
        e.preventDefault();
        showAlert('A confirmação de senha não confere com a senha digitada.');
        return;
      }
    });
  }
});
