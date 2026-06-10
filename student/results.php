<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "Exam Results";
$searchPlaceholder = "Search results...";
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
      <h1>Exam Results</h1>
      <p class="text-muted mt-1 mb-0">View your exam results and performance.</p>
    </div>

    <div class="dash-card mb-4">
      <div class="dash-card-header">
        <h5><i class="fas fa-filter"></i> Filter Results</h5>
      </div>
      <div class="dash-card-body">
        <div class="row g-3">
          <div class="col-md-4 col-12">
            <label class="form-label">Exam Term</label>
            <select class="form-select">
              <option>Term 1 2026</option>
              <option>Term 2 2025</option>
              <option>Final 2025</option>
            </select>
          </div>
          <div class="col-md-4 col-12">
            <label class="form-label">Subject</label>
            <select class="form-select">
              <option>All Subjects</option>
              <option>Mathematics</option>
              <option>Science</option>
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
        <h5><i class="fas fa-file-alt"></i> Results List</h5>
        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-download me-1"></i> Download</button>
      </div>
      <div class="dash-card-body p-0">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Subject</th>
              <th>Exam</th>
              <th>Marks</th>
              <th>Grade</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Mathematics</td>
              <td>Term 1 Exam</td>
              <td>88/100</td>
              <td><span class="status-badge active">A+</span></td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></button>
                <button class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></button>
              </td>
            </tr>
            <tr>
              <td>Science</td>
              <td>Term 1 Exam</td>
              <td>76/100</td>
              <td><span class="status-badge active">B+</span></td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></button>
                <button class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></button>
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