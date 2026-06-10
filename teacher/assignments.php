<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
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
      <p class="text-muted mt-1 mb-0">Create and manage class assignments.</p>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-tasks"></i> All Assignments</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
          <i class="fas fa-plus me-1"></i> Add Assignment
        </button>
      </div>
      <div class="dash-card-body p-0">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Class</th>
              <th>Subject</th>
              <th>Due Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Algebra Exercises</td>
              <td>Class 10-A</td>
              <td>Mathematics</td>
              <td>15 Jun 2026</td>
              <td><span class="status-badge pending">Pending</span></td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1 edit-assignment-btn" data-id="1"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger delete-assignment-btn" data-id="1"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
            <tr>
              <td>Physics Lab Report</td>
              <td>Class 11-Pre Eng</td>
              <td>Physics</td>
              <td>18 Jun 2026</td>
              <td><span class="status-badge active">Active</span></td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1 edit-assignment-btn" data-id="2"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger delete-assignment-btn" data-id="2"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <div class="modal fade" id="addAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add New Assignment</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addAssignmentForm">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Chapter 5 Exercises" required>
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
              <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Assignment details..."></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Due Date *</label>
                <input type="date" name="due_date" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select" required>
                  <option value="active">Active</option>
                  <option value="pending">Pending</option>
                  <option value="completed">Completed</option>
                </select>
              </div>
            </div>
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Assignment</button>
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
    
    document.querySelectorAll('.edit-assignment-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        showToast('Edit assignment: ' + this.dataset.id, '#1565C0');
      });
    });
    
    document.querySelectorAll('.delete-assignment-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this assignment?')) {
          this.closest('tr').remove();
          showToast('Assignment deleted successfully!', '#E53935');
        }
      });
    });
  });
  </script>
</body>
</html>