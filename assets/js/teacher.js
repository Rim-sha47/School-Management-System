/* ============================================================
    TEACHER DASHBOARD JS — teacher.js
    ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  
  if (typeof initTeacherCharts === 'function' && typeof Chart !== 'undefined') {
    initTeacherCharts();
  }
  
  initAttendanceMarker();
  initProfileEdit();
  initTableActions();
});

function initSidebar() {
  const sidebar   = document.getElementById('sidebar');
  const topbar    = document.getElementById('topbar');
  const content   = document.getElementById('mainContent');
  const toggleBtn = document.getElementById('sidebarToggle');
  const overlay   = document.getElementById('sidebarOverlay');
  const isMobile  = () => window.innerWidth < 992;
  let   collapsed = false;

  function updateLayout(c) {
    if (isMobile()) {
      sidebar.classList.toggle('mobile-open', !c);
      if (overlay) overlay.classList.toggle('active', !c);
      if (topbar) {
        topbar.classList.add('sidebar-mobile');
        topbar.classList.remove('sidebar-collapsed');
      }
      if (content) {
        content.classList.add('sidebar-mobile');
        content.classList.remove('sidebar-collapsed');
      }
    } else {
      sidebar.classList.toggle('collapsed', c);
      sidebar.classList.remove('mobile-open');
      if (overlay) overlay.classList.remove('active');
      if (topbar) {
        topbar.classList.toggle('sidebar-collapsed', c);
        topbar.classList.remove('sidebar-mobile');
      }
      if (content) {
        content.classList.toggle('sidebar-collapsed', c);
        content.classList.remove('sidebar-mobile');
      }
    }
  }
  toggleBtn?.addEventListener('click', () => { collapsed = !collapsed; updateLayout(collapsed); });
  overlay?.addEventListener('click',   () => { collapsed = true;       updateLayout(collapsed); });

  window.addEventListener('resize', () => {
    if (!isMobile()) {
      sidebar.classList.remove('mobile-open');
      if (overlay) overlay.classList.remove('active');
      if (topbar) topbar.classList.remove('sidebar-mobile');
      if (content) content.classList.remove('sidebar-mobile');
    }
  });
}

function initTeacherCharts() {
  if (typeof Chart === 'undefined') return;
  
  Chart.defaults.font.family = "'Inter', sans-serif";

  // Class Attendance Radar Chart
  const radarCtx = document.getElementById('classAttendanceChart');
  if (radarCtx) {
    new Chart(radarCtx, {
      type: 'radar',
      data: {
        labels: ['Class 9A','Class 10B','Class 11C','Class 12A','Class 8B'],
        datasets: [{
          label: 'Attendance %',
          data: [88, 92, 85, 94, 90],
          backgroundColor: 'rgba(21,101,192,0.15)',
          borderColor: '#1565C0',
          pointBackgroundColor: '#1565C0',
          pointRadius: 5
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        scales: { r: { beginAtZero: false, min: 70, max: 100, ticks: { stepSize: 10 } } },
        plugins: { legend: { display: false } }
      }
    });
  }

  // Assignment Completion Bar
  const assignCtx = document.getElementById('assignmentChart');
  if (assignCtx) {
    new Chart(assignCtx, {
      type: 'doughnut',
      data: {
        labels: ['Submitted', 'Pending'],
        datasets: [{
          data: [68, 32],
          backgroundColor: ['#43A047', '#E53935'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        cutout: '70%',
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } }
      }
    });
  }
}

function initAttendanceMarker() {
  const btns = document.querySelectorAll('.att-btn');
  btns.forEach(btn => {
    btn.addEventListener('click', () => {
      const row    = btn.closest('tr');
      const status = btn.dataset.status;
      row.querySelectorAll('.att-btn').forEach(b => {
        b.classList.remove('active');
        b.style.opacity = '0.5';
      });
      btn.classList.add('active');
      btn.style.opacity = '1';
      const badge = row.querySelector('.att-status');
      if (badge) {
        badge.className = 'status-badge ' + status;
        badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
      }
    });
  });

  const submitBtn = document.getElementById('submitAttendance');
  if (submitBtn) {
    submitBtn.addEventListener('click', () => {
      showToast('Attendance saved successfully!', '#43A047');
    });
  }

  function showToast(msg, color) {
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:28px;right:28px;background:${color};color:#fff;
      padding:14px 24px;border-radius:12px;font-size:.875rem;font-weight:600;
      z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,.2);font-family:'Inter',sans-serif;
      animation:fadeInUp .3s ease;display:flex;align-items:center;gap:10px;`;
    t.innerHTML = `<i class="fas fa-check-circle"></i>${msg}`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
  }
}

function initProfileEdit() {
  const editBtn = document.getElementById('editProfileBtn');
  const cancelBtn = document.getElementById('cancelEdit');
  const saveBtn = document.getElementById('saveProfileBtn');
  const formInputs = document.querySelectorAll('#profileForm .form-control');

  if (editBtn) {
    editBtn.addEventListener('click', () => {
      document.getElementById('profileSection').classList.add('editing');
      formInputs.forEach(input => input.removeAttribute('readonly'));
      if (saveBtn) saveBtn.style.display = 'inline-block';
      if (cancelBtn) cancelBtn.style.display = 'inline-block';
      editBtn.style.display = 'none';
    });
  }

  const cancelEditBtn = document.getElementById('cancelEditBtn');
  if (cancelEditBtn) {
    cancelEditBtn.addEventListener('click', () => {
      document.getElementById('profileSection')?.classList.remove('editing');
      formInputs.forEach(input => input.setAttribute('readonly', true));
      if (saveBtn) saveBtn.style.display = 'none';
      if (cancelEditBtn) cancelEditBtn.style.display = 'none';
      if (editBtn) editBtn.style.display = 'inline-block';
    });
  }

  const form = document.getElementById('profileForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      showToast('Profile updated successfully!', '#43A047');
      formInputs.forEach(input => input.setAttribute('readonly', true));
      if (saveBtn) saveBtn.style.display = 'none';
      if (cancelEditBtn) cancelEditBtn.style.display = 'none';
      if (editBtn) editBtn.style.display = 'inline-block';
    });
  }
}

function initTableActions() {
  // Edit buttons
  document.querySelectorAll('[data-bs-target*="EditModal"], .btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
      if (typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(document.querySelector(this.dataset.bsTarget || this.getAttribute('data-bs-target')));
        if (modal) modal.show();
      }
    });
  });

  // Delete/Trash buttons
  document.querySelectorAll('.btn-outline-danger').forEach(btn => {
    btn.addEventListener('click', function(e) {
      if (this.innerHTML.includes('fa-trash') || this.innerHTML.includes('fa-times')) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this item?')) {
          const row = this.closest('tr');
          if (row) {
            row.remove();
            showToast('Item deleted successfully!', '#E53935');
          }
        }
      }
    });
  });
}

function showToast(msg, color) {
  const t = document.createElement('div');
  t.style.cssText = `position:fixed;bottom:28px;right:28px;background:${color};color:#fff;
    padding:14px 24px;border-radius:12px;font-size:.875rem;font-weight:600;
    z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,.2);font-family:'Inter',sans-serif;
    animation:fadeInUp .3s ease;display:flex;align-items:center;gap:10px;`;
  t.innerHTML = `<i class="fas fa-check-circle"></i>${msg}`;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3000);
}