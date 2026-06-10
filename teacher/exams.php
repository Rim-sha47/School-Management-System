<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "Exams";
$searchPlaceholder = "Search exams...";
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
      <h1>Exams</h1>
      <p class="text-muted mt-1 mb-0">Manage upcoming and past exams.</p>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-file-signature"></i> All Exams</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addExamModal">
          <i class="fas fa-plus me-1"></i> Add Exam
        </button>
      </div>
      <div class="dash-card-body p-0">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Exam Name</th>
              <th>Class</th>
              <th>Subject</th>
              <th>Date</th>
              <th>Duration</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Mathematics Midterm</td>
              <td>Class 10-A</td>
              <td>Mathematics</td>
              <td>25 Jun 2026</td>
              <td>3 hours</td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1 edit-exam-btn" data-id="1"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger delete-exam-btn" data-id="1"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
            <tr>
              <td>Physics Final</td>
              <td>Class 11-Pre Eng</td>
              <td>Physics</td>
              <td>30 Jun 2026</td>
              <td>2 hours</td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1 edit-exam-btn" data-id="2"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger delete-exam-btn" data-id="2"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <div class="modal fade" id="addExamModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add New Exam</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addExamForm">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Exam Name *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Mathematics Midterm" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Class *</label>
                <select name="class_id" class="form-select" required>
                  <option value="">Select Class</option>
                  <option value="1">Class 10-A</option>
                  <option value="2">Class 11-Pre Eng</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Subject *</label>
                <select name="subject_id" class="form-select" required>
                  <option value="">Select Subject</option>
                  <option value="1">Mathematics</option>
                  <option value="2">Physics</option>
                  <option value="3">Chemistry</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Exam Date *</label>
                <input type="date" name="exam_date" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Duration *</label>
                <input type="text" name="duration" class="form-control" placeholder="e.g. 3 hours" required>
              </div>
            </div>
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Exam</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/teacher.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    
    document.querySelectorAll('.edit-exam-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        showToast('Edit exam: ' + this.dataset.id, '#1565C0');
      });
    });
    
    document.querySelectorAll('.delete-exam-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this exam?')) {
          this.closest('tr').remove();
          showToast('Exam deleted successfully!', '#E53935');
        }
      });
    });
  });
  </script>
</body>
</html>