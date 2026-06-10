<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "Notices";
$searchPlaceholder = "Search notices...";
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
      <h1>Notices</h1>
      <p class="text-muted mt-1 mb-0">View all school notices and announcements.</p>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-bullhorn"></i> All Notices</h5>
      </div>
      <div class="dash-card-body p-3">
        <div class="notice-item">
          <div class="notice-dot urgent"></div>
          <div class="flex-grow-1">
            <h6>Submission of Term Results
              <div class="float-end">
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></button>
              </div>
            </h6>
            <p>All teachers must submit Term 1 results by Friday EOD.</p>
            <div class="notice-date">2 hours ago</div>
          </div>
        </div>
        <div class="notice-item">
          <div class="notice-dot info"></div>
          <div class="flex-grow-1">
            <h6>Parent-Teacher Meeting
              <div class="float-end">
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></button>
              </div>
            </h6>
            <p>PTM scheduled for next week. Please check schedule.</p>
            <div class="notice-date">1 day ago</div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/student.js"></script>
</body>
</html>