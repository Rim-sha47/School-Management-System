<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "Notice Board";
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
      <h1>Notice Board</h1>
      <p class="text-muted mt-1 mb-0">View and manage notices.</p>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-bullhorn"></i> All Notices</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addNoticeModal">
          <i class="fas fa-plus me-1"></i> Add Notice
        </button>
      </div>
      <div class="dash-card-body p-3">
        <div class="notice-item">
          <div class="notice-dot urgent"></div>
          <div class="flex-grow-1">
            <h6>Submission of Term Results
              <div class="float-end">
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
              </div>
            </h6>
            <p>All teachers must submit Term 1 results by Friday EOD.</p>
            <div class="notice-date">2 hours ago</div>
          </div>
        </div>
        <div class="notice-item">
          <div class="notice-dot"></div>
          <div class="flex-grow-1">
            <h6>Parent-Teacher Meeting
              <div class="float-end">
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
              </div>
            </h6>
            <p>PTM scheduled for next week. Please check schedule.</p>
            <div class="notice-date">1 day ago</div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="addNoticeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add New Notice</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addNoticeForm">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Important Announcement" required>
              </div>
              <div class="col-12">
                <label class="form-label">Content *</label>
                <textarea name="content" class="form-control" rows="4" placeholder="Write notice details here..." required></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Audience *</label>
                <select name="audience" class="form-select" required>
                  <option value="all">Everyone</option>
                  <option value="teachers">Teachers Only</option>
                  <option value="students">Students Only</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Priority *</label>
                <select name="priority" class="form-select" required>
                  <option value="normal">Normal</option>
                  <option value="important">Important</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Publish Date *</label>
                <input type="date" name="publish_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
              </div>
            </div>
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Publish Notice</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/teacher.js"></script>
</body>
</html>