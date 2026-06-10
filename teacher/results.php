<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "Results Entry";
$searchPlaceholder = "Search results, students...";
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
      <h1>Results Entry</h1>
      <p class="text-muted mt-1 mb-0">Enter and manage student exam results.</p>
    </div>

    <div class="dash-card mb-4">
      <div class="dash-card-header">
        <h5><i class="fas fa-filter"></i> Select Exam & Class</h5>
      </div>
      <div class="dash-card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <select class="form-select" id="resultExam">
              <option value="">Select Exam</option>
              <option value="1">Mathematics Midterm</option>
              <option value="2">Physics Final</option>
            </select>
          </div>
          <div class="col-md-4">
            <select class="form-select" id="resultClass">
              <option value="">Select Class</option>
              <option value="10a">Class 10-A</option>
              <option value="11pe">Class 11-Pre Eng</option>
            </select>
          </div>
          <div class="col-md-4">
            <button class="btn btn-primary w-100" onclick="loadResultStudents()"><i class="fas fa-sync me-1"></i> Load Students</button>
          </div>
        </div>
      </div>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-poll"></i> Student Results</h5>
        <button class="btn btn-sm btn-primary" id="saveResults"><i class="fas fa-save me-1"></i> Save All</button>
      </div>
      <div class="dash-card-body p-0">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Student Name</th>
              <th>Roll No</th>
              <th>Marks Obtained</th>
              <th>Total Marks</th>
              <th>Grade</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><div class="avatar-sm">AK</div> Ali Khan</td>
              <td>10A-01</td>
              <td><input type="number" class="form-control form-control-sm" style="width:80px" value="85"></td>
              <td>100</td>
              <td><span class="status-badge active">A+</span></td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
            <tr>
              <td><div class="avatar-sm" style="background:var(--warning);">SA</div> Sara Ahmed</td>
              <td>10A-02</td>
              <td><input type="number" class="form-control form-control-sm" style="width:80px" value="72"></td>
              <td>100</td>
              <td><span class="status-badge active">B+</span></td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
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