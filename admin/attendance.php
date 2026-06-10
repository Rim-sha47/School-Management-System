<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Get filter inputs
$class_id = $_GET['class_id'] ?? '';
$section_id = $_GET['section_id'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

$students = [];
$classes = [];
$sections = [];

try {
    // Fetch all classes for filter dropdown
    $classesStmt = $pdo->query("SELECT id, name FROM classes ORDER BY numeric_name ASC");
    $classes = $classesStmt->fetchAll();

    if (!empty($class_id)) {
        // Fetch sections for chosen class
        $sectionsStmt = $pdo->prepare("SELECT id, name FROM sections WHERE class_id = ? ORDER BY name ASC");
        $sectionsStmt->execute([$class_id]);
        $sections = $sectionsStmt->fetchAll();
    }

    if (!empty($class_id) && !empty($section_id)) {
        // Fetch students and their attendance status on that date
        $studentsStmt = $pdo->prepare("
            SELECT s.id, s.first_name, s.last_name, s.registration_number, 
                   a.status AS att_status, a.remarks 
            FROM students s 
            LEFT JOIN attendance a ON s.id = a.student_id AND a.date = :date 
            WHERE s.class_id = :class_id AND s.section_id = :section_id AND s.status = 'active'
            ORDER BY s.roll_number ASC, s.first_name ASC
        ");
        $studentsStmt->execute([
            'date' => $date,
            'class_id' => $class_id,
            'section_id' => $section_id
        ]);
        $students = $studentsStmt->fetchAll();
    }
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Manage Attendance";
  include 'includes/header.php'; 
  ?>
  <style>
    .attendance-radio {
      display: none;
    }
    .attendance-label {
      padding: 6px 14px;
      border-radius: var(--radius-full);
      font-size: 0.75rem;
      font-weight: 700;
      cursor: pointer;
      border: 1px solid var(--gray-300);
      background: white;
      color: var(--gray-500);
      transition: all 0.2s;
      text-transform: uppercase;
    }
    .attendance-radio:checked + .attendance-label.present {
      background: rgba(67,160,71,0.15);
      border-color: var(--success);
      color: var(--success);
    }
    .attendance-radio:checked + .attendance-label.absent {
      background: rgba(229,57,53,0.15);
      border-color: var(--danger);
      color: var(--danger);
    }
    .attendance-radio:checked + .attendance-label.late {
      background: rgba(251,140,0,0.15);
      border-color: var(--warning);
      color: var(--warning);
    }
    .attendance-radio:checked + .attendance-label.leave {
      background: rgba(21,101,192,0.15);
      border-color: var(--primary);
      color: var(--primary);
    }
  </style>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search page data...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header">
      <h1>Manage Attendance</h1>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Attendance</li>
      </ol>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="dash-card mb-4">
      <div class="dash-card-header">
        <h5><i class="fas fa-filter text-primary"></i> Select Class & Date</h5>
      </div>
      <div class="dash-card-body">
        <form method="GET" class="row g-3 align-items-end" id="filterForm">
          <div class="col-md-3">
            <label class="form-label fw-semibold">Class</label>
            <select name="class_id" class="form-select" id="classSelect" onchange="this.form.submit()" required>
              <option value="">-- Select Class --</option>
              <?php foreach($classes as $c): ?>
                <option value="<?php echo $c['id']; ?>" <?php echo $class_id == $c['id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($c['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Section</label>
            <select name="section_id" class="form-select" id="sectionSelect" onchange="this.form.submit()" required <?php echo empty($class_id) ? 'disabled' : ''; ?>>
              <option value="">-- Select Section --</option>
              <?php foreach($sections as $s): ?>
                <option value="<?php echo $s['id']; ?>" <?php echo $section_id == $s['id'] ? 'selected' : ''; ?>>
                  Section <?php echo htmlspecialchars($s['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Date</label>
            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>" required onchange="this.form.submit()">
          </div>
        </form>
      </div>
    </div>

    <?php if(!empty($class_id) && !empty($section_id)): ?>
      <!-- Student Attendance Marking Table -->
      <form id="attendanceForm">
        <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class_id); ?>">
        <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($section_id); ?>">
        <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
        
        <div class="dash-card">
          <div class="dash-card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-calendar-check text-primary"></i> Attendance Sheet</h5>
            <div id="saveAlert" class="small fw-semibold text-success d-none"><i class="fas fa-check-circle me-1"></i> Saved!</div>
          </div>
          <div class="dash-card-body p-0" style="overflow-x: auto;">
            <table class="dash-table" id="attendanceTable">
              <thead>
                <tr>
                  <th>Roll / Reg No</th>
                  <th>Student Name</th>
                  <th class="text-center">Attendance Status</th>
                  <th>Remarks / Notes</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($students)): ?>
                  <tr><td colspan="4" class="text-center py-4">No active students found in this class section.</td></tr>
                <?php else: ?>
                  <?php foreach($students as $student): ?>
                    <?php 
                      $current_status = $student['att_status'] ?? 'present'; 
                    ?>
                    <tr>
                      <td>
                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($student['registration_number']); ?></span>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm me-2"><?php echo strtoupper(substr($student['first_name'],0,1).substr($student['last_name'],0,1)); ?></div>
                          <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                        </div>
                      </td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                          <label class="me-1">
                            <input type="radio" class="attendance-radio" name="attendance[<?php echo $student['id']; ?>]" value="present" <?php echo $current_status === 'present' ? 'checked' : ''; ?>>
                            <span class="attendance-label present">Present</span>
                          </label>
                          <label class="me-1">
                            <input type="radio" class="attendance-radio" name="attendance[<?php echo $student['id']; ?>]" value="absent" <?php echo $current_status === 'absent' ? 'checked' : ''; ?>>
                            <span class="attendance-label absent">Absent</span>
                          </label>
                          <label class="me-1">
                            <input type="radio" class="attendance-radio" name="attendance[<?php echo $student['id']; ?>]" value="late" <?php echo $current_status === 'late' ? 'checked' : ''; ?>>
                            <span class="attendance-label late">Late</span>
                          </label>
                          <label class="me-1">
                            <input type="radio" class="attendance-radio" name="attendance[<?php echo $student['id']; ?>]" value="leave" <?php echo $current_status === 'leave' ? 'checked' : ''; ?>>
                            <span class="attendance-label leave">Leave</span>
                          </label>
                        </div>
                      </td>
                      <td>
                        <input type="text" name="remarks[<?php echo $student['id']; ?>]" class="form-control form-control-sm" placeholder="Remarks (optional)" value="<?php echo htmlspecialchars($student['remarks'] ?? ''); ?>">
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <?php if(!empty($students)): ?>
            <div class="dash-card-footer p-3 bg-light text-end border-top">
              <button type="submit" class="btn btn-primary" id="saveAttendanceBtn">
                <i class="fas fa-save me-1"></i> Save Attendance Sheet
              </button>
            </div>
          <?php endif; ?>
        </div>
      </form>
    <?php else: ?>
      <div class="text-center py-5 border border-dashed rounded bg-white">
        <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
        <h5>Please select a Class and Section above to view student sheet.</h5>
      </div>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/admin.js"></script>
  <script>
    // Search functionality
    document.getElementById('globalSearch').addEventListener('keyup', function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll('#attendanceTable tbody tr');
      rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // Save Attendance AJAX
    const attForm = document.getElementById('attendanceForm');
    if (attForm) {
      attForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('saveAttendanceBtn');
        const alertDiv = document.getElementById('saveAlert');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;
        
        const formData = new FormData(this);
        
        fetch('save_attendance_action.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            alertDiv.classList.remove('d-none');
            btn.innerHTML = originalText;
            btn.disabled = false;
            setTimeout(() => {
              alertDiv.classList.add('d-none');
            }, 3000);
          } else {
            alert('Error: ' + data.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
          }
        })
        .catch(err => {
          alert('A network error occurred.');
          btn.innerHTML = originalText;
          btn.disabled = false;
        });
      });
    }
  </script>
</body>
</html>
