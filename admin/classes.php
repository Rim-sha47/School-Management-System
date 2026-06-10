<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch all classes and their sections & student counts
try {
    $stmt = $pdo->query("SELECT c.*, 
                                GROUP_CONCAT(DISTINCT s.name ORDER BY s.name ASC SEPARATOR ', ') AS sections, 
                                COUNT(DISTINCT std.id) AS student_count 
                         FROM classes c 
                         LEFT JOIN sections s ON c.id = s.class_id 
                         LEFT JOIN students std ON c.id = std.class_id AND std.status = 'active'
                         GROUP BY c.id 
                         ORDER BY c.numeric_name ASC");
    $classes = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching classes: " . $e->getMessage();
    $classes = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Manage Classes";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search classes...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Manage Classes</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Classes</li>
        </ol>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
        <i class="fas fa-plus me-2"></i> Add Class
      </button>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-school text-primary"></i> Class Rooms</h5>
      </div>
      <div class="dash-card-body p-0" style="overflow-x: auto;">
        <table class="dash-table" id="classesTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Class Name</th>
              <th>Numeric Name</th>
              <th>Sections</th>
              <th>Capacity</th>
              <th>Enrolled Students</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($classes)): ?>
              <tr><td colspan="7" class="text-center py-4">No classes found.</td></tr>
            <?php else: ?>
              <?php foreach($classes as $class): ?>
                <tr>
                  <td><?php echo htmlspecialchars($class['id']); ?></td>
                  <td><strong><?php echo htmlspecialchars($class['name']); ?></strong></td>
                  <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($class['numeric_name']); ?></span></td>
                  <td>
                    <?php if(!empty($class['sections'])): ?>
                      <?php foreach(explode(', ', $class['sections']) as $sec): ?>
                        <span class="badge bg-primary-pale text-primary border me-1"><?php echo htmlspecialchars($sec); ?></span>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <span class="text-muted small">No sections</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($class['capacity']); ?></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="progress-custom flex-grow-1 me-2" style="max-width: 100px;">
                        <?php 
                          $pct = min(100, round(($class['student_count'] / $class['capacity']) * 100)); 
                          $color = $pct > 90 ? 'var(--danger)' : ($pct > 70 ? 'var(--warning)' : 'var(--success)');
                        ?>
                        <div class="progress-fill" style="width: <?php echo $pct; ?>%; background: <?php echo $color; ?>;"></div>
                      </div>
                      <span class="small fw-semibold"><?php echo htmlspecialchars($class['student_count']); ?> / <?php echo htmlspecialchars($class['capacity']); ?></span>
                    </div>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary btn-edit-class" data-id="<?php echo $class['id']; ?>" data-name="<?php echo htmlspecialchars($class['name']); ?>" data-numeric="<?php echo htmlspecialchars($class['numeric_name']); ?>" data-capacity="<?php echo htmlspecialchars($class['capacity']); ?>" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-class" data-id="<?php echo $class['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
</div>
     </div>
   </main>

   <!-- Edit Class Modal -->
   <div class="modal fade" id="editClassModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
         <div class="modal-header bg-primary text-white border-0">
           <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Class</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body p-4">
           <form id="editClassForm">
             <div id="editFormAlert" class="alert d-none"></div>
             <input type="hidden" name="id" id="editClassId">
             
             <div class="row g-3">
               <div class="col-12">
                 <label class="form-label">Class Name *</label>
                 <input type="text" name="name" id="editClassName" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Numeric Name *</label>
                 <input type="number" name="numeric_name" id="editClassNumericName" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Capacity *</label>
                 <input type="number" name="capacity" id="editClassCapacity" class="form-control" required>
               </div>
             </div>
             
             <div class="mt-4 text-end">
               <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="updateClassBtn">
                 Update Class
               </button>
             </div>
           </form>
         </div>
       </div>
     </div>
   </div>

   <!-- Add Class Modal -->
  <div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add New Class</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addClassForm">
            <div id="formAlert" class="alert d-none"></div>
            
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Class Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Class 9" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Numeric Name *</label>
                <input type="number" name="numeric_name" class="form-control" placeholder="e.g. 9" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Capacity *</label>
                <input type="number" name="capacity" class="form-control" value="40" required>
              </div>
              <div class="col-12">
                <label class="form-label">Default Section Name</label>
                <input type="text" name="section_name" class="form-control" placeholder="e.g. A" value="A">
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveClassBtn">
                Save Class
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
       let rows = document.querySelectorAll('#classesTable tbody tr');
       rows.forEach(row => {
         let text = row.textContent.toLowerCase();
         row.style.display = text.includes(filter) ? '' : 'none';
       });
     });

     // Add Class AJAX
     document.getElementById('addClassForm').addEventListener('submit', function(e) {
       e.preventDefault();
       
       const btn = document.getElementById('saveClassBtn');
       const alertDiv = document.getElementById('formAlert');
       const originalText = btn.innerHTML;
       
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       
       const formData = new FormData(this);
       
       fetch('add_class_action.php', {
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

     // Edit Class Modal
     document.querySelectorAll('.btn-edit-class').forEach(button => {
       button.addEventListener('click', function() {
         document.getElementById('editClassId').value = this.dataset.id;
         document.getElementById('editClassName').value = this.dataset.name;
         document.getElementById('editClassNumericName').value = this.dataset.numeric;
         document.getElementById('editClassCapacity').value = this.dataset.capacity;
         new bootstrap.Modal(document.getElementById('editClassModal')).show();
       });
     });

     // Update Class AJAX
     document.getElementById('editClassForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('updateClassBtn');
       const alertDiv = document.getElementById('editFormAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('update_class_action.php', {
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

     // Delete Class AJAX
     document.querySelectorAll('.btn-delete-class').forEach(button => {
       button.addEventListener('click', function() {
         if (confirm('Are you sure you want to delete this class? This will delete all sections and associations.')) {
           const classId = this.dataset.id;
           
           fetch('delete_class_action.php', {
             method: 'POST',
             headers: {
               'Content-Type': 'application/x-www-form-urlencoded',
             },
             body: 'id=' + encodeURIComponent(classId)
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
