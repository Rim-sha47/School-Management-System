<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "My Timetable";
$searchPlaceholder = "Search timetable...";
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
      <h1>My Timetable</h1>
      <p class="text-muted mt-1 mb-0">View and manage your weekly schedule.</p>
    </div>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-calendar-alt"></i> Weekly Timetable</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTimetableModal">
          <i class="fas fa-plus me-1"></i> Add Entry
        </button>
      </div>
      <div class="dash-card-body">
        <div class="table-responsive">
          <table class="dash-table">
            <thead>
              <tr>
                <th>Time</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>08:30 - 09:15</td>
                <td><span class="status-badge active">Mathematics (10-A)</span></td>
                <td><span class="status-badge active">Physics (11-PE)</span></td>
                <td><span class="status-badge active">Mathematics (10-A)</span></td>
                <td><span class="status-badge active">Physics (11-PE)</span></td>
                <td><span class="status-badge active">Mathematics (10-A)</span></td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
              <tr>
                <td>09:20 - 10:05</td>
                <td><span class="status-badge active">Physics (11-PE)</span></td>
                <td><span class="status-badge active">Mathematics (10-A)</span></td>
                <td><span class="status-badge active">Physics (11-PE)</span></td>
                <td><span class="status-badge active">Mathematics (10-A)</span></td>
                <td><span class="status-badge active">Physics (11-PE)</span></td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="addTimetableModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add Timetable Entry</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addTimetableForm">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Day *</label>
                <select name="day" class="form-select" required>
                  <option value="">Select Day</option>
                  <option value="monday">Monday</option>
                  <option value="tuesday">Tuesday</option>
                  <option value="wednesday">Wednesday</option>
                  <option value="thursday">Thursday</option>
                  <option value="friday">Friday</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Time *</label>
                <input type="text" name="time" class="form-control" placeholder="e.g. 08:30 - 09:15" required>
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
            </div>
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Entry</button>
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