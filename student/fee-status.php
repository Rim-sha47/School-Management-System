<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "Fee Status";
$searchPlaceholder = "Search fees...";
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
      <h1>Fee Status</h1>
      <p class="text-muted mt-1 mb-0">View your fee payment history and status.</p>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-4 col-md-6">
        <div class="widget-card green">
          <div class="widget-icon"><i class="fas fa-check-circle"></i></div>
          <div class="widget-info">
            <h2>Cleared</h2>
            <p>Current Status</p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="widget-card blue">
          <div class="widget-icon"><i class="fas fa-rupee-sign"></i></div>
          <div class="widget-info">
            <h2>$1500</h2>
            <p>Total Paid</p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="widget-card orange">
          <div class="widget-icon"><i class="fas fa-receipt"></i></div>
          <div class="widget-info">
            <h2>$0</h2>
            <p>Due Amount</p>
          </div>
        </div>
      </div>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-history"></i> Payment History</h5>
        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-download me-1"></i> Receipt</button>
      </div>
      <div class="dash-card-body p-0">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Amount</th>
              <th>Mode</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>01 May 2026</td>
              <td>$500</td>
              <td>Cash</td>
              <td><span class="status-badge paid">Paid</span></td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></button>
                <button class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></button>
              </td>
            </tr>
            <tr>
              <td>01 Apr 2026</td>
              <td>$500</td>
              <td>Bank Transfer</td>
              <td><span class="status-badge paid">Paid</span></td>
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