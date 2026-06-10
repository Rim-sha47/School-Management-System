/* ============================================================
   ADMIN DASHBOARD JS — admin.js
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initCharts();
  initCalendar();
  initCounterWidgets();
});

// Make sidebar toggle work on mobile
function initSidebar() {
  const sidebar  = document.getElementById('sidebar');
  const topbar   = document.getElementById('topbar');
  const content  = document.getElementById('mainContent');
  const toggleBtn = document.getElementById('sidebarToggle');
  const overlay   = document.getElementById('sidebarOverlay');

  const isMobile = () => window.innerWidth < 992;

  function updateLayout(collapsed) {
    if (isMobile()) {
      sidebar.classList.toggle('mobile-open', !collapsed);
      if (overlay) overlay.classList.toggle('active', !collapsed);
      if (topbar) {
        topbar.classList.add('sidebar-mobile');
        topbar.classList.remove('sidebar-collapsed');
      }
      if (content) {
        content.classList.add('sidebar-mobile');
        content.classList.remove('sidebar-collapsed');
      }
    } else {
      sidebar.classList.toggle('collapsed', collapsed);
      sidebar.classList.remove('mobile-open');
      if (overlay) overlay.classList.remove('active');
      if (topbar) {
        topbar.classList.toggle('sidebar-collapsed', collapsed);
        topbar.classList.remove('sidebar-mobile');
      }
      if (content) {
        content.classList.toggle('sidebar-collapsed', collapsed);
        content.classList.remove('sidebar-mobile');
      }
    }
  }

  let collapsed = false;
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      collapsed = !collapsed;
      updateLayout(collapsed);
    });
  }
  if (overlay) {
    overlay.addEventListener('click', () => {
      collapsed = true;
      updateLayout(collapsed);
    });
  }

  window.addEventListener('resize', () => {
    if (!isMobile()) {
      sidebar.classList.remove('mobile-open');
      if (overlay) overlay.classList.remove('active');
      if (topbar) topbar.classList.remove('sidebar-mobile');
      if (content) content.classList.remove('sidebar-mobile');
    }
  });
}

/* ── Chart.js Charts ── */
function initCharts() {
  Chart.defaults.font.family = "'Inter', sans-serif";
  Chart.defaults.color = '#64748B';

  // 1. Monthly Attendance Line Chart
  const attCtx = document.getElementById('attendanceChart');
  if (attCtx) {
    new Chart(attCtx, {
      type: 'line',
      data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
          label: 'Attendance %',
          data: [92, 88, 94, 91, 87, 95, 89, 93, 96, 90, 94, 92],
          borderColor: '#1565C0',
          backgroundColor: 'rgba(21,101,192,0.08)',
          tension: 0.45,
          fill: true,
          pointBackgroundColor: '#1565C0',
          pointRadius: 5,
          pointHoverRadius: 7,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#1565C0',
            titleColor: '#fff',
            bodyColor: 'rgba(255,255,255,0.85)',
            padding: 12,
            cornerRadius: 10,
            callbacks: { label: ctx => ` ${ctx.parsed.y}%` }
          }
        },
        scales: {
          y: { min: 75, max: 100, grid: { color: 'rgba(0,0,0,0.05)' },
               ticks: { callback: v => v + '%' } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  // 2. Fee Collection Doughnut Chart
  const feeCtx = document.getElementById('feeChart');
  if (feeCtx) {
    new Chart(feeCtx, {
      type: 'doughnut',
      data: {
        labels: ['Paid', 'Partial', 'Overdue'],
        datasets: [{
          data: [68, 22, 10],
          backgroundColor: ['#43A047','#FB8C00','#E53935'],
          borderWidth: 0,
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { padding: 20, usePointStyle: true, pointStyleWidth: 10 }
          },
          tooltip: {
            backgroundColor: '#0D47A1',
            titleColor: '#fff',
            bodyColor: 'rgba(255,255,255,0.85)',
            padding: 12,
            cornerRadius: 10,
            callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}%` }
          }
        }
      }
    });
  }

  // 3. Exam Performance Bar Chart
  const examCtx = document.getElementById('examChart');
  if (examCtx) {
    new Chart(examCtx, {
      type: 'bar',
      data: {
        labels: ['Class 6','Class 7','Class 8','Class 9','Class 10','Class 11','Class 12'],
        datasets: [
          {
            label: 'Term 1', data: [78, 82, 76, 88, 85, 79, 91],
            backgroundColor: 'rgba(21,101,192,0.75)', borderRadius: 6, borderSkipped: false
          },
          {
            label: 'Term 2', data: [82, 79, 84, 85, 88, 83, 89],
            backgroundColor: 'rgba(0,188,212,0.75)', borderRadius: 6, borderSkipped: false
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top', labels: { usePointStyle: true } },
          tooltip: {
            backgroundColor: '#0D47A1',
            titleColor: '#fff',
            bodyColor: 'rgba(255,255,255,0.85)',
            padding: 12, cornerRadius: 10
          }
        },
        scales: {
          y: { beginAtZero: false, min: 60, max: 100, grid: { color: 'rgba(0,0,0,0.04)' },
               ticks: { callback: v => v + '%' } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  // 4. Gender Distribution Pie Chart
  const genderCtx = document.getElementById('genderChart');
  if (genderCtx) {
    new Chart(genderCtx, {
      type: 'pie',
      data: {
        labels: ['Male', 'Female'],
        datasets: [{
          data: [54, 46],
          backgroundColor: ['#1565C0','#E91E63'],
          borderWidth: 0, hoverOffset: 6
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
          tooltip: {
            backgroundColor: '#0D47A1',
            titleColor: '#fff',
            bodyColor: 'rgba(255,255,255,0.85)',
            padding: 12, cornerRadius: 10,
            callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}%` }
          }
        }
      }
    });
  }
}

/* ── Mini Calendar ── */
function initCalendar() {
  const calGrid = document.getElementById('calGrid');
  const calMonthLabel = document.getElementById('calMonth');
  if (!calGrid) return;

  let today = new Date();
  let current = new Date(today.getFullYear(), today.getMonth(), 1);
  const eventDays = [5, 12, 18, 25]; // Example event days

  function renderCalendar() {
    const year  = current.getFullYear();
    const month = current.getMonth();
    const first = new Date(year, month, 1).getDay();
    const days  = new Date(year, month + 1, 0).getDate();
    const months = ['January','February','March','April','May','June',
                    'July','August','September','October','November','December'];
    calMonthLabel.textContent = `${months[month]} ${year}`;

    const dayLabels = ['S','M','T','W','T','F','S'];
    let html = dayLabels.map(d => `<div class="cal-header">${d}</div>`).join('');

    for (let i = 0; i < first; i++) {
      html += `<div class="cal-day other-month"></div>`;
    }
    for (let d = 1; d <= days; d++) {
      const isToday = d === today.getDate() && month === today.getMonth() && year === today.getFullYear();
      const hasEvt  = eventDays.includes(d);
      html += `<div class="cal-day${isToday ? ' today' : ''}${hasEvt ? ' has-event' : ''}">${d}</div>`;
    }
    calGrid.innerHTML = html;
  }

  renderCalendar();
  document.getElementById('calPrev')?.addEventListener('click', () => {
    current.setMonth(current.getMonth() - 1);
    renderCalendar();
  });
  document.getElementById('calNext')?.addEventListener('click', () => {
    current.setMonth(current.getMonth() + 1);
    renderCalendar();
  });
}

/* ── Counter Widgets ── */
function initCounterWidgets() {
  const widgets = document.querySelectorAll('[data-count]');
  widgets.forEach(w => {
    const target = parseInt(w.dataset.count, 10);
    const suffix = w.dataset.suffix || '';
    let current  = 0;
    const step   = target / 60;
    const timer  = setInterval(() => {
      current += step;
      if (current >= target) { current = target; clearInterval(timer); }
      w.textContent = Math.floor(current).toLocaleString() + suffix;
    }, 20);
  });
}
