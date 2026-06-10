<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch all parents and their children
try {
    $stmt = $pdo->query("SELECT p.*, u.username, GROUP_CONCAT(CONCAT(s.first_name, ' ', s.last_name, ' (', s.registration_number, ')') SEPARATOR ', ') AS children 
                         FROM parents p 
                         LEFT JOIN users u ON p.user_id = u.id 
                         LEFT JOIN parent_students ps ON p.id = ps.parent_id 
                         LEFT JOIN students s ON ps.student_id = s.id 
                         GROUP BY p.id 
                         ORDER BY p.id DESC");
    $parents = $stmt->fetchAll();
    
    // Fetch all students for linking in modal
    $studentsStmt = $pdo->query("SELECT id, first_name, last_name, registration_number FROM students ORDER BY first_name ASC");
    $all_students = $studentsStmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching parents data: " . $e->getMessage();
    $parents = [];
    $all_students = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Manage Parents";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search parents...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Manage Parents</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Parents</li>
        </ol>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addParentModal">
        <i class="fas fa-plus me-2"></i> Add Parent
      </button>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-users text-primary"></i> Parent Records</h5>
      </div>
      <div class="dash-card-body p-0" style="overflow-x: auto;">
        <table class="dash-table" id="parentsTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Parent Name</th>
              <th>Relation</th>
              <th>Phone</th>
              <th>Email</th>
              <th>Children</th>
              <th>CNIC / Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($parents)): ?>
              <tr><td colspan="8" class="text-center py-4">No parent records found.</td></tr>
            <?php else: ?>
              <?php foreach($parents as $parent): ?>
                <tr>
                  <td><?php echo htmlspecialchars($parent['id']); ?></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar-sm me-2"><?php echo strtoupper(substr($parent['first_name'],0,1).substr($parent['last_name'],0,1)); ?></div>
                      <div>
                        <strong><?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?></strong>
                        <div class="text-muted small">User: <?php echo htmlspecialchars($parent['username'] ?? 'N/A'); ?></div>
                      </div>
                    </div>
                  </td>
                  <td><span class="badge bg-light text-dark border"><?php echo ucfirst(htmlspecialchars($parent['relation'])); ?></span></td>
                  <td><?php echo htmlspecialchars($parent['phone'] ?? 'N/A'); ?></td>
                  <td><?php echo htmlspecialchars($parent['email'] ?? 'N/A'); ?></td>
                  <td>
                    <div class="small" style="max-width: 250px; white-space: normal;">
                      <?php echo htmlspecialchars($parent['children'] ?? 'No linked student'); ?>
                    </div>
                  </td>
                  <td>
                    <div class="small text-muted mb-1"><?php echo htmlspecialchars($parent['cnic'] ?? 'N/A'); ?></div>
                    <span class="status-badge <?php echo $parent['status'] === 'active' ? 'active' : 'inactive'; ?>">
                      <?php echo ucfirst(htmlspecialchars($parent['status'])); ?>
                    </span>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary btn-edit-parent" data-id="<?php echo $parent['id']; ?>" data-first-name="<?php echo htmlspecialchars($parent['first_name']); ?>" data-last-name="<?php echo htmlspecialchars($parent['last_name']); ?>" data-relation="<?php echo htmlspecialchars($parent['relation']); ?>" data-email="<?php echo htmlspecialchars($parent['email'] ?? ''); ?>" data-phone="<?php echo htmlspecialchars($parent['phone'] ?? ''); ?>" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-parent" data-id="<?php echo $parent['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Add Parent Modal -->
  <div class="modal fade" id="addParentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Add New Parent</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addParentForm">
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
                <label class="form-label">Relation *</label>
                <select name="relation" class="form-select" required>
                  <option value="father">Father</option>
                  <option value="mother">Mother</option>
                  <option value="guardian">Guardian</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone *</label>
                <input type="text" name="phone" class="form-control" placeholder="e.g. 0300-1234567" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">CNIC</label>
                <input type="text" name="cnic" class="form-control" placeholder="e.g. 35201-xxxxxxx-x">
              </div>

              <div class="col-12">
                <label class="form-label">Occupation</label>
                <input type="text" name="occupation" class="form-control" placeholder="e.g. Business">
              </div>
              
              <div class="col-12">
                <label class="form-label">Link Student (Child) *</label>
                <select name="student_id" class="form-select" required>
                  <option value="">-- Select Student --</option>
                  <?php foreach($all_students as $student): ?>
                    <option value="<?php echo $student['id']; ?>">
                      <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['registration_number'] . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
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
              <button type="submit" class="btn btn-primary" id="saveParentBtn">
                Save Parent
              </button>
            </div>
          </form>
        </div>
</div>
     </div>
   </div>
   </div>
   </div>

   <!-- Edit Parent Modal -->
   <div class="modal fade" id="editParentModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
         <div class="modal-header bg-primary text-white border-0">
           <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Parent</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body p-4">
           <form id="editParentForm">
             <div id="editFormAlert" class="alert d-none"></div>
             <input type="hidden" name="id" id="editParentId">
             
             <div class="row g-3">
               <div class="col-md-6">
                 <label class="form-label">First Name *</label>
                 <input type="text" name="first_name" id="editParentFirstName" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Last Name *</label>
                 <input type="text" name="last_name" id="editParentLastName" class="form-control" required>
               </div>
               
               <div class="col-md-6">
                 <label class="form-label">Relation *</label>
                 <select name="relation" id="editParentRelation" class="form-select" required>
                   <option value="father">Father</option>
                   <option value="mother">Mother</option>
                   <option value="guardian">Guardian</option>
                 </select>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Phone *</label>
                 <input type="text" name="phone" id="editParentPhone" class="form-control" required>
               </div>

               <div class="col-md-6">
                 <label class="form-label">Email</label>
                 <input type="email" name="email" id="editParentEmail" class="form-control">
               </div>
               <div class="col-md-6">
                 <label class="form-label">CNIC</label>
                 <input type="text" name="cnic" class="form-control" placeholder="e.g. 35201-xxxxxxx-x">
               </div>

               <div class="col-12">
                 <label class="form-label">Occupation</label>
                 <input type="text" name="occupation" id="editParentOccupation" class="form-control">
               </div>
             </div>
             
             <div class="mt-4 text-end">
               <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="updateParentBtn">
                 Update Parent
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
      let rows = document.querySelectorAll('#parentsTable tbody tr');
      rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // Add Parent AJAX
    document.getElementById('addParentForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const btn = document.getElementById('saveParentBtn');
      const alertDiv = document.getElementById('formAlert');
      const originalText = btn.innerHTML;
      
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      btn.disabled = true;
      alertDiv.className = 'alert d-none';
      
      const formData = new FormData(this);
      
      fetch('add_parent_action.php', {
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

    // Edit Parent Modal
     document.querySelectorAll('.btn-edit-parent').forEach(button => {
       button.addEventListener('click', function() {
         document.getElementById('editParentId').value = this.dataset.id;
         document.getElementById('editParentFirstName').value = this.dataset.firstName;
         document.getElementById('editParentLastName').value = this.dataset.lastName;
         document.getElementById('editParentRelation').value = this.dataset.relation;
         document.getElementById('editParentEmail').value = this.dataset.email;
         document.getElementById('editParentPhone').value = this.dataset.phone;
         new bootstrap.Modal(document.getElementById('editParentModal')).show();
       });
     });

     // Update Parent AJAX
     document.getElementById('editParentForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('updateParentBtn');
       const alertDiv = document.getElementById('editFormAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('update_parent_action.php', {
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

     // Delete Parent AJAX
    document.querySelectorAll('.btn-delete-parent').forEach(button => {
      button.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this parent? This will also delete their login account.')) {
          const parentId = this.dataset.id;
          
          fetch('delete_parent_action.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + encodeURIComponent(parentId)
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
