<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "Attendance Record";
$searchPlaceholder = "Search attendance...";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/header.php'; ?>
  <style>
    .attendance-filters { background: var(--gray-100); padding: 15px; border-radius: var(--radius-sm); margin-bottom: 20px; }
  </style>
</head>
<body class="dashboard-body">
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/topbar.php'; ?>

  <main class="main-content" id="mainContent">
    <div class="page-header">
      <h1>Attendance Record</h1>
      <p class="text-muted mt-1 mb-0">View your monthly attendance history.</p>
    </div>

    <div class="dash-card mb-4">
      <div class="dash-card-header">
        <h5><i class="fas fa-filter"></i> Filter Attendance</h5>
      </div>
      <div class="dash-card-body attendance-filters">
        <div class="row g-3">
          <div class="col-md-4 col-12">
            <label class="form-label">Month</label>
            <select class="form-select" id="attendanceMonth">
              <option value="0">January</option>
              <option value="1" selected>June</option>
              <option value="2">February</option>
            </select>
          </div>
          <div class="col-md-4 col-12">
            <label class="form-label">Year</label>
            <select class="form-select" id="attendanceYear">
              <option value="2026" selected>2026</option>
              <option value="2025">2025</option>
            </select>
          </div>
          <div class="col-md-4 col-12 d-flex align-items-end">
            <button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> View</button>
          </div>
        </div>
      </div>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-calendar-check"></i> Monthly Attendance</h5>
        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-download me-1"></i> Export</button>
      </div>
      <div class="dash-card-body p-0">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Status</th>
              <th>Remarks</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>10 Jun 2026</td>
              <td><span class="status-badge present">Present</span></td>
              <td>-</td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td>09 Jun 2026</td>
              <td><span class="status-badge present">Present</span></td>
              <td>-</td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td>08 Jun 2026</td>
              <td><span class="status-badge absent">Absent</span></td>
              <td>Sick leave</td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/student.js"></script>
</body>
</html>