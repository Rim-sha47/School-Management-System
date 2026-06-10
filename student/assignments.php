<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "Assignments";
$searchPlaceholder = "Search assignments...";
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
      <h1>Assignments</h1>
      <p class="text-muted mt-1 mb-0">View and submit your assignments.</p>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-book-open"></i> All Assignments</h5>
      </div>
      <div class="dash-card-body p-0">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Subject</th>
              <th>Due Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Algebra Exercises</td>
              <td>Mathematics</td>
              <td>15 Jun 2026</td>
              <td><span class="status-badge pending">Pending</span></td>
              <td>
                <button class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i> Submit</button>
              </td>
            </tr>
            <tr>
              <td>Physics Lab Report</td>
              <td>Physics</td>
              <td>18 Jun 2026</td>
              <td><span class="status-badge active">Submitted</span></td>
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