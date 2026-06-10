<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch all teachers
try {
    $stmt = $pdo->query("SELECT t.*, u.username FROM teachers t JOIN users u ON t.user_id = u.id ORDER BY t.id DESC");
    $teachers = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching teachers: " . $e->getMessage();
    $teachers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Manage Teachers";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search teachers...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Manage Teachers</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Teachers</li>
        </ol>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
        <i class="fas fa-plus me-2"></i> Add Teacher
      </button>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-chalkboard-teacher text-primary"></i> Faculty Members</h5>
      </div>
      <div class="dash-card-body p-0" style="overflow-x: auto;">
        <table class="dash-table" id="teachersTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Teacher ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Department</th>
              <th>Qualification</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($teachers)): ?>
              <tr><td colspan="8" class="text-center py-4">No teachers found.</td></tr>
            <?php else: ?>
              <?php foreach($teachers as $teacher): ?>
                <tr>
                  <td><?php echo htmlspecialchars($teacher['id']); ?></td>
                  <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($teacher['teacher_id']); ?></span></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar-sm me-2"><?php echo strtoupper(substr($teacher['first_name'],0,1).substr($teacher['last_name'],0,1)); ?></div>
                      <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                    </div>
                  </td>
                  <td><?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?></td>
                  <td><?php echo htmlspecialchars($teacher['department'] ?? 'N/A'); ?></td>
                  <td><?php echo htmlspecialchars($teacher['qualification'] ?? 'N/A'); ?></td>
                  <td>
                    <span class="status-badge <?php echo $teacher['status'] === 'active' ? 'active' : 'inactive'; ?>">
                      <?php echo ucfirst(htmlspecialchars($teacher['status'])); ?>
                    </span>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary btn-edit-teacher" data-id="<?php echo $teacher['id']; ?>" data-first-name="<?php echo htmlspecialchars($teacher['first_name']); ?>" data-last-name="<?php echo htmlspecialchars($teacher['last_name']); ?>" data-email="<?php echo htmlspecialchars($teacher['email'] ?? ''); ?>" data-phone="<?php echo htmlspecialchars($teacher['phone'] ?? ''); ?>" data-department="<?php echo htmlspecialchars($teacher['department'] ?? ''); ?>" data-qualification="<?php echo htmlspecialchars($teacher['qualification'] ?? ''); ?>" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-teacher" data-id="<?php echo $teacher['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Add Teacher Modal -->
  <div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Add New Teacher</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addTeacherForm">
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
                <label class="form-label">Teacher ID *</label>
                <input type="text" name="teacher_id" class="form-control" placeholder="e.g. TCH-1003" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
              </div>

              <div class="col-md-6">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" placeholder="e.g. Science">
              </div>
              <div class="col-md-6">
                <label class="form-label">Qualification</label>
                <input type="text" name="qualification" class="form-control" placeholder="e.g. M.Sc">
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
              <button type="submit" class="btn btn-primary" id="saveTeacherBtn">
                Save Teacher
              </button>
            </div>
          </form>
        </div>
</div>
     </div>
   </div>
   </div>
   </div>

   <!-- Edit Teacher Modal -->
   <div class="modal fade" id="editTeacherModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
         <div class="modal-header bg-primary text-white border-0">
           <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Teacher</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body p-4">
           <form id="editTeacherForm">
             <div id="editFormAlert" class="alert d-none"></div>
             <input type="hidden" name="id" id="editTeacherId">
             
             <div class="row g-3">
               <div class="col-md-6">
                 <label class="form-label">First Name *</label>
                 <input type="text" name="first_name" id="editTeacherFirstName" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Last Name *</label>
                 <input type="text" name="last_name" id="editTeacherLastName" class="form-control" required>
               </div>
               
               <div class="col-md-6">
                 <label class="form-label">Email</label>
                 <input type="email" name="email" id="editTeacherEmail" class="form-control">
               </div>
               <div class="col-md-6">
                 <label class="form-label">Phone</label>
                 <input type="text" name="phone" id="editTeacherPhone" class="form-control">
               </div>

               <div class="col-md-6">
                 <label class="form-label">Department</label>
                 <input type="text" name="department" id="editTeacherDepartment" class="form-control">
               </div>
               <div class="col-md-6">
                 <label class="form-label">Qualification</label>
                 <input type="text" name="qualification" id="editTeacherQualification" class="form-control">
               </div>
             </div>
             
             <div class="mt-4 text-end">
               <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="updateTeacherBtn">
                 Update Teacher
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
       let rows = document.querySelectorAll('#teachersTable tbody tr');
       rows.forEach(row => {
         let text = row.textContent.toLowerCase();
         row.style.display = text.includes(filter) ? '' : 'none';
       });
     });

     // Add Teacher AJAX
     document.getElementById('addTeacherForm').addEventListener('submit', function(e) {
       e.preventDefault();
       
       const btn = document.getElementById('saveTeacherBtn');
       const alertDiv = document.getElementById('formAlert');
       const originalText = btn.innerHTML;
       
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       
       const formData = new FormData(this);
       
       fetch('add_teacher_action.php', {
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

     // Edit Teacher Modal
     document.querySelectorAll('.btn-edit-teacher').forEach(button => {
       button.addEventListener('click', function() {
         document.getElementById('editTeacherId').value = this.dataset.id;
         document.getElementById('editTeacherFirstName').value = this.dataset.firstName;
         document.getElementById('editTeacherLastName').value = this.dataset.lastName;
         document.getElementById('editTeacherEmail').value = this.dataset.email;
         document.getElementById('editTeacherPhone').value = this.dataset.phone;
         document.getElementById('editTeacherDepartment').value = this.dataset.department;
         document.getElementById('editTeacherQualification').value = this.dataset.qualification;
         new bootstrap.Modal(document.getElementById('editTeacherModal')).show();
       });
     });

     // Update Teacher AJAX
     document.getElementById('editTeacherForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('updateTeacherBtn');
       const alertDiv = document.getElementById('editFormAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('update_teacher_action.php', {
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

     // Delete Teacher AJAX
     document.querySelectorAll('.btn-delete-teacher').forEach(button => {
       button.addEventListener('click', function() {
         if (confirm('Are you sure you want to delete this teacher? This will also delete their login account.')) {
           const teacherId = this.dataset.id;
           
           fetch('delete_teacher_action.php', {
             method: 'POST',
             headers: {
               'Content-Type': 'application/x-www-form-urlencoded',
             },
             body: 'id=' + encodeURIComponent(teacherId)
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
   </script>
  </body>
</html>
