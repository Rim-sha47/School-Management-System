<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

try {
    $stmt = $pdo->prepare("SELECT s.*, u.username FROM students s JOIN users u ON s.user_id = u.id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch();
} catch(PDOException $e) {
    $student = null;
}
$pageTitle = "Student Dashboard";
$searchPlaceholder = "Search notices, assignments...";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Student Dashboard";
  $searchPlaceholder = "Search notices, assignments...";
  include 'includes/header.php'; ?>
</head>
<body class="dashboard-body">
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/topbar.php'; ?>

  <main class="main-content" id="mainContent">
    <div class="page-header">
      <h1>Student Dashboard</h1>
      <p class="text-muted mt-1 mb-0">Hello <?php echo htmlspecialchars($student['first_name'] ?? 'Student'); ?>, here is your academic overview.</p>
    </div>

    <!-- Stats Widgets -->
    <div class="row g-4 mb-4">
      <div class="col-xl-3 col-md-6">
        <div class="widget-card blue">
          <div class="widget-icon"><i class="fas fa-calendar-check"></i></div>
          <div class="widget-info">
            <h2>92%</h2>
            <p>Overall Attendance</p>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="widget-card cyan">
          <div class="widget-icon"><i class="fas fa-trophy"></i></div>
          <div class="widget-info">
            <h2>88%</h2>
            <p>Average Score</p>
          </div>
          <div class="widget-trend up"><i class="fas fa-arrow-up"></i> Top 5</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="widget-card orange">
          <div class="widget-icon"><i class="fas fa-tasks"></i></div>
          <div class="widget-info">
            <h2>2</h2>
            <p>Pending Assignments</p>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="widget-card green">
          <div class="widget-icon"><i class="fas fa-file-invoice-dollar"></i></div>
          <div class="widget-info">
            <h2 style="font-size: 1.5rem;" class="mt-2 text-success">Cleared</h2>
            <p>Fee Status</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
      <div class="col-lg-5">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-spider"></i> Subject Performance</h5>
          </div>
          <div class="dash-card-body">
            <div class="chart-container" style="height: 250px;">
              <canvas id="resultsChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-chart-bar"></i> My Attendance Record</h5>
          </div>
          <div class="dash-card-body">
            <div class="chart-container" style="height: 250px;">
              <canvas id="studentAttendanceChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Row -->
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-book"></i> Course Syllabus Progress</h5>
          </div>
          <div class="dash-card-body p-0">
            <table class="dash-table">
              <tbody>
                <tr>
                  <td width="30%"><strong>Mathematics</strong></td>
                  <td width="60%">
                    <div class="progress-custom">
                      <div class="progress-fill" data-width="75%" style="background: var(--primary);"></div>
                    </div>
                  </td>
                  <td width="10%" class="text-end fw-bold">75%</td>
                </tr>
                <tr>
                  <td><strong>Science</strong></td>
                  <td>
                    <div class="progress-custom">
                      <div class="progress-fill" data-width="60%" style="background: var(--accent);"></div>
                    </div>
                  </td>
                  <td class="text-end fw-bold">60%</td>
                </tr>
                <tr>
                  <td><strong>English</strong></td>
                  <td>
                    <div class="progress-custom">
                      <div class="progress-fill" data-width="85%" style="background: var(--success);"></div>
                    </div>
                  </td>
                  <td class="text-end fw-bold">85%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-clipboard-check"></i> Pending Assignments</h5>
          </div>
          <div class="dash-card-body p-3">
            <div class="event-item">
              <div class="event-date-box" style="background: rgba(229,57,53,0.1);">
                <div class="day" style="color: var(--danger);">10</div>
                <div class="month">Jun</div>
              </div>
              <div class="event-info flex-grow-1">
                <h6>Mathematics - Algebra Sheet</h6>
                <p>Complete exercises 5.1 and 5.2</p>
              </div>
              <button class="btn btn-sm btn-outline-danger align-self-center">Upload</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../assets/js/student.js"></script>
  <script>
    initSidebar();
    Chart.defaults.font.family = "'Inter', sans-serif";
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
    const attCtx = document.getElementById('studentAttendanceChart');
    if (attCtx) {
      new Chart(attCtx, {
        type: 'bar',
        data: {
          labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
          datasets: [
            { label: 'Present', data: [20,18,22,19,21,23,20,22,21,20,22,19], backgroundColor: 'rgba(67,160,71,0.8)', borderRadius: 6, borderSkipped: false },
            { label: 'Absent', data: [2,4,0,3,1,0,2,1,1,2,0,3], backgroundColor: 'rgba(229,57,53,0.7)', borderRadius: 6, borderSkipped: false }
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
  </script>
</body>
</html>