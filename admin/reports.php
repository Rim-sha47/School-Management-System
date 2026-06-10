<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

try {
    // 1. Students Count by Class
    $classStudentsStmt = $pdo->query("
        SELECT c.name AS class_name, COUNT(s.id) AS student_count 
        FROM classes c 
        LEFT JOIN students s ON c.id = s.class_id AND s.status = 'active'
        GROUP BY c.id 
        ORDER BY c.numeric_name ASC
    ");
    $classStudentsData = $classStudentsStmt->fetchAll();
    
    $classNames = [];
    $studentCounts = [];
    foreach($classStudentsData as $row) {
        $classNames[] = $row['class_name'];
        $studentCounts[] = intval($row['student_count']);
    }

    // 2. Attendance Rates by Class
    $attendanceRateStmt = $pdo->query("
        SELECT c.name AS class_name, 
               COUNT(a.id) AS total_marked,
               SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_count
        FROM classes c 
        JOIN attendance a ON c.id = a.class_id
        GROUP BY c.id 
        ORDER BY c.numeric_name ASC
    ");
    $attendanceRatesData = $attendanceRateStmt->fetchAll();
    
    $attClassNames = [];
    $attPercentages = [];
    foreach($attendanceRatesData as $row) {
        $attClassNames[] = $row['class_name'];
        $pct = $row['total_marked'] > 0 ? round(($row['present_count'] / $row['total_marked']) * 100) : 0;
        $attPercentages[] = $pct;
    }

    // 3. Fee Collection Breakdown
    $feeBreakdownStmt = $pdo->query("
        SELECT status, SUM(total_paid) as collected, COUNT(*) as count 
        FROM fee_payments 
        GROUP BY status
    ");
    $feeBreakdownData = $feeBreakdownStmt->fetchAll();
    
    $feeStatuses = [];
    $feeTotals = [];
    foreach($feeBreakdownData as $row) {
        $feeStatuses[] = ucfirst($row['status']);
        $feeTotals[] = floatval($row['collected'] ?? 0);
    }
} catch(PDOException $e) {
    $error = "Error generating reports: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "System Reports";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search page reports...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header">
      <h1>Analytical Reports</h1>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Reports</li>
      </ol>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Charts Layout Grid -->
    <div class="row g-4 mb-4">
      <!-- Students Count Chart -->
      <div class="col-lg-8">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-user-graduate"></i> Student Enrollment by Class</h5>
          </div>
          <div class="dash-card-body">
            <div class="chart-container" style="height: 320px; position: relative;">
              <canvas id="reportsStudentChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Fees Collection breakdown Chart -->
      <div class="col-lg-4">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-wallet"></i> Fee Status Distribution</h5>
          </div>
          <div class="dash-card-body d-flex flex-column justify-content-center">
            <div class="chart-container" style="height: 250px; position: relative;">
              <canvas id="reportsFeeChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Attendance Chart -->
      <div class="col-12">
        <div class="dash-card">
          <div class="dash-card-header">
            <h5><i class="fas fa-calendar-check"></i> Average Present Rate by Class (%)</h5>
          </div>
          <div class="dash-card-body">
            <div class="chart-container" style="height: 300px; position: relative;">
              <canvas id="reportsAttendanceChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../assets/js/admin.js"></script>
  
  <script>
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748B';

    // 1. Students by Class Bar Chart
    const classNames = <?php echo json_encode($classNames); ?>;
    const studentCounts = <?php echo json_encode($studentCounts); ?>;
    
    new Chart(document.getElementById('reportsStudentChart'), {
      type: 'bar',
      data: {
        labels: classNames,
        datasets: [{
          label: 'Enrolled Students',
          data: studentCounts,
          backgroundColor: 'rgba(21, 101, 192, 0.85)',
          borderRadius: 6,
          borderSkipped: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { 
            beginAtZero: true, 
            grid: { color: 'rgba(0,0,0,0.04)' } 
          },
          x: { grid: { display: false } }
        }
      }
    });

    // 2. Fees collection status pie chart
    const feeStatuses = <?php echo json_encode($feeStatuses); ?>;
    const feeTotals = <?php echo json_encode($feeTotals); ?>;

    new Chart(document.getElementById('reportsFeeChart'), {
      type: 'pie',
      data: {
        labels: feeStatuses,
        datasets: [{
          data: feeTotals,
          backgroundColor: ['#43A047', '#E53935', '#FB8C00'],
          borderWidth: 0,
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } }
        }
      }
    });

    // 3. Attendance Rates bar chart
    const attClassNames = <?php echo json_encode($attClassNames); ?>;
    const attPercentages = <?php echo json_encode($attPercentages); ?>;

    new Chart(document.getElementById('reportsAttendanceChart'), {
      type: 'line',
      data: {
        labels: attClassNames.length ? attClassNames : ['None'],
        datasets: [{
          label: 'Average Present Rate',
          data: attPercentages.length ? attPercentages : [0],
          borderColor: '#00ACC1',
          backgroundColor: 'rgba(0,172,193,0.08)',
          tension: 0.35,
          fill: true,
          pointBackgroundColor: '#00ACC1',
          pointRadius: 6,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { 
            min: 0, 
            max: 100, 
            ticks: { callback: v => v + '%' },
            grid: { color: 'rgba(0,0,0,0.04)' } 
          },
          x: { grid: { display: false } }
        }
      }
    });
  </script>
</body>
</html>
