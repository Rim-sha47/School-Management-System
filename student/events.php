<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "Events";
$searchPlaceholder = "Search events...";
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
      <h1>Events</h1>
      <p class="text-muted mt-1 mb-0">View upcoming school events.</p>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-glass-cheers"></i> Upcoming Events</h5>
      </div>
      <div class="dash-card-body p-3">
        <div class="event-item">
          <div class="event-date-box" style="background: rgba(21,101,192,0.1);">
            <div class="day" style="color: var(--primary);">15</div>
            <div class="month">Jun</div>
          </div>
          <div class="event-info flex-grow-1">
            <h6>Science Fair Exhibition</h6>
            <p>Annual science fair for all classes. Last date for project submission: 12th June.</p>
          </div>
          <button class="btn btn-sm btn-outline-primary align-self-center"><i class="fas fa-eye"></i></button>
        </div>
        <div class="event-item">
          <div class="event-date-box" style="background: rgba(67,160,71,0.1);">
            <div class="day" style="color: var(--success);">20</div>
            <div class="month">Jun</div>
          </div>
          <div class="event-info flex-grow-1">
            <h6>Sports Gala</h6>
            <p>Annual sports day. All students must participate.</p>
          </div>
          <button class="btn btn-sm btn-outline-primary align-self-center"><i class="fas fa-eye"></i></button>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/student.js"></script>
</body>
</html>