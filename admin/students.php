<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch all students
try {
    $stmt = $pdo->query("SELECT s.*, u.username FROM students s JOIN users u ON s.user_id = u.id ORDER BY s.id DESC");
    $students = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching students: " . $e->getMessage();
    $students = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Manage Students";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search students...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Manage Students</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Students</li>
        </ol>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="fas fa-plus me-2"></i> Add Student
      </button>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-list"></i> Enrolled Students</h5>
      </div>
      <div class="dash-card-body p-0" style="overflow-x: auto;">
        <table class="dash-table" id="studentsTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Registration No</th>
              <th>Name</th>
              <th>Class ID</th>
              <th>Username</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($students)): ?>
              <tr><td colspan="6" class="text-center py-4">No students found.</td></tr>
            <?php else: ?>
              <?php foreach($students as $student): ?>
                <tr>
                  <td><?php echo htmlspecialchars($student['id']); ?></td>
                  <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($student['registration_number']); ?></span></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar-sm me-2"><?php echo strtoupper(substr($student['first_name'],0,1).substr($student['last_name'],0,1)); ?></div>
                      <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                    </div>
                  </td>
                  <td>Class <?php echo htmlspecialchars($student['class_id']); ?></td>
                  <td><?php echo htmlspecialchars($student['username']); ?></td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary btn-edit-student" data-id="<?php echo $student['id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-student" data-id="<?php echo $student['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Add Student Modal -->
  <div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Add New Student</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addStudentForm">
            <div id="formAlert" class="alert d-none"></div>
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">First Name *</label>
                <input type="text" name="first_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Last Name *</label>
                <input type="text" name="last_name" class="form-control" required>
              </div>
              
              <div class="col-md-6">
                <label class="form-label">Registration No *</label>
                <input type="text" name="registration_number" class="form-control" placeholder="e.g. STD-2002" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Class ID *</label>
                <input type="number" name="class_id" class="form-control" value="1" required>
              </div>

              <div class="col-12 mt-4">
                <h6><i class="fas fa-key me-2 text-muted"></i> Account Credentials</h6>
                <hr class="mt-1 mb-3">
              </div>

              <div class="col-md-6">
                <label class="form-label">Username *</label>
                <input type="text" name="username" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required>
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveStudentBtn">
                Save Student
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

<!-- Edit Student Modal -->
   <div class="modal fade" id="editStudentModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
         <div class="modal-header bg-primary text-white border-0">
           <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Student</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body p-4">
           <form id="editStudentForm">
             <div id="editFormAlert" class="alert d-none"></div>
             <input type="hidden" name="id" id="editStudentId">
             
             <div class="row g-3">
               <div class="col-md-6">
                 <label class="form-label">First Name *</label>
                 <input type="text" name="first_name" id="editStudentFirstName" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Last Name *</label>
                 <input type="text" name="last_name" id="editStudentLastName" class="form-control" required>
               </div>
               
               <div class="col-md-6">
                 <label class="form-label">Registration No *</label>
                 <input type="text" name="registration_number" id="editStudentRegNo" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Class ID *</label>
                 <input type="number" name="class_id" id="editStudentClassId" class="form-control" required>
               </div>
             </div>
             
             <div class="mt-4 text-end">
               <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="updateStudentBtn">
                 Update Student
               </button>
             </div>
           </form>
         </div>
       </div>
     </div>
   </div>
   
   <!-- Delete Student Modal -->
   <div class="modal fade" id="deleteStudentModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0">
         <div class="modal-body p-4 text-center">
           <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
           <h5 class="mt-3">Delete Student?</h5>
           <p class="text-muted">This will permanently delete the student and their account.</p>
           <input type="hidden" id="deleteStudentId">
           <div class="mt-4">
             <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
             <button type="button" class="btn btn-danger" id="confirmDeleteStudent">Delete Student</button>
           </div>
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
       let rows = document.querySelectorAll('#studentsTable tbody tr');
       rows.forEach(row => {
         let text = row.textContent.toLowerCase();
         row.style.display = text.includes(filter) ? '' : 'none';
       });
     });

     // Add Student AJAX
     document.getElementById('addStudentForm').addEventListener('submit', function(e) {
       e.preventDefault();
       
       const btn = document.getElementById('saveStudentBtn');
       const alertDiv = document.getElementById('formAlert');
       const originalText = btn.innerHTML;
       
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       
       const formData = new FormData(this);
       
       fetch('add_student_action.php', {
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

     // Edit Student - populate modal and show
     document.querySelectorAll('.btn-edit-student').forEach(button => {
       button.addEventListener('click', function() {
         const row = this.closest('tr');
         document.getElementById('editStudentId').value = this.dataset.id;
         document.getElementById('editStudentFirstName').value = row.cells[2].querySelector('div')?.textContent.trim().split(' ')[0] || '';
         document.getElementById('editStudentLastName').value = row.cells[2].querySelector('div')?.textContent.trim().split(' ').slice(1).join(' ') || '';
         document.getElementById('editStudentRegNo').value = row.cells[1].textContent.trim();
         document.getElementById('editStudentClassId').value = row.cells[3].textContent.replace('Class ', '').trim();
         new bootstrap.Modal(document.getElementById('editStudentModal')).show();
       });
     });

     // Update Student AJAX
     document.getElementById('editStudentForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('updateStudentBtn');
       const alertDiv = document.getElementById('editFormAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('update_student_action.php', {
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

     // Delete Student
     document.querySelectorAll('.btn-delete-student').forEach(button => {
       button.addEventListener('click', function() {
         document.getElementById('deleteStudentId').value = this.dataset.id;
         new bootstrap.Modal(document.getElementById('deleteStudentModal')).show();
       });
     });
     
     document.getElementById('confirmDeleteStudent').addEventListener('click', function() {
       const studentId = document.getElementById('deleteStudentId').value;
       fetch('delete_student_action.php', {
         method: 'POST',
         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
         body: 'id=' + encodeURIComponent(studentId)
       })
       .then(res => res.json())
       .then(data => {
         if(data.status === 'success') { window.location.reload(); }
         else { alert(data.message); }
       });
     });
   </script>
  </body>
</html>
