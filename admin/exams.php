<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch all exams
try {
    $stmt = $pdo->query("SELECT e.*, c.name AS class_name, s.name AS session_name 
                         FROM exams e 
                         LEFT JOIN classes c ON e.class_id = c.id 
                         LEFT JOIN academic_sessions s ON e.session_id = s.id 
                         ORDER BY e.id DESC");
    $exams = $stmt->fetchAll();
    
    // Fetch classes for dropdown selection
    $classesStmt = $pdo->query("SELECT id, name FROM classes ORDER BY numeric_name ASC");
    $classes = $classesStmt->fetchAll();

    // Fetch sessions for dropdown selection
    $sessionsStmt = $pdo->query("SELECT id, name FROM academic_sessions WHERE is_current = 1 LIMIT 1");
    $current_session = $sessionsStmt->fetch();
} catch(PDOException $e) {
    $error = "Error fetching exams: " . $e->getMessage();
    $exams = [];
    $classes = [];
    $current_session = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Manage Exams";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search exams...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Manage Exams</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Exams</li>
        </ol>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExamModal">
        <i class="fas fa-plus me-2"></i> Add Exam
      </button>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-file-alt text-primary"></i> Examination List</h5>
      </div>
      <div class="dash-card-body p-0" style="overflow-x: auto;">
        <table class="dash-table" id="examsTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Exam Name</th>
              <th>Type</th>
              <th>Class</th>
              <th>Session</th>
              <th>Start / End Date</th>
              <th>Marks (Total / Passing)</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($exams)): ?>
              <tr><td colspan="9" class="text-center py-4">No exams found.</td></tr>
            <?php else: ?>
              <?php foreach($exams as $exam): ?>
                <tr>
                  <td><?php echo htmlspecialchars($exam['id']); ?></td>
                  <td><strong><?php echo htmlspecialchars($exam['name']); ?></strong></td>
                  <td><span class="badge bg-light text-dark border"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($exam['type']))); ?></span></td>
                  <td><?php echo htmlspecialchars($exam['class_name'] ?? 'General / All Classes'); ?></td>
                  <td><?php echo htmlspecialchars($exam['session_name'] ?? 'N/A'); ?></td>
                  <td>
                    <div class="small">Start: <?php echo htmlspecialchars($exam['start_date']); ?></div>
                    <div class="small text-muted">End: <?php echo htmlspecialchars($exam['end_date']); ?></div>
                  </td>
                  <td>
                    <div>Total: <span class="fw-semibold text-primary"><?php echo htmlspecialchars($exam['total_marks']); ?></span></div>
                    <div class="small text-muted">Passing: <?php echo htmlspecialchars($exam['passing_marks']); ?></div>
                  </td>
                  <td>
                    <?php 
                      $status = $exam['status'];
                      if($status === 'upcoming') echo '<span class="status-badge pending">Upcoming</span>';
                      elseif($status === 'ongoing') echo '<span class="status-badge leave">Ongoing</span>';
                      else echo '<span class="status-badge active">Completed</span>';
                    ?>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary btn-edit-exam" data-id="<?php echo $exam['id']; ?>" data-name="<?php echo htmlspecialchars($exam['name']); ?>" data-type="<?php echo htmlspecialchars($exam['type']); ?>" data-class-id="<?php echo htmlspecialchars($exam['class_id'] ?? ''); ?>" data-start-date="<?php echo htmlspecialchars($exam['start_date'] ?? ''); ?>" data-end-date="<?php echo htmlspecialchars($exam['end_date'] ?? ''); ?>" data-total-marks="<?php echo htmlspecialchars($exam['total_marks']); ?>" data-passing-marks="<?php echo htmlspecialchars($exam['passing_marks']); ?>" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-exam" data-id="<?php echo $exam['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Add Exam Modal -->
  <div class="modal fade" id="addExamModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add New Exam</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addExamForm">
            <div id="formAlert" class="alert d-none"></div>
            <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($current_session['id'] ?? '1'); ?>">
            
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Exam Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Mid-Term Examination" required>
              </div>
              
              <div class="col-md-6">
                <label class="form-label">Exam Type *</label>
                <select name="type" class="form-select" required>
                  <option value="mid_term">Mid Term</option>
                  <option value="final">Final Exam</option>
                  <option value="unit_test">Unit Test</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Class *</label>
                <select name="class_id" class="form-select" required>
                  <option value="">-- Select Class --</option>
                  <?php foreach($classes as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Start Date *</label>
                <input type="date" name="start_date" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">End Date *</label>
                <input type="date" name="end_date" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Total Marks *</label>
                <input type="number" name="total_marks" class="form-control" value="100" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Passing Marks *</label>
                <input type="number" name="passing_marks" class="form-control" value="40" required>
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveExamBtn">
                Save Exam
              </button>
            </div>
          </form>
</div>
     </div>
   </div>
   </div>
   </div>

   <!-- Edit Exam Modal -->
   <div class="modal fade" id="editExamModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
         <div class="modal-header bg-primary text-white border-0">
           <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Exam</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body p-4">
           <form id="editExamForm">
             <div id="editFormAlert" class="alert d-none"></div>
             <input type="hidden" name="id" id="editExamId">
             
             <div class="row g-3">
               <div class="col-12">
                 <label class="form-label">Exam Name *</label>
                 <input type="text" name="name" id="editExamName" class="form-control" required>
               </div>
               
               <div class="col-md-6">
                 <label class="form-label">Exam Type *</label>
                 <select name="type" id="editExamType" class="form-select" required>
                   <option value="mid_term">Mid Term</option>
                   <option value="final">Final Exam</option>
                   <option value="unit_test">Unit Test</option>
                   <option value="other">Other</option>
                 </select>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Class</label>
                 <select name="class_id" id="editExamClassId" class="form-select">
                   <option value="">-- Select Class --</option>
                   <?php foreach($classes as $c): ?>
                     <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                   <?php endforeach; ?>
                 </select>
               </div>

               <div class="col-md-6">
                 <label class="form-label">Start Date</label>
                 <input type="date" name="start_date" id="editExamStartDate" class="form-control">
               </div>
               <div class="col-md-6">
                 <label class="form-label">End Date</label>
                 <input type="date" name="end_date" id="editExamEndDate" class="form-control">
               </div>

               <div class="col-md-6">
                 <label class="form-label">Total Marks *</label>
                 <input type="number" name="total_marks" id="editExamTotalMarks" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Passing Marks *</label>
                 <input type="number" name="passing_marks" id="editExamPassingMarks" class="form-control" required>
               </div>
             </div>
             
             <div class="mt-4 text-end">
               <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="updateExamBtn">
                 Update Exam
               </button>
             </div>
           </form>
         </div>
       </div>
     </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/admin.js"></script>
  <script>
    // Search functionality
    document.getElementById('globalSearch').addEventListener('keyup', function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll('#examsTable tbody tr');
      rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // Add Exam AJAX
    document.getElementById('addExamForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const btn = document.getElementById('saveExamBtn');
      const alertDiv = document.getElementById('formAlert');
      const originalText = btn.innerHTML;
      
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      btn.disabled = true;
      alertDiv.className = 'alert d-none';
      
      const formData = new FormData(this);
      
      fetch('add_exam_action.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          alertDiv.className = 'alert alert-success';
          alertDiv.textContent = data.message;
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        } else {
          alertDiv.className = 'alert alert-danger';
          alertDiv.textContent = data.message;
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      })
      .catch(err => {
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'A network error occurred.';
        btn.innerHTML = originalText;
        btn.disabled = false;
      });
    });

