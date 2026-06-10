<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "Attendance";
$searchPlaceholder = "Search students, dates...";
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
      <h1>Attendance</h1>
      <p class="text-muted mt-1 mb-0">Mark and manage student attendance.</p>
    </div>

    <div class="dash-card mb-4">
      <div class="dash-card-header">
        <h5><i class="fas fa-clipboard-check"></i> Mark Attendance</h5>
        <button class="btn btn-sm btn-primary" id="submitAttendance"><i class="fas fa-save me-1"></i> Save</button>
      </div>
      <div class="dash-card-body p-0">
        <div class="p-3 border-bottom">
          <div class="row g-2">
            <div class="col-md-4">
              <select class="form-select" id="attendanceClass">
                <option value="">Select Class</option>
                <option value="10a">Class 10-A</option>
                <option value="11pe">Class 11-Pre Eng</option>
              </select>
            </div>
            <div class="col-md-4">
              <input type="date" class="form-control" id="attendanceDate" value="<?= date('Y-m-d'); ?>">
            </div>
            <div class="col-md-4">
              <button class="btn btn-primary w-100" onclick="loadStudents()"><i class="fas fa-sync me-1"></i> Load Students</button>
            </div>
          </div>
        </div>
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
            <tr>
              <td>10A-03</td>
              <td><div class="avatar-sm">UR</div> Umar Rashid</td>
              <td><span class="status-badge present att-status">Present</span></td>
              <td>
                <button class="att-btn p active" data-status="present" title="Present"><i class="fas fa-check"></i></button>
                <button class="att-btn a" data-status="absent" title="Absent"><i class="fas fa-times"></i></button>
                <button class="att-btn l" data-status="leave" title="Leave"><i class="fas fa-envelope"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/teacher.js"></script>
</body>
</html>