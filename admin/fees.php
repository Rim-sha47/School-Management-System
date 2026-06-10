<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch overall stats
try {
    $paidStmt = $pdo->query("SELECT SUM(total_paid) FROM fee_payments WHERE status = 'paid'");
    $total_paid = $paidStmt->fetchColumn() ?? 0;

    $unpaidStmt = $pdo->query("SELECT SUM(amount) FROM fee_payments WHERE status = 'unpaid'");
    $total_unpaid = $unpaidStmt->fetchColumn() ?? 0;
    
    // Fetch fee structures
    $structStmt = $pdo->query("SELECT fs.*, c.name AS class_name 
                               FROM fee_structures fs 
                               LEFT JOIN classes c ON fs.class_id = c.id 
                               ORDER BY fs.id DESC");
    $structures = $structStmt->fetchAll();

    // Fetch fee payments history
    $paymentStmt = $pdo->query("SELECT fp.*, s.first_name, s.last_name, s.registration_number, fs.name AS fee_name 
                                FROM fee_payments fp 
                                JOIN students s ON fp.student_id = s.id 
                                LEFT JOIN fee_structures fs ON fp.fee_structure_id = fs.id 
                                ORDER BY fp.id DESC");
    $payments = $paymentStmt->fetchAll();

    // Fetch classes for dropdown selection
    $classesStmt = $pdo->query("SELECT id, name FROM classes ORDER BY numeric_name ASC");
    $classes = $classesStmt->fetchAll();

    // Fetch students list for payment modal dropdown
    $studentsStmt = $pdo->query("SELECT id, first_name, last_name, registration_number FROM students ORDER BY first_name ASC");
    $students = $studentsStmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching fee data: " . $e->getMessage();
    $structures = [];
    $payments = [];
    $classes = [];
    $students = [];
    $total_paid = 0;
    $total_unpaid = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Manage Fees";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search fee entries...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Manage Fees & Payments</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Fees</li>
        </ol>
      </div>
      <div>
        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#addStructureModal">
          <i class="fas fa-plus me-1"></i> Add Fee Structure
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#collectFeeModal">
          <i class="fas fa-hand-holding-usd me-1"></i> Collect Fee Payment
        </button>
      </div>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Widgets -->
    <div class="row g-4 mb-4">
      <div class="col-md-6 col-xl-3">
        <div class="widget-card green">
          <div class="widget-icon"><i class="fas fa-money-bill-wave"></i></div>
          <div class="widget-info">
            <h2>PKR <?php echo number_format($total_paid); ?></h2>
            <p>Total Fees Collected</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="widget-card red">
          <div class="widget-icon"><i class="fas fa-exclamation-triangle"></i></div>
          <div class="widget-info">
            <h2>PKR <?php echo number_format($total_unpaid); ?></h2>
            <p>Outstanding Invoices</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs Container -->
    <ul class="nav nav-tabs mb-4 border-bottom-0" id="feeTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active border-0 bg-transparent fw-semibold" id="payments-tab" data-bs-toggle="tab" data-bs-target="#paymentsTab" type="button">Payment Records</button>
      </li>
      <li class="nav-item">
        <button class="nav-link border-0 bg-transparent fw-semibold" id="structures-tab" data-bs-toggle="tab" data-bs-target="#structuresTab" type="button">Fee Structures</button>
      </li>
    </ul>

    <div class="tab-content" id="feeTabContent">
      <!-- Payments Tab -->
      <div class="tab-pane fade show active" id="paymentsTab">
        <div class="dash-card">
          <div class="dash-card-header">
            <h5><i class="fas fa-file-invoice-dollar text-primary"></i> Fee Payments History</h5>
          </div>
          <div class="dash-card-body p-0" style="overflow-x: auto;">
            <table class="dash-table" id="paymentsTable">
              <thead>
                <tr>
                  <th>Challan No</th>
                  <th>Student Name</th>
                  <th>Fee Type</th>
                  <th>Month/Year</th>
                  <th>Paid Amount</th>
                  <th>Date</th>
                  <th>Method</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($payments)): ?>
                  <tr><td colspan="8" class="text-center py-4">No payment records found.</td></tr>
                <?php else: ?>
                  <?php foreach($payments as $p): ?>
                    <tr>
                      <td><span class="badge bg-light text-dark border font-monospace"><?php echo htmlspecialchars($p['challan_no']); ?></span></td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm me-2"><?php echo strtoupper(substr($p['first_name'],0,1).substr($p['last_name'],0,1)); ?></div>
                          <div>
                            <strong><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></strong>
                            <div class="text-muted small"><?php echo htmlspecialchars($p['registration_number']); ?></div>
                          </div>
                        </div>
                      </td>
                      <td><?php echo htmlspecialchars($p['fee_name'] ?? 'General Collection'); ?></td>
                      <td><?php echo htmlspecialchars($p['month_year'] ?? 'N/A'); ?></td>
                      <td><strong>PKR <?php echo number_format($p['total_paid']); ?></strong></td>
                      <td><?php echo htmlspecialchars($p['payment_date'] ?? 'N/A'); ?></td>
                      <td><span class="badge bg-light text-dark border text-uppercase"><?php echo htmlspecialchars($p['payment_method']); ?></span></td>
                      <td>
                        <span class="status-badge <?php echo $p['status']; ?>">
                          <?php echo ucfirst(htmlspecialchars($p['status'])); ?>
                        </span>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary btn-edit-payment" data-id="<?php echo $p['id']; ?>" data-status="<?php echo htmlspecialchars($p['status']); ?>" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger btn-delete-payment" data-id="<?php echo $p['id']; ?>" title="Remove"><i class="fas fa-trash"></i></button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Structures Tab -->
      <div class="tab-pane fade" id="structuresTab">
        <div class="dash-card">
          <div class="dash-card-header">
            <h5><i class="fas fa-cogs text-primary"></i> Fee Structure rules</h5>
          </div>
          <div class="dash-card-body p-0" style="overflow-x: auto;">
<table class="dash-table" id="structuresTable">
               <thead>
                 <tr>
                   <th>ID</th>
                   <th>Structure Name</th>
                   <th>Class</th>
                   <th>Amount</th>
                   <th>Frequency</th>
                   <th>Due Day</th>
                   <th>Late Fee</th>
                   <th>Status</th>
                   <th>Actions</th>
                 </tr>
               </thead>
               <tbody>
<?php if(empty($structures)): ?>
                   <tr><td colspan="9" class="text-center py-4">No structures found.</td></tr>
                 <?php else: ?>
                   <?php foreach($structures as $s): ?>
                     <tr>
                       <td><?php echo htmlspecialchars($s['id']); ?></td>
                       <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                       <td><?php echo htmlspecialchars($s['class_name'] ?? 'All Classes'); ?></td>
                       <td><strong>PKR <?php echo number_format($s['amount']); ?></strong></td>
                       <td><span class="badge bg-light text-dark border text-uppercase"><?php echo htmlspecialchars($s['type']); ?></span></td>
                       <td>Day <?php echo htmlspecialchars($s['due_day']); ?> of Month</td>
                       <td>PKR <?php echo number_format($s['late_fee']); ?></td>
                       <td>
                         <span class="status-badge <?php echo $s['is_active'] ? 'active' : 'inactive'; ?>">
                           <?php echo $s['is_active'] ? 'Active' : 'Inactive'; ?>
                         </span>
                       </td>
                       <td>
                         <button class="btn btn-sm btn-outline-primary btn-edit-structure" data-id="<?php echo $s['id']; ?>" data-name="<?php echo htmlspecialchars($s['name']); ?>" data-class-id="<?php echo htmlspecialchars($s['class_id'] ?? ''); ?>" data-amount="<?php echo htmlspecialchars($s['amount']); ?>" data-late-fee="<?php echo htmlspecialchars($s['late_fee']); ?>" title="Edit"><i class="fas fa-edit"></i></button>
                         <button class="btn btn-sm btn-outline-danger btn-delete-structure" data-id="<?php echo $s['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                       </td>
                     </tr>
                   <?php endforeach; ?>
                 <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Structure Modal -->
  <div class="modal fade" id="addStructureModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Add Fee Structure</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addStructureForm">
            <div id="structAlert" class="alert d-none"></div>
            
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Structure Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Monthly Class 9 Fee" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Class Allocation</label>
                <select name="class_id" class="form-select">
                  <option value="">-- All Classes --</option>
                  <?php foreach($classes as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Frequency Type *</label>
                <select name="type" class="form-select" required>
                  <option value="monthly">Monthly</option>
                  <option value="term">Term Fee</option>
                  <option value="annual">Annual Fee</option>
                  <option value="one_time">One Time</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Fee Amount (PKR) *</label>
                <input type="number" name="amount" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Late Fee Fine (PKR)</label>
                <input type="number" name="late_fee" class="form-control" value="0">
              </div>
              <div class="col-md-6">
                <label class="form-label">Due Day of Month *</label>
                <input type="number" name="due_day" class="form-control" min="1" max="28" value="10" required>
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveStructureBtn">Save Structure</button>
            </div>
          </form>
        </div>
</div>
     </div>
   </div>
   </div>
   </div>

   <!-- Edit Structure Modal -->
   <div class="modal fade" id="editStructureModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
         <div class="modal-header bg-primary text-white border-0">
           <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Fee Structure</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body p-4">
           <form id="editStructureForm">
             <div id="editStructAlert" class="alert d-none"></div>
             <input type="hidden" name="id" id="editStructureId">
             
             <div class="row g-3">
               <div class="col-12">
                 <label class="form-label">Structure Name *</label>
                 <input type="text" name="name" id="editStructureName" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Class Allocation</label>
                 <select name="class_id" id="editStructureClassId" class="form-select">
                   <option value="">-- All Classes --</option>
                   <?php foreach($classes as $c): ?>
                     <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                   <?php endforeach; ?>
                 </select>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Fee Amount (PKR) *</label>
                 <input type="number" name="amount" id="editStructureAmount" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Late Fee Fine (PKR)</label>
                 <input type="number" name="late_fee" id="editStructureLateFee" class="form-control" value="0">
               </div>
             </div>
             
             <div class="mt-4 text-end">
               <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="updateStructureBtn">Update Structure</button>
             </div>
           </form>
         </div>
       </div>
     </div>
   </div>

   <!-- Collect Fee Modal -->
  <div class="modal fade" id="collectFeeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-hand-holding-usd me-2"></i> Record Fee Payment</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="collectFeeForm">
            <div id="collectAlert" class="alert d-none"></div>
            
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Select Student *</label>
                <select name="student_id" class="form-select" required>
                  <option value="">-- Select Student --</option>
                  <?php foreach($students as $st): ?>
                    <option value="<?php echo $st['id']; ?>">
                      <?php echo htmlspecialchars($st['first_name'] . ' ' . $st['last_name'] . ' (' . $st['registration_number'] . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Select Fee Rule Template *</label>
                <select name="fee_structure_id" class="form-select" required>
                  <option value="">-- Select Fee Structure --</option>
                  <?php foreach($structures as $sr): ?>
                    <option value="<?php echo $sr['id']; ?>">
                      <?php echo htmlspecialchars($sr['name'] . ' (PKR ' . $sr['amount'] . ')'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Challan Number *</label>
                <input type="text" name="challan_no" class="form-control" placeholder="e.g. CH-2026-005" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Billing Month/Year *</label>
                <input type="text" name="month_year" class="form-control" placeholder="e.g. 2026-06" value="<?php echo date('Y-m'); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Amount Paid *</label>
                <input type="number" name="total_paid" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Payment Method *</label>
                <select name="payment_method" class="form-select" required>
                  <option value="cash">Cash</option>
                  <option value="bank">Bank Transfer</option>
                  <option value="online">Online Payment</option>
                  <option value="cheque">Cheque</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Remarks / Description</label>
                <textarea name="remarks" class="form-control" rows="2"></textarea>
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveCollectBtn">Record Payment</button>
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
      let activeTab = document.querySelector('#feeTabs button.active').id;
      let targetTable = activeTab === 'payments-tab' ? '#paymentsTable' : '#structuresTable';
      
      let rows = document.querySelectorAll(targetTable + ' tbody tr');
      rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // Add Fee Structure AJAX
    document.getElementById('addStructureForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = document.getElementById('saveStructureBtn');
      const alertDiv = document.getElementById('structAlert');
      const originalText = btn.innerHTML;
      
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      btn.disabled = true;
      alertDiv.className = 'alert d-none';
      
      const formData = new FormData(this);
      fetch('add_fee_structure_action.php', {
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

    // Collect Fee Payment AJAX
    document.getElementById('collectFeeForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = document.getElementById('saveCollectBtn');
      const alertDiv = document.getElementById('collectAlert');
      const originalText = btn.innerHTML;
      
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      btn.disabled = true;
      alertDiv.className = 'alert d-none';
      
      const formData = new FormData(this);
      fetch('collect_fee_action.php', {
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

     // Edit Structure Modal
     document.querySelectorAll('.btn-edit-structure').forEach(button => {
       button.addEventListener('click', function() {
         document.getElementById('editStructureId').value = this.dataset.id;
         document.getElementById('editStructureName').value = this.dataset.name;
         document.getElementById('editStructureClassId').value = this.dataset.classId || '';
         document.getElementById('editStructureAmount').value = this.dataset.amount;
         document.getElementById('editStructureLateFee').value = this.dataset.lateFee;
         new bootstrap.Modal(document.getElementById('editStructureModal')).show();
       });
     });

     // Update Structure AJAX
     document.getElementById('editStructureForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('updateStructureBtn');
       const alertDiv = document.getElementById('editStructAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('update_fee_structure_action.php', {
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

     // Delete Structure AJAX
     document.querySelectorAll('.btn-delete-structure').forEach(button => {
       button.addEventListener('click', function() {
         if (confirm('Are you sure you want to delete this fee structure?')) {
           const structureId = this.dataset.id;
           fetch('delete_fee_structure_action.php', {
             method: 'POST',
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
             body: 'id=' + encodeURIComponent(structureId)
           })
           .then(res => res.json())
           .then(data => { if(data.status === 'success') { window.location.reload(); } else { alert(data.message); } })
           .catch(() => { alert('A network error occurred.'); });
         }
       });
     // Delete Payment AJAX
     document.querySelectorAll('.btn-delete-payment').forEach(button => {
       button.addEventListener('click', function() {
         if (confirm('Are you sure you want to delete this payment record?')) {
           const paymentId = this.dataset.id;
           fetch('delete_payment_action.php', {
             method: 'POST',
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
             body: 'id=' + encodeURIComponent(paymentId)
           })
           .then(res => res.json())
           .then(data => { if(data.status === 'success') { window.location.reload(); } else { alert(data.message); } })
           .catch(() => { alert('A network error occurred.'); });
         }
       });
     });
   </script>
  </body>
</html>
