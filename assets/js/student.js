/* ============================================================
   STUDENT DASHBOARD JS — student.js
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initStudentCharts();
  initProgressBars();
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

function initStudentCharts() {
  Chart.defaults.font.family = "'Inter', sans-serif";

  // Exam Results Radar Chart
  const radarCtx = document.getElementById('resultsChart');
  if (radarCtx) {
    new Chart(radarCtx, {
      type: 'radar',
      data: {
        labels: ['Mathematics','Science','English','Urdu','Computer','Islamiyat','Social Studies'],
        datasets: [{
          label: 'Marks %',
          data: [88, 76, 92, 85, 94, 80, 78],
          backgroundColor: 'rgba(21,101,192,0.15)',
          borderColor: '#1565C0',
          pointBackgroundColor: '#1565C0',
          pointRadius: 5,
          pointHoverRadius: 7
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        scales: { r: { beginAtZero: false, min: 50, max: 100, ticks: { stepSize: 10, font: { size: 10 } } } },
        plugins: { legend: { display: false } }
      }
    });
  }

  // Monthly Attendance Line Chart
  const attCtx = document.getElementById('studentAttendanceChart');
  if (attCtx) {
    new Chart(attCtx, {
      type: 'bar',
      data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [
          { label: 'Present', data: [20,18,22,19,21,23,20,22,21,20,22,19],
            backgroundColor: 'rgba(67,160,71,0.8)', borderRadius: 6, borderSkipped: false },
          { label: 'Absent',  data: [2,4,0,3,1,0,2,1,1,2,0,3],
            backgroundColor: 'rgba(229,57,53,0.7)', borderRadius: 6, borderSkipped: false }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { usePointStyle: true } } },
        scales: {
          x: { stacked: true, grid: { display: false } },
          y: { stacked: true, grid: { color: 'rgba(0,0,0,0.04)' } }
        }
      }
    });
  }
}

function initProgressBars() {
  const bars = document.querySelectorAll('.progress-fill[data-width]');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.width = e.target.dataset.width;
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.5 });
  bars.forEach(b => { b.style.width = '0%'; observer.observe(b); });
}
