<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch all timetables with related data
try {
    $stmt = $pdo->query("SELECT t.*, c.name as class_name, s.name as subject_name, sec.name as section_name, CONCAT(tea.first_name, ' ', tea.last_name) as teacher_name
                         FROM timetables t 
                         JOIN classes c ON t.class_id = c.id
                         JOIN subjects s ON t.subject_id = s.id
                         JOIN teachers tea ON t.teacher_id = tea.id
                         LEFT JOIN sections sec ON t.section_id = sec.id
                         ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time");
    $timetables = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching timetables: " . $e->getMessage();
    $timetables = [];
}

// Fetch lists for modals
try {
    $classes = $pdo->query("SELECT id, name FROM classes ORDER BY numeric_name")->fetchAll();
    $subjects = $pdo->query("SELECT id, name FROM subjects")->fetchAll();
    $teachers = $pdo->query("SELECT id, first_name, last_name FROM teachers")->fetchAll();
    $sections = $pdo->query("SELECT id, name, class_id FROM sections")->fetchAll();
} catch(PDOException $e) {
    $classes = []; $subjects = []; $teachers = []; $sections = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Manage Timetable";
  include 'includes/header.php'; 
  ?>
  <style>
      .timetable-table { min-width: 800px; }
  </style>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search timetable...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Manage Timetable</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Timetable</li>
        </ol>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTimetableModal">
        <i class="fas fa-plus me-2"></i> Add Schedule
      </button>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-calendar-week text-primary"></i> Class Schedules</h5>
      </div>
      <div class="dash-card-body p-0" style="overflow-x: auto;">
        <table class="dash-table timetable-table" id="timetableTable">
          <thead>
            <tr>
              <th>Class/Section</th>
              <th>Day</th>
              <th>Time</th>
              <th>Subject</th>
              <th>Teacher</th>
              <th>Room</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($timetables)): ?>
              <tr><td colspan="7" class="text-center py-4">No schedules found.</td></tr>
            <?php else: ?>
              <?php foreach($timetables as $tt): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($tt['class_name']); ?></strong> 
                      <?php if($tt['section_name']) echo '- <span class="badge bg-primary-pale text-primary">' . htmlspecialchars($tt['section_name']) . '</span>'; ?>
                  </td>
                  <td><?php echo htmlspecialchars($tt['day_of_week']); ?></td>
                  <td><?php echo date('h:i A', strtotime($tt['start_time'])) . ' - ' . date('h:i A', strtotime($tt['end_time'])); ?></td>
                  <td><?php echo htmlspecialchars($tt['subject_name']); ?></td>
                  <td><?php echo htmlspecialchars($tt['teacher_name']); ?></td>
                  <td><?php echo htmlspecialchars($tt['room_number'] ?? 'N/A'); ?></td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary btn-edit-tt" 
                        data-id="<?php echo $tt['id']; ?>" 
                        data-class="<?php echo $tt['class_id']; ?>" 
                        data-section="<?php echo $tt['section_id'] ?? ''; ?>" 
                        data-subject="<?php echo $tt['subject_id']; ?>" 
                        data-teacher="<?php echo $tt['teacher_id']; ?>" 
                        data-day="<?php echo htmlspecialchars($tt['day_of_week']); ?>" 
                        data-start="<?php echo $tt['start_time']; ?>" 
                        data-end="<?php echo $tt['end_time']; ?>" 
                        data-room="<?php echo htmlspecialchars($tt['room_number']); ?>" 
                        title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-tt" data-id="<?php echo $tt['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Add Timetable Modal -->
  <div class="modal fade" id="addTimetableModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add Schedule</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addTimetableForm">
            <div id="addFormAlert" class="alert d-none"></div>
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Class *</label>
                <select name="class_id" class="form-select" required>
                  <option value="">Select Class</option>
                  <?php foreach($classes as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Section (Optional)</label>
                <select name="section_id" class="form-select">
                  <option value="">Any</option>
                  <?php foreach($sections as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Subject *</label>
                <select name="subject_id" class="form-select" required>
                  <option value="">Select Subject</option>
                  <?php foreach($subjects as $sub): ?>
                    <option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Teacher *</label>
                <select name="teacher_id" class="form-select" required>
                  <option value="">Select Teacher</option>
                  <?php foreach($teachers as $t): ?>
                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Day of Week *</label>
                <select name="day_of_week" class="form-select" required>
                  <option value="">Select Day</option>
                  <option value="Monday">Monday</option>
                  <option value="Tuesday">Tuesday</option>
                  <option value="Wednesday">Wednesday</option>
                  <option value="Thursday">Thursday</option>
                  <option value="Friday">Friday</option>
                  <option value="Saturday">Saturday</option>
                  <option value="Sunday">Sunday</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Room Number</label>
                <input type="text" name="room_number" class="form-control" placeholder="e.g. 101">
              </div>
              <div class="col-md-6">
                <label class="form-label">Start Time *</label>
                <input type="time" name="start_time" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">End Time *</label>
                <input type="time" name="end_time" class="form-control" required>
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveTtBtn">Save Schedule</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Timetable Modal -->
  <div class="modal fade" id="editTimetableModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Schedule</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="editTimetableForm">
            <div id="editFormAlert" class="alert d-none"></div>
            <input type="hidden" name="id" id="editTtId">
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Class *</label>
                <select name="class_id" id="editTtClass" class="form-select" required>
                  <?php foreach($classes as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Section</label>
                <select name="section_id" id="editTtSection" class="form-select">
                  <option value="">Any</option>
                  <?php foreach($sections as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Subject *</label>
                <select name="subject_id" id="editTtSubject" class="form-select" required>
                  <?php foreach($subjects as $sub): ?>
                    <option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Teacher *</label>
                <select name="teacher_id" id="editTtTeacher" class="form-select" required>
                  <?php foreach($teachers as $t): ?>
                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Day of Week *</label>
                <select name="day_of_week" id="editTtDay" class="form-select" required>
                  <option value="Monday">Monday</option>
                  <option value="Tuesday">Tuesday</option>
                  <option value="Wednesday">Wednesday</option>
                  <option value="Thursday">Thursday</option>
                  <option value="Friday">Friday</option>
                  <option value="Saturday">Saturday</option>
                  <option value="Sunday">Sunday</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Room Number</label>
                <input type="text" name="room_number" id="editTtRoom" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">Start Time *</label>
                <input type="time" name="start_time" id="editTtStart" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">End Time *</label>
                <input type="time" name="end_time" id="editTtEnd" class="form-control" required>
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="updateTtBtn">Update Schedule</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/admin.js"></script>
  <script>
    // Search
    document.getElementById('globalSearch').addEventListener('keyup', function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll('#timetableTable tbody tr');
      rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // Add Timetable AJAX
    document.getElementById('addTimetableForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = document.getElementById('saveTtBtn');
      const alertDiv = document.getElementById('addFormAlert');
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      btn.disabled = true;
      alertDiv.className = 'alert d-none';
      
      fetch('add_timetable_action.php', {
        method: 'POST',
        body: new FormData(this)
      })
      .then(res => res.json())
      .then(data => {
        if(data.status === 'success') {
          alertDiv.className = 'alert alert-success';
          alertDiv.textContent = data.message;
          setTimeout(() => { window.location.reload(); }, 1000);
        } else {
          alertDiv.className = 'alert alert-danger';
          alertDiv.textContent = data.message;
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      })
      .catch(err => {
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'Network error occurred.';
        btn.innerHTML = originalText;
        btn.disabled = false;
      });
    });

    // Edit Modal population
    document.querySelectorAll('.btn-edit-tt').forEach(button => {
      button.addEventListener('click', function() {
        document.getElementById('editTtId').value = this.dataset.id;
        document.getElementById('editTtClass').value = this.dataset.class;
        document.getElementById('editTtSection').value = this.dataset.section;
        document.getElementById('editTtSubject').value = this.dataset.subject;
        document.getElementById('editTtTeacher').value = this.dataset.teacher;
        document.getElementById('editTtDay').value = this.dataset.day;
        document.getElementById('editTtStart').value = this.dataset.start;
        document.getElementById('editTtEnd').value = this.dataset.end;
        document.getElementById('editTtRoom').value = this.dataset.room;
        new bootstrap.Modal(document.getElementById('editTimetableModal')).show();
      });
    });

    // Update Timetable AJAX
    document.getElementById('editTimetableForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = document.getElementById('updateTtBtn');
      const alertDiv = document.getElementById('editFormAlert');
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
      btn.disabled = true;
      alertDiv.className = 'alert d-none';
      
      fetch('update_timetable_action.php', {
        method: 'POST',
        body: new FormData(this)
      })
      .then(res => res.json())
      .then(data => {
        if(data.status === 'success') {
          alertDiv.className = 'alert alert-success';
          alertDiv.textContent = data.message;
          setTimeout(() => { window.location.reload(); }, 1000);
        } else {
          alertDiv.className = 'alert alert-danger';
          alertDiv.textContent = data.message;
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      })
      .catch(err => {
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'Network error occurred.';
        btn.innerHTML = originalText;
        btn.disabled = false;
      });
    });

    // Delete Timetable AJAX
    document.querySelectorAll('.btn-delete-tt').forEach(button => {
      button.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this schedule?')) {
          fetch('delete_timetable_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(this.dataset.id)
          })
          .then(res => res.json())
          .then(data => {
            if(data.status === 'success') window.location.reload();
            else alert(data.message);
          })
          .catch(err => alert('Network error occurred.'));
        }
      });
    });
  </script>
</body>
</html>
