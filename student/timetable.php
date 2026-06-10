<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "Timetable";
$searchPlaceholder = "Search timetable...";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/header.php'; ?>
</head>
<body class="dashboard-body">
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/topbar.php'; ?>

  <main class="main-content" id="mainContent">
    <div class="page-header">
      <h1>My Timetable</h1>
      <p class="text-muted mt-1 mb-0">View your weekly class schedule.</p>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-calendar-alt"></i> Weekly Schedule</h5>
        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-download me-1"></i> Download</button>
      </div>
      <div class="dash-card-body">
        <div class="table-responsive">
          <table class="dash-table">
            <thead>
              <tr>
                <th>Time</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>08:30 - 09:15</td>
                <td><span class="status-badge active">Mathematics</span></td>
                <td><span class="status-badge active">English</span></td>
                <td><span class="status-badge active">Mathematics</span></td>
                <td><span class="status-badge active">English</span></td>
                <td><span class="status-badge active">Mathematics</span></td>
              </tr>
              <tr>
                <td>09:20 - 10:05</td>
                <td><span class="status-badge active">Science</span></td>
                <td><span class="status-badge active">Mathematics</span></td>
                <td><span class="status-badge active">Science</span></td>
                <td><span class="status-badge active">Mathematics</span></td>
                <td><span class="status-badge active">Science</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/student.js"></script>
</body>
</html>