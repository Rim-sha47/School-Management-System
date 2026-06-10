<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "My Classes";
$searchPlaceholder = "Search classes, subjects...";
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
      <h1>My Classes</h1>
      <p class="text-muted mt-1 mb-0">Manage your assigned classes and subjects.</p>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-users-class"></i> All Classes</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
          <i class="fas fa-plus me-1"></i> Add Class
        </button>
      </div>
      <div class="dash-card-body p-0">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Class</th>
              <th>Subject</th>
              <th>Students</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Class 10-A</td>
              <td>Mathematics</td>
              <td>35</td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1 edit-class-btn" data-id="1" data-class="10-A" data-subject="Mathematics"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger delete-class-btn" data-id="1"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
            <tr>
              <td>Class 11-Pre Eng</td>
              <td>Physics</td>
              <td>28</td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1 edit-class-btn" data-id="2" data-class="11-Pre Eng" data-subject="Physics"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger delete-class-btn" data-id="2"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add New Class</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addClassForm">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Class Name *</label>
                <select name="class_id" class="form-select" required>
                  <option value="">Select Class</option>
                  <option value="1">Class 10-A</option>
                  <option value="2">Class 11-Pre Eng</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Subject *</label>
                <select name="subject_id" class="form-select" required>
                  <option value="">Select Subject</option>
                  <option value="1">Mathematics</option>
                  <option value="2">Physics</option>
                </select>
              </div>
            </div>
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Class</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editClassModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Class Assignment</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="editClassForm">
            <input type="hidden" name="id" id="editClassId">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Class Name *</label>
                <select name="class_id" id="editClassSelect" class="form-select" required>
                  <option value="1">Class 10-A</option>
                  <option value="2">Class 11-Pre Eng</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Subject *</label>
                <select name="subject_id" id="editSubjectSelect" class="form-select" required>
                  <option value="1">Mathematics</option>
                  <option value="2">Physics</option>
                </select>
              </div>
            </div>
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Update Class</button>
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
    
    document.querySelectorAll('.edit-class-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.getElementById('editClassId').value = this.dataset.id;
        document.getElementById('editClassSelect').value = this.dataset.class;
        document.getElementById('editSubjectSelect').value = this.dataset.subject;
        new bootstrap.Modal(document.getElementById('editClassModal')).show();
      });
    });
    
    document.querySelectorAll('.delete-class-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to remove this class assignment?')) {
          this.closest('tr').remove();
          showToast('Class removed successfully!', '#E53935');
        }
      });
    });
  });
  </script>
</body>
</html>