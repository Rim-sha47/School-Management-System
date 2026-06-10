<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

try {
    $stmt = $pdo->prepare("SELECT t.*, u.username FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $teacher = $stmt->fetch();
} catch(PDOException $e) {
    $teacher = null;
}
$pageTitle = "Teacher Dashboard";
$searchPlaceholder = "Search students in my classes...";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/header.php'; ?>
  <style>
    .att-btn {
      width: 32px; height: 32px;
      border-radius: 50%;
      border: none;
      display: inline-flex; align-items: center; justify-content: center;
      color: white; font-size: 0.8rem;
      margin-right: 6px;
      opacity: 0.5;
      transition: all 0.2s;
    }
    .att-btn:hover { opacity: 0.8; transform: scale(1.1); }
    .att-btn.active { opacity: 1; transform: scale(1.1); box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
    .att-btn.p { background: var(--success); }
    .att-btn.a { background: var(--danger); }
    .att-btn.l { background: var(--warning); }
  </style>
</head>
<body class="dashboard-body">
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/topbar.php'; ?>

  <main class="main-content" id="mainContent">
    <div class="page-header">
      <h1>Teacher Dashboard</h1>
      <p class="text-muted mt-1 mb-0">Welcome back, <?php echo htmlspecialchars($teacher['first_name'] ?? 'Teacher'); ?>. Here is your summary for today.</p>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-xl-3 col-md-6">
        <div class="widget-card blue">
          <div class="widget-icon"><i class="fas fa-users"></i></div>
          <div class="widget-info">
            <h2>5</h2>
            <p>My Classes</p>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="widget-card cyan">
          <div class="widget-icon"><i class="fas fa-user-graduate"></i></div>
          <div class="widget-info">
            <h2>185</h2>
            <p>Total Students</p>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="widget-card orange">
          <div class="widget-icon"><i class="fas fa-tasks"></i></div>
          <div class="widget-info">
            <h2>12</h2>
            <p>Pending Assignments</p>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="widget-card green">
          <div class="widget-icon"><i class="fas fa-poll"></i></div>
          <div class="widget-info">
            <h2>82%</h2>
            <p>Average Results</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-7">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-clipboard-list"></i> Quick Attendance (Class 10-A)</h5>
            <button class="btn btn-sm btn-primary" id="submitAttendance">Submit</button>
          </div>
          <div class="dash-card-body p-0">
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Roll No</th>
                  <th>Student Name</th>
                  <th>Status</th>
                  <th>Mark</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>10A-01</td>
                  <td><div class="avatar-sm">AK</div> Ali Khan</td>
                  <td><span class="status-badge present att-status">Present</span></td>
                  <td>
                    <button class="att-btn p active" data-status="present" title="Present"><i class="fas fa-check"></i></button>
                    <button class="att-btn a" data-status="absent" title="Absent"><i class="fas fa-times"></i></button>
                    <button class="att-btn l" data-status="leave" title="Leave"><i class="fas fa-envelope"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>10A-02</td>
                  <td><div class="avatar-sm" style="background:var(--warning);">SA</div> Sara Ahmed</td>
                  <td><span class="status-badge absent att-status">Absent</span></td>
                  <td>
                    <button class="att-btn p" data-status="present" title="Present"><i class="fas fa-check"></i></button>
                    <button class="att-btn a active" data-status="absent" title="Absent"><i class="fas fa-times"></i></button>
                    <button class="att-btn l" data-status="leave" title="Leave"><i class="fas fa-envelope"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="dash-card mb-4">
          <div class="dash-card-header">
            <h5><i class="fas fa-chart-pie"></i> Assignment Completion</h5>
          </div>
          <div class="dash-card-body">
            <div class="chart-container" style="height: 180px;">
              <canvas id="assignmentChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-calendar-day"></i> Today's Timetable</h5>
          </div>
          <div class="dash-card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="timetable-cell blue">
                  <div class="subject">Mathematics (Class 10-A)</div>
                  <div class="time"><i class="far fa-clock"></i> 08:30 AM - 09:15 AM</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="timetable-cell cyan">
                  <div class="subject">Physics (Class 11-Pre Eng)</div>
                  <div class="time"><i class="far fa-clock"></i> 09:20 AM - 10:05 AM</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-bullhorn"></i> Staff Notice Board</h5>
          </div>
          <div class="dash-card-body p-3">
            <div class="notice-item">
              <div class="notice-dot urgent"></div>
              <div>
                <h6>Submission of Term Results</h6>
                <p>All teachers must submit Term 1 results by Friday EOD.</p>
                <div class="notice-date">2 hours ago</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../assets/js/teacher.js"></script>
  <script>
    Chart.defaults.font.family = "'Inter', sans-serif";
    initSidebar();
    const ctx = document.getElementById('assignmentChart');
    if (ctx) {
      new Chart(ctx, {
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
  </script>
</body>
</html>