<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch all notices
try {
    $stmt = $pdo->query("SELECT n.*, u.username 
                         FROM notices n 
                         LEFT JOIN users u ON n.created_by = u.id 
                         ORDER BY n.publish_date DESC, n.id DESC");
    $notices = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching notices: " . $e->getMessage();
    $notices = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Notice Board";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search notices...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Notice Board</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Notice Board</li>
        </ol>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoticeModal">
        <i class="fas fa-plus me-2"></i> Add Notice
      </button>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-bullhorn text-primary"></i> School Announcements</h5>
      </div>
      <div class="dash-card-body p-4">
        <div id="noticeContainer">
          <?php if(empty($notices)): ?>
            <div class="text-center py-4 text-muted">No notices published.</div>
          <?php else: ?>
            <?php foreach($notices as $notice): ?>
              <?php 
                $priority = $notice['priority'];
                $dotColor = 'var(--primary)';
                $badgeClass = 'leave';
                if ($priority === 'urgent') { $dotColor = 'var(--danger)'; $badgeClass = 'overdue'; }
                elseif ($priority === 'important') { $dotColor = 'var(--warning)'; $badgeClass = 'late'; }
              ?>
<div class="notice-item pb-3 mb-3 border-bottom d-flex justify-content-between align-items-start" data-searchable>
                 <div class="d-flex gap-3">
                   <div class="notice-dot mt-2" style="background: <?php echo $dotColor; ?>;"></div>
                   <div>
                     <h5 class="mb-1 text-dark fw-bold"><?php echo htmlspecialchars($notice['title']); ?></h5>
                     <p class="text-muted mb-2" style="font-size: 0.88rem; max-width: 700px; white-space: pre-line;"><?php echo htmlspecialchars($notice['content']); ?></p>
                     <div class="d-flex flex-wrap gap-2 align-items-center text-muted small" style="font-size: 0.75rem;">
                       <span class="status-badge <?php echo $badgeClass; ?>"><?php echo ucfirst(htmlspecialchars($priority)); ?></span>
                       <span>Target: <strong class="text-primary text-uppercase"><?php echo htmlspecialchars($notice['audience']); ?></strong></span>
                       <span>• Published: <?php echo htmlspecialchars($notice['publish_date']); ?></span>
                       <span>• Author: @<?php echo htmlspecialchars($notice['username'] ?? 'admin'); ?></span>
                     </div>
                   </div>
                 </div>
                 <div>
                   <button class="btn btn-sm btn-outline-primary btn-edit-notice" data-id="<?php echo $notice['id']; ?>" data-title="<?php echo htmlspecialchars($notice['title']); ?>" data-content="<?php echo htmlspecialchars($notice['content']); ?>" data-audience="<?php echo htmlspecialchars($notice['audience']); ?>" data-priority="<?php echo htmlspecialchars($notice['priority']); ?>" title="Edit"><i class="fas fa-edit"></i></button>
                   <button class="btn btn-sm btn-outline-danger btn-delete-notice" data-id="<?php echo $notice['id']; ?>" title="Delete Announcement"><i class="fas fa-trash"></i></button>
                 </div>
               </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Notice Modal -->
  <div class="modal fade" id="addNoticeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-bullhorn me-2"></i> Publish New Notice</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addNoticeForm">
            <div id="noticeFormAlert" class="alert d-none"></div>
            
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Winter Holiday Schedule" required>
              </div>
              <div class="col-12">
                <label class="form-label">Content *</label>
                <textarea name="content" class="form-control" rows="4" placeholder="Write notice details here..." required></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Audience *</label>
                <select name="audience" class="form-select" required>
                  <option value="all">Everyone</option>
                  <option value="students">Students Only</option>
                  <option value="teachers">Teachers Only</option>
                  <option value="parents">Parents Only</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Priority *</label>
                <select name="priority" class="form-select" required>
                  <option value="normal">Normal</option>
                  <option value="important">Important</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Publish Date *</label>
                <input type="date" name="publish_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control">
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveNoticeBtn">Publish Notice</button>
            </div>
          </form>
        </div>
</div>
     </div>
   </div>
   </div>
   </div>

   <!-- Edit Notice Modal -->
   <div class="modal fade" id="editNoticeModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
         <div class="modal-header bg-primary text-white border-0">
           <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Notice</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body p-4">
           <form id="editNoticeForm">
             <div id="editNoticeAlert" class="alert d-none"></div>
             <input type="hidden" name="id" id="editNoticeId">
             <div class="row g-3">
               <div class="col-12">
                 <label class="form-label">Title *</label>
                 <input type="text" name="title" id="editNoticeTitle" class="form-control" required>
               </div>
               <div class="col-12">
                 <label class="form-label">Content *</label>
                 <textarea name="content" id="editNoticeContent" class="form-control" rows="4" required></textarea>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Audience *</label>
                 <select name="audience" id="editNoticeAudience" class="form-select" required>
                   <option value="all">Everyone</option>
                   <option value="students">Students Only</option>
                   <option value="teachers">Teachers Only</option>
                   <option value="parents">Parents Only</option>
                 </select>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Priority *</label>
                 <select name="priority" id="editNoticePriority" class="form-select" required>
                   <option value="normal">Normal</option>
                   <option value="important">Important</option>
                   <option value="urgent">Urgent</option>
                 </select>
               </div>
             </div>
             <div class="mt-4 text-end">
               <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="updateNoticeBtn">Update Notice</button>
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
      let elements = document.querySelectorAll('#noticeContainer [data-searchable]');
      elements.forEach(el => {
        let text = el.textContent.toLowerCase();
        el.style.setProperty('display', text.includes(filter) ? 'flex' : 'none', 'important');
      });
    });

    // Add Notice AJAX
    document.getElementById('addNoticeForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = document.getElementById('saveNoticeBtn');
      const alertDiv = document.getElementById('noticeFormAlert');
      const originalText = btn.innerHTML;
      
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publishing...';
      btn.disabled = true;
      alertDiv.className = 'alert d-none';
      
      const formData = new FormData(this);
      fetch('add_notice_action.php', {
        method: 'POST',
        body: formData
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

    // Delete Notice AJAX
    document.querySelectorAll('.btn-delete-notice').forEach(button => {
      button.addEventListener('click', function() {
        if (confirm('Delete this notice announcement?')) {
          const id = this.dataset.id;
          
          fetch('delete_notice_action.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + encodeURIComponent(id)
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

     // Edit Notice Modal
     document.querySelectorAll('.btn-edit-notice').forEach(button => {
       button.addEventListener('click', function() {
         document.getElementById('editNoticeId').value = this.dataset.id;
         document.getElementById('editNoticeTitle').value = this.dataset.title;
         document.getElementById('editNoticeContent').value = this.dataset.content;
         document.getElementById('editNoticeAudience').value = this.dataset.audience;
         document.getElementById('editNoticePriority').value = this.dataset.priority;
         new bootstrap.Modal(document.getElementById('editNoticeModal')).show();
       });
     });

     // Update Notice AJAX
     document.getElementById('editNoticeForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('updateNoticeBtn');
       const alertDiv = document.getElementById('editNoticeAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('update_notice_action.php', { method: 'POST', body: new FormData(this) })
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
       });
     });
   </script>
  </body>
</html>
