/* ============================================================
   LOGIN PAGE JS — login.js
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  // ── Show / Hide Password Toggles ──
  document.querySelectorAll('.toggle-pw').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.closest('.login-input-wrap').querySelector('.login-input');
      const icon  = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    });
  });

  // ── Login Form Submissions ──
  document.querySelectorAll('.login-form').forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();
      const btn      = form.querySelector('.login-btn');
      const role     = form.dataset.role;
      const original = btn.innerHTML;

      // Extract form data
      const inputs = form.querySelectorAll('.login-input');
      const username = inputs[0].value.trim();
      const password = inputs[1].value.trim();

      // Validate
      let valid = true;
      inputs.forEach(input => {
        if (!input.value.trim()) {
          valid = false;
          input.style.borderColor = '#FF5252';
          input.addEventListener('input', () => { input.style.borderColor = ''; }, { once: true });
        }
      });
      if (!valid) {
        shakeCard(form.closest('.login-card'));
        return;
      }

      // Loading state
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying…';
      btn.disabled  = true;

      // Prepare form data
      const formData = new FormData();
      formData.append('username', username);
      formData.append('password', password);
      formData.append('role', role);

      // Send to backend
      fetch('login_action.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          btn.innerHTML = '<i class="fas fa-check-circle"></i> Redirecting…';
          setTimeout(() => {
            window.location.href = data.redirect;
          }, 600);
        } else {
          // Authentication failed
          btn.innerHTML = original;
          btn.disabled = false;
          shakeCard(form.closest('.login-card'));
          showToast(data.message || 'Login failed.', 'error');
        }
      })
      .catch(err => {
        btn.innerHTML = original;
        btn.disabled = false;
        showToast('A network error occurred. Please try again.', 'error');
        console.error(err);
      });
    });
  });

  // ── Shake animation on failed login ──
  function shakeCard(card) {
    card.style.animation = 'none';
    card.offsetHeight; // reflow
    card.style.animation = 'shake 0.5s ease';
    card.addEventListener('animationend', () => { card.style.animation = ''; }, { once: true });
  }

  // Inject shake keyframe
  const style = document.createElement('style');
  style.textContent = `
    @keyframes shake {
      0%,100%{transform:translateX(0) translateY(-8px)}
      20%{transform:translateX(-8px) translateY(-8px)}
      40%{transform:translateX(8px)  translateY(-8px)}
      60%{transform:translateX(-6px) translateY(-8px)}
      80%{transform:translateX(6px)  translateY(-8px)}
    }
  `;
  document.head.appendChild(style);

  // ── Forgot Password ──
  document.querySelectorAll('.forgot-link').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const card = link.closest('.login-card');
      const title = card.querySelector('.card-title').textContent;
      showToast(`Password reset link will be sent for ${title.trim()}.`, 'info');
    });
  });

  // ── Toast Notification ──
  function showToast(message, type = 'info') {
    const colors = { info: '#1565C0', success: '#43A047', error: '#E53935' };
    const toast = document.createElement('div');
    toast.style.cssText = `
      position:fixed;bottom:30px;left:50%;transform:translateX(-50%) translateY(20px);
      background:${colors[type]};color:white;padding:14px 28px;border-radius:50px;
      font-size:0.875rem;font-weight:600;z-index:9999;
      box-shadow:0 8px 32px rgba(0,0,0,0.25);
      transition:all 0.35s ease;opacity:0;font-family:'Inter',sans-serif;
      display:flex;align-items:center;gap:10px;
    `;
    toast.innerHTML = `<i class="fas fa-info-circle"></i>${message}`;
    document.body.appendChild(toast);
    requestAnimationFrame(() => {
      toast.style.opacity = '1';
      toast.style.transform = 'translateX(-50%) translateY(0)';
    });
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(-50%) translateY(20px)';
      setTimeout(() => toast.remove(), 400);
    }, 3500);
  }

});
