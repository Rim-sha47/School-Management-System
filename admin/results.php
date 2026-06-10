<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Get filter inputs
$exam_id = $_GET['exam_id'] ?? '';
$section_id = $_GET['section_id'] ?? '';
$subject_id = $_GET['subject_id'] ?? '';

$exams = [];
$sections = [];
$subjects = [];
$students = [];
$selected_exam = null;

try {
    // Fetch all exams for selection
    $examsStmt = $pdo->query("SELECT e.*, c.name AS class_name FROM exams e JOIN classes c ON e.class_id = c.id ORDER BY e.id DESC");
    $exams = $examsStmt->fetchAll();

    if (!empty($exam_id)) {
        // Fetch details of selected exam to get its class
        $examDetailsStmt = $pdo->prepare("SELECT e.*, c.name AS class_name FROM exams e JOIN classes c ON e.class_id = c.id WHERE e.id = ?");
        $examDetailsStmt->execute([$exam_id]);
        $selected_exam = $examDetailsStmt->fetch();

        if ($selected_exam) {
            $class_id = $selected_exam['class_id'];
            
            // Fetch sections for this class
            $sectionsStmt = $pdo->prepare("SELECT id, name FROM sections WHERE class_id = ? ORDER BY name ASC");
            $sectionsStmt->execute([$class_id]);
            $sections = $sectionsStmt->fetchAll();

            // Fetch subjects for this class
            $subjectsStmt = $pdo->prepare("SELECT id, name, code FROM subjects WHERE class_id = ? OR class_id IS NULL ORDER BY name ASC");
            $subjectsStmt->execute([$class_id]);
            $subjects = $subjectsStmt->fetchAll();
        }
    }

    if (!empty($exam_id) && !empty($section_id) && !empty($subject_id) && $selected_exam) {
        $class_id = $selected_exam['class_id'];
        
        // Fetch students and their recorded results
        $studentsStmt = $pdo->prepare("
            SELECT s.id, s.first_name, s.last_name, s.registration_number, 
                   r.marks_obtained, r.remarks, r.grade 
            FROM students s 
            LEFT JOIN results r ON s.id = r.student_id AND r.exam_id = :exam_id AND r.subject_id = :subject_id 
            WHERE s.class_id = :class_id AND s.section_id = :section_id AND s.status = 'active'
            ORDER BY s.roll_number ASC, s.first_name ASC
        ");
        $studentsStmt->execute([
            'exam_id' => $exam_id,
            'subject_id' => $subject_id,
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
  $pageTitle = "Manage Exam Results";
  include 'includes/header.php'; 
  ?>
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
      <h1>Manage Exam Results</h1>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Results</li>
      </ol>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Selector Card -->
    <div class="dash-card mb-4">
      <div class="dash-card-header">
        <h5><i class="fas fa-filter text-primary"></i> Select Exam, Section & Subject</h5>
      </div>
      <div class="dash-card-body">
        <form method="GET" class="row g-3 align-items-end" id="resultsFilterForm">
          <div class="col-md-4">
            <label class="form-label fw-semibold">Exam *</label>
            <select name="exam_id" class="form-select" onchange="this.form.submit()" required>
              <option value="">-- Select Exam --</option>
              <?php foreach($exams as $e): ?>
                <option value="<?php echo $e['id']; ?>" <?php echo $exam_id == $e['id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($e['name'] . ' (' . $e['class_name'] . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Section *</label>
            <select name="section_id" class="form-select" onchange="this.form.submit()" required <?php echo empty($exam_id) ? 'disabled' : ''; ?>>
              <option value="">-- Select Section --</option>
              <?php foreach($sections as $s): ?>
                <option value="<?php echo $s['id']; ?>" <?php echo $section_id == $s['id'] ? 'selected' : ''; ?>>
                  Section <?php echo htmlspecialchars($s['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Subject *</label>
            <select name="subject_id" class="form-select" onchange="this.form.submit()" required <?php echo empty($exam_id) ? 'disabled' : ''; ?>>
              <option value="">-- Select Subject --</option>
              <?php foreach($subjects as $sub): ?>
                <option value="<?php echo $sub['id']; ?>" <?php echo $subject_id == $sub['id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($sub['name'] . ' [' . $sub['code'] . ']'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </form>
      </div>
    </div>

    <?php if(!empty($exam_id) && !empty($section_id) && !empty($subject_id) && $selected_exam): ?>
      <!-- Marks Entry Form -->
      <form id="resultsForm">
        <input type="hidden" name="exam_id" value="<?php echo htmlspecialchars($exam_id); ?>">
        <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($subject_id); ?>">
        
        <div class="dash-card">
          <div class="dash-card-header d-flex justify-content-between align-items-center">
            <h5>
              <i class="fas fa-edit text-primary"></i> 
              Marks Entry sheet — Maximum Marks: <span class="text-primary font-monospace"><?php echo htmlspecialchars($selected_exam['total_marks']); ?></span>
            </h5>
            <div id="saveAlert" class="small fw-semibold text-success d-none"><i class="fas fa-check-circle me-1"></i> Marks Saved!</div>
          </div>
          <div class="dash-card-body p-0" style="overflow-x: auto;">
            <table class="dash-table" id="resultsTable">
              <thead>
                <tr>
                  <th>Reg No</th>
                  <th>Student Name</th>
                  <th style="width:200px;">Obtained Marks *</th>
                  <th>Grade</th>
                  <th>Remarks</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($students)): ?>
                  <tr><td colspan="5" class="text-center py-4">No students found in this class section.</td></tr>
                <?php else: ?>
                  <?php foreach($students as $student): ?>
                    <tr>
                      <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($student['registration_number']); ?></span></td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm me-2"><?php echo strtoupper(substr($student['first_name'],0,1).substr($student['last_name'],0,1)); ?></div>
                          <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                        </div>
                      </td>
                      <td>
                        <input type="number" step="0.1" name="marks[<?php echo $student['id']; ?>]" 
                               class="form-control marks-input" 
                               max="<?php echo htmlspecialchars($selected_exam['total_marks']); ?>" 
                               min="0" 
                               placeholder="e.g. 85" 
                               value="<?php echo htmlspecialchars($student['marks_obtained'] ?? ''); ?>" 
                               required>
                      </td>
                      <td>
                        <input type="text" name="grade[<?php echo $student['id']; ?>]" 
                               class="form-control bg-light grade-output" 
                               value="<?php echo htmlspecialchars($student['grade'] ?? ''); ?>" 
                               readonly>
                      </td>
                      <td>
                        <input type="text" name="remarks[<?php echo $student['id']; ?>]" 
                               class="form-control" 
                               placeholder="e.g. Excellent / Fail" 
                               value="<?php echo htmlspecialchars($student['remarks'] ?? ''); ?>">
                      </td>
                      <td>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-clear-result" data-student-id="<?php echo $student['id']; ?>" title="Clear Result"><i class="fas fa-eraser"></i></button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <?php if(!empty($students)): ?>
            <div class="dash-card-footer p-3 bg-light text-end border-top">
              <button type="submit" class="btn btn-primary" id="saveResultsBtn">
                <i class="fas fa-save me-1"></i> Save Marks
              </button>
            </div>
          <?php endif; ?>
        </div>
      </form>
    <?php else: ?>
      <div class="text-center py-5 border border-dashed rounded bg-white">
        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
        <h5>Please select an Exam, Section, and Subject above to entry student marks.</h5>
      </div>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/admin.js"></script>
  <script>
    // Search functionality
    document.getElementById('globalSearch').addEventListener('keyup', function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll('#resultsTable tbody tr');
      rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // Auto grading helper based on total marks
    const maxMarks = <?php echo isset($selected_exam['total_marks']) ? floatval($selected_exam['total_marks']) : 100; ?>;
    
    function calculateGrade(obtained) {
      if (obtained === '' || isNaN(obtained)) return '';
      const pct = (obtained / maxMarks) * 100;
      if (pct >= 90) return 'A+';
      if (pct >= 80) return 'A';
      if (pct >= 70) return 'B';
      if (pct >= 60) return 'C';
      if (pct >= 50) return 'D';
      if (pct >= 40) return 'E';
      return 'F';
    }

    document.querySelectorAll('.marks-input').forEach(input => {
      input.addEventListener('input', function() {
        const gradeField = this.closest('tr').querySelector('.grade-output');
        const obtained = parseFloat(this.value);
        gradeField.value = calculateGrade(obtained);
      });
    });

    // Save Results AJAX
    const resultsForm = document.getElementById('resultsForm');
    if (resultsForm) {
      resultsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('saveResultsBtn');
        const alertDiv = document.getElementById('saveAlert');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;
        
        const formData = new FormData(this);
        formData.append('total_marks', maxMarks);
        
        fetch('save_results_action.php', {
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

    // Clear Result
    document.querySelectorAll('.btn-clear-result').forEach(btn => {
      btn.addEventListener('click', function() {
        if(confirm('Are you sure you want to clear the result for this student?')) {
          const row = this.closest('tr');
          row.querySelector('.marks-input').value = '';
          row.querySelector('.grade-output').value = '';
          row.querySelector('input[name^="remarks"]').value = '';
          // Highlight row to show it will be cleared on save
          row.style.backgroundColor = 'rgba(229,57,53,0.1)';
        }
      });
    });
  </script>
</body>
</html>