// Delete Exam AJAX
     document.querySelectorAll('.btn-delete-exam').forEach(button => {
       button.addEventListener('click', function() {
         if (confirm('Are you sure you want to delete this exam schedule?')) {
           const examId = this.dataset.id;
           
           fetch('delete_exam_action.php', {
             method: 'POST',
             headers: {
               'Content-Type': 'application/x-www-form-urlencoded',
             },
             body: 'id=' + encodeURIComponent(examId)
           })
           .then(res => res.json())
           .then(data => {
             if (data.status === 'success') {
               window.location.reload();
             } else {
               alert(data.message);
             }
           })
           .catch(err => {
             alert('A network error occurred.');
           });
         }
       });
     });

     // Edit Exam Modal
     document.querySelectorAll('.btn-edit-exam').forEach(button => {
       button.addEventListener('click', function() {
         document.getElementById('editExamId').value = this.dataset.id;
         document.getElementById('editExamName').value = this.dataset.name;
         document.getElementById('editExamType').value = this.dataset.type;
         document.getElementById('editExamClassId').value = this.dataset.classId || '';
         document.getElementById('editExamStartDate').value = this.dataset.startDate || '';
         document.getElementById('editExamEndDate').value = this.dataset.endDate || '';
         document.getElementById('editExamTotalMarks').value = this.dataset.totalMarks;
         document.getElementById('editExamPassingMarks').value = this.dataset.passingMarks;
         new bootstrap.Modal(document.getElementById('editExamModal')).show();
       });
     });

     // Update Exam AJAX
     document.getElementById('editExamForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('updateExamBtn');
       const alertDiv = document.getElementById('editFormAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('update_exam_action.php', {
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
         alertDiv.textContent = 'A network error occurred.';
         btn.innerHTML = originalText;
         btn.disabled = false;
       });
     });
   </script>
</body>
</html>
