<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "Parent Messages";
$searchPlaceholder = "Search messages...";
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
      <h1>Parent Messages</h1>
      <p class="text-muted mt-1 mb-0">Communicate with parents of your students.</p>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-comments"></i> Messages</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sendMessageModal">
          <i class="fas fa-plus me-1"></i> Send Message
        </button>
      </div>
      <div class="dash-card-body p-3">
        <div class="notice-item">
          <div class="notice-dot urgent"></div>
          <div class="flex-grow-1">
            <h6>John's Parent: Meeting Request
              <div class="float-end">
                <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-reply"></i></button>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
              </div>
            </h6>
            <p>Can we schedule a meeting to discuss John's progress?</p>
            <div class="notice-date">3 hours ago</div>
          </div>
        </div>
        <div class="notice-item">
          <div class="notice-dot"></div>
          <div class="flex-grow-1">
            <h6>Sarah's Parent: Leave Request
              <div class="float-end">
                <button class="btn btn-sm btn-outline-success me-1"><i class="fas fa-check"></i></button>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
              </div>
            </h6>
            <p>Sarah will be absent tomorrow due to family event.</p>
            <div class="notice-date">1 day ago</div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="sendMessageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i> Send Message to Parent</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="sendMessageForm">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">To (Student) *</label>
                <select name="student_id" class="form-select" required>
                  <option value="">Select Student</option>
                  <option value="1">Ali Khan (Class 10-A)</option>
                  <option value="2">Sara Ahmed (Class 10-A)</option>
                  <option value="3">Umar Rashid (Class 11-PE)</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Message *</label>
                <textarea name="message" class="form-control" rows="4" placeholder="Type your message here..." required></textarea>
              </div>
            </div>
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Send Message</button>
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