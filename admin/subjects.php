<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch all subjects
try {
    $stmt = $pdo->query("SELECT s.*, c.name AS class_name 
                         FROM subjects s 
                         LEFT JOIN classes c ON s.class_id = c.id 
                         ORDER BY s.id DESC");
    $subjects = $stmt->fetchAll();
    
    // Fetch classes for dropdown selection
    $classesStmt = $pdo->query("SELECT id, name FROM classes ORDER BY numeric_name ASC");
    $classes = $classesStmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching subjects data: " . $e->getMessage();
    $subjects = [];
    $classes = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Manage Subjects";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search subjects...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Manage Subjects</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Subjects</li>
        </ol>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
        <i class="fas fa-plus me-2"></i> Add Subject
      </button>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-book text-primary"></i> Subject Catalog</h5>
      </div>
      <div class="dash-card-body p-0" style="overflow-x: auto;">
        <table class="dash-table" id="subjectsTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Subject Name</th>
              <th>Subject Code</th>
              <th>Class</th>
              <th>Type</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($subjects)): ?>
              <tr><td colspan="6" class="text-center py-4">No subjects found.</td></tr>
            <?php else: ?>
              <?php foreach($subjects as $subject): ?>
                <tr>
                  <td><?php echo htmlspecialchars($subject['id']); ?></td>
                  <td><strong><?php echo htmlspecialchars($subject['name']); ?></strong></td>
                  <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($subject['code']); ?></span></td>
                  <td><?php echo htmlspecialchars($subject['class_name'] ?? 'General / All Classes'); ?></td>
                  <td>
                    <?php 
                      $type = $subject['type'];
                      if($type === 'theory') echo '<span class="status-badge leave">Theory</span>';
                      elseif($type === 'practical') echo '<span class="status-badge late">Practical</span>';
                      else echo '<span class="status-badge active">Both</span>';
                    ?>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary btn-edit-subject" data-id="<?php echo $subject['id']; ?>" data-name="<?php echo htmlspecialchars($subject['name']); ?>" data-code="<?php echo htmlspecialchars($subject['code']); ?>" data-type="<?php echo htmlspecialchars($subject['type']); ?>" data-class-id="<?php echo htmlspecialchars($subject['class_id'] ?? ''); ?>" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-subject" data-id="<?php echo $subject['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Add Subject Modal -->
  <div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add New Subject</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addSubjectForm">
            <div id="formAlert" class="alert d-none"></div>
            
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Subject Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Chemistry" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Subject Code *</label>
                <input type="text" name="code" class="form-control" placeholder="e.g. CHEM-101" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Subject Type *</label>
                <select name="type" class="form-select" required>
                  <option value="theory">Theory</option>
                  <option value="practical">Practical</option>
                  <option value="both">Both</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Linked Class (Optional)</label>
                <select name="class_id" class="form-select">
                  <option value="">-- General / All Classes --</option>
                  <?php foreach($classes as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveSubjectBtn">
                Save Subject
              </button>
            </div>
          </form>
        </div>
</div>
     </div>
   </div>
   </div>
   </div>

   <!-- Edit Subject Modal -->
   <div class="modal fade" id="editSubjectModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
         <div class="modal-header bg-primary text-white border-0">
           <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Subject</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body p-4">
           <form id="editSubjectForm">
             <div id="editFormAlert" class="alert d-none"></div>
             <input type="hidden" name="id" id="editSubjectId">
             
             <div class="row g-3">
               <div class="col-12">
                 <label class="form-label">Subject Name *</label>
                 <input type="text" name="name" id="editSubjectName" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Subject Code *</label>
                 <input type="text" name="code" id="editSubjectCode" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Subject Type *</label>
                 <select name="type" id="editSubjectType" class="form-select" required>
                   <option value="theory">Theory</option>
                   <option value="practical">Practical</option>
                   <option value="both">Both</option>
                 </select>
               </div>
               <div class="col-12">
                 <label class="form-label">Linked Class (Optional)</label>
                 <select name="class_id" id="editSubjectClassId" class="form-select">
                   <option value="">-- General / All Classes --</option>
                   <?php foreach($classes as $c): ?>
                     <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                   <?php endforeach; ?>
                 </select>
               </div>
             </div>
             
             <div class="mt-4 text-end">
               <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="updateSubjectBtn">
                 Update Subject
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
      let rows = document.querySelectorAll('#subjectsTable tbody tr');
      rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // Add Subject AJAX
    document.getElementById('addSubjectForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const btn = document.getElementById('saveSubjectBtn');
      const alertDiv = document.getElementById('formAlert');
      const originalText = btn.innerHTML;
      
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      btn.disabled = true;
      alertDiv.className = 'alert d-none';
      
      const formData = new FormData(this);
      
      fetch('add_subject_action.php', {
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

// Delete Subject AJAX
     document.querySelectorAll('.btn-delete-subject').forEach(button => {
       button.addEventListener('click', function() {
         if (confirm('Are you sure you want to delete this subject?')) {
           const subjectId = this.dataset.id;
           
           fetch('delete_subject_action.php', {
             method: 'POST',
             headers: {
               'Content-Type': 'application/x-www-form-urlencoded',
             },
             body: 'id=' + encodeURIComponent(subjectId)
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

     // Edit Subject Modal
     document.querySelectorAll('.btn-edit-subject').forEach(button => {
       button.addEventListener('click', function() {
         document.getElementById('editSubjectId').value = this.dataset.id;
         document.getElementById('editSubjectName').value = this.dataset.name;
         document.getElementById('editSubjectCode').value = this.dataset.code;
         document.getElementById('editSubjectType').value = this.dataset.type;
         document.getElementById('editSubjectClassId').value = this.dataset.classId || '';
         new bootstrap.Modal(document.getElementById('editSubjectModal')).show();
       });
     });

     // Update Subject AJAX
     document.getElementById('editSubjectForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('updateSubjectBtn');
       const alertDiv = document.getElementById('editFormAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('update_subject_action.php', {
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
