<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch lists
try {
    // Routes list
    $routesStmt = $pdo->query("SELECT r.*, v.registration_no, v.make_model 
                               FROM routes r 
                               LEFT JOIN vehicles v ON r.vehicle_id = v.id 
                               ORDER BY r.id DESC");
    $routes = $routesStmt->fetchAll();

    // Vehicles list
    $vehiclesStmt = $pdo->query("SELECT * FROM vehicles ORDER BY id DESC");
    $vehicles = $vehiclesStmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching transport data: " . $e->getMessage();
    $routes = [];
    $vehicles = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Transport Fleet";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search routes or buses...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Transport Management</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Transport</li>
        </ol>
      </div>
      <div>
        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
          <i class="fas fa-bus me-1"></i> Add Vehicle
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRouteModal">
          <i class="fas fa-route me-1"></i> Add Route
        </button>
      </div>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Tabs Container -->
    <ul class="nav nav-tabs mb-4 border-bottom-0" id="transportTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active border-0 bg-transparent fw-semibold" id="routes-tab" data-bs-toggle="tab" data-bs-target="#routesTab" type="button">Transit Routes</button>
      </li>
      <li class="nav-item">
        <button class="nav-link border-0 bg-transparent fw-semibold" id="vehicles-tab" data-bs-toggle="tab" data-bs-target="#vehiclesTab" type="button">Fleet Vehicles</button>
      </li>
    </ul>

    <div class="tab-content" id="transportTabContent">
      <!-- Routes Tab -->
      <div class="tab-pane fade show active" id="routesTab">
        <div class="dash-card">
          <div class="dash-card-header">
            <h5><i class="fas fa-route text-primary"></i> Route Maps & Fees</h5>
          </div>
          <div class="dash-card-body p-0" style="overflow-x: auto;">
            <table class="dash-table" id="routesTable">
<thead>
               <tr>
                 <th>ID</th>
                 <th>Route Name</th>
                 <th>Assigned Vehicle</th>
                 <th>Stops / Waypoints</th>
                 <th>Fare (PKR)</th>
                 <th>Status</th>
                 <th>Actions</th>
               </tr>
             </thead>
              <tbody>
                <?php if(empty($routes)): ?>
                  <tr><td colspan="6" class="text-center py-4">No transport routes created.</td></tr>
                <?php else: ?>
                  <?php foreach($routes as $r): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($r['id']); ?></td>
                      <td><strong><?php echo htmlspecialchars($r['name']); ?></strong></td>
                      <td>
                        <?php if(!empty($r['registration_no'])): ?>
                          <span class="badge bg-light text-dark border font-monospace"><?php echo htmlspecialchars($r['registration_no']); ?></span>
                          <div class="small text-muted"><?php echo htmlspecialchars($r['make_model']); ?></div>
                        <?php else: ?>
                          <span class="text-muted small">None Assigned</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="small" style="max-width:300px; white-space:normal;">
                          <?php echo htmlspecialchars($r['stops']); ?>
                        </div>
                      </td>
                      <td><strong>PKR <?php echo number_format($r['fare']); ?></strong></td>
<td>
                         <span class="status-badge <?php echo $r['status'] === 'active' ? 'active' : 'inactive'; ?>">
                           <?php echo ucfirst(htmlspecialchars($r['status'])); ?>
                         </span>
                       </td>
                       <td>
                         <button class="btn btn-sm btn-outline-primary btn-edit-route" data-id="<?php echo $r['id']; ?>" data-name="<?php echo htmlspecialchars($r['name']); ?>" data-vehicle-id="<?php echo htmlspecialchars($r['vehicle_id'] ?? ''); ?>" data-fare="<?php echo htmlspecialchars($r['fare']); ?>" data-stops="<?php echo htmlspecialchars($r['stops'] ?? ''); ?>" title="Edit"><i class="fas fa-edit"></i></button>
                         <button class="btn btn-sm btn-outline-danger btn-delete-route" data-id="<?php echo $r['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                       </td>
                     </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Vehicles Tab -->
      <div class="tab-pane fade" id="vehiclesTab">
        <div class="dash-card">
          <div class="dash-card-header">
            <h5><i class="fas fa-bus text-primary"></i> Fleet Inventory</h5>
          </div>
          <div class="dash-card-body p-0" style="overflow-x: auto;">
            <table class="dash-table" id="vehiclesTable">
<thead>
               <tr>
                 <th>ID</th>
                 <th>Registration No</th>
                 <th>Make / Model</th>
                 <th>Passenger Capacity</th>
                 <th>Driver Details</th>
                 <th>Status</th>
                 <th>Actions</th>
               </tr>
             </thead>
              <tbody>
                <?php if(empty($vehicles)): ?>
                  <tr><td colspan="6" class="text-center py-4">No vehicles in fleet inventory.</td></tr>
                <?php else: ?>
                  <?php foreach($vehicles as $v): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($v['id']); ?></td>
                      <td><span class="badge bg-light text-dark border font-monospace fw-semibold"><?php echo htmlspecialchars($v['registration_no']); ?></span></td>
                      <td><strong><?php echo htmlspecialchars($v['make_model']); ?></strong></td>
                      <td><?php echo htmlspecialchars($v['capacity']); ?> Seats</td>
                      <td>
                        <strong><?php echo htmlspecialchars($v['driver_name'] ?? 'N/A'); ?></strong>
                        <div class="small text-muted"><?php echo htmlspecialchars($v['driver_phone'] ?? 'N/A'); ?></div>
                      </td>
<td>
                         <span class="status-badge <?php echo $v['status'] === 'active' ? 'active' : ($v['status'] === 'maintenance' ? 'late' : 'inactive'); ?>">
                           <?php echo ucfirst(htmlspecialchars($v['status'])); ?>
                         </span>
                       </td>
                       <td>
                         <button class="btn btn-sm btn-outline-primary btn-edit-vehicle" data-id="<?php echo $v['id']; ?>" data-reg="<?php echo htmlspecialchars($v['registration_no']); ?>" data-model="<?php echo htmlspecialchars($v['make_model']); ?>" data-capacity="<?php echo htmlspecialchars($v['capacity']); ?>" data-status="<?php echo htmlspecialchars($v['status']); ?>" data-driver="<?php echo htmlspecialchars($v['driver_name'] ?? ''); ?>" data-phone="<?php echo htmlspecialchars($v['driver_phone'] ?? ''); ?>" title="Edit"><i class="fas fa-edit"></i></button>
                         <button class="btn btn-sm btn-outline-danger btn-delete-vehicle" data-id="<?php echo $v['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
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

  <!-- Add Vehicle Modal -->
  <div class="modal fade" id="addVehicleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-bus me-2"></i> Add New Vehicle</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addVehicleForm">
            <div id="vehicleAlert" class="alert d-none"></div>
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Registration No *</label>
                <input type="text" name="registration_no" class="form-control" placeholder="e.g. LEA-1234" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Make & Model *</label>
                <input type="text" name="make_model" class="form-control" placeholder="e.g. Toyota Coaster" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Seating Capacity *</label>
                <input type="number" name="capacity" class="form-control" value="30" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Vehicle Status *</label>
                <select name="status" class="form-select" required>
                  <option value="active">Active</option>
                  <option value="maintenance">Under Maintenance</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Driver Name</label>
                <input type="text" name="driver_name" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">Driver Phone</label>
                <input type="text" name="driver_phone" class="form-control" placeholder="e.g. 0300-1111111">
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveVehicleBtn">Save Vehicle</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Route Modal -->
  <div class="modal fade" id="addRouteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-route me-2"></i> Add Transit Route</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addRouteForm">
            <div id="routeAlert" class="alert d-none"></div>
            
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Route Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Route C - Johar Town" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Assigned Vehicle</label>
                <select name="vehicle_id" class="form-select">
                  <option value="">-- No Vehicle Assigned --</option>
                  <?php foreach($vehicles as $vh): ?>
                    <option value="<?php echo $vh['id']; ?>">
                      <?php echo htmlspecialchars($vh['registration_no'] . ' [' . $vh['make_model'] . ']'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Fare (PKR Monthly) *</label>
                <input type="number" name="fare" class="form-control" placeholder="e.g. 1000" required>
              </div>
              <div class="col-12">
                <label class="form-label">Route Stops *</label>
                <textarea name="stops" class="form-control" rows="3" placeholder="List stops separated by commas, e.g. Stop 1, Stop 2, School" required></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control">
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveRouteBtn">Save Route</button>
            </div>
          </form>
</div>
     </div>
   </div>
   </div>
   </div>

   <!-- Edit Vehicle Modal -->
   <div class="modal fade" id="editVehicleModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
         <div class="modal-header bg-primary text-white border-0">
           <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Vehicle</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body p-4">
           <form id="editVehicleForm">
             <div id="editVehicleAlert" class="alert d-none"></div>
             <input type="hidden" name="id" id="editVehicleId">
             <div class="row g-3">
               <div class="col-md-6">
                 <label class="form-label">Registration No *</label>
                 <input type="text" name="registration_no" id="editVehicleReg" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Make & Model *</label>
                 <input type="text" name="make_model" id="editVehicleModel" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Seating Capacity *</label>
                 <input type="number" name="capacity" id="editVehicleCapacity" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Status *</label>
                 <select name="status" id="editVehicleStatus" class="form-select" required>
                   <option value="active">Active</option>
                   <option value="maintenance">Under Maintenance</option>
                   <option value="inactive">Inactive</option>
                 </select>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Driver Name</label>
                 <input type="text" name="driver_name" id="editVehicleDriver" class="form-control">
               </div>
               <div class="col-md-6">
                 <label class="form-label">Driver Phone</label>
                 <input type="text" name="driver_phone" id="editVehiclePhone" class="form-control">
               </div>
             </div>
             <div class="mt-4 text-end">
               <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="updateVehicleBtn">Update Vehicle</button>
             </div>
           </form>
         </div>
       </div>
     </div>
   </div>

   <!-- Edit Route Modal -->
   <div class="modal fade" id="editRouteModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
         <div class="modal-header bg-primary text-white border-0">
           <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Route</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body p-4">
           <form id="editRouteForm">
             <div id="editRouteAlert" class="alert d-none"></div>
             <input type="hidden" name="id" id="editRouteId">
             <div class="row g-3">
               <div class="col-12">
                 <label class="form-label">Route Name *</label>
                 <input type="text" name="name" id="editRouteName" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Assigned Vehicle</label>
                 <select name="vehicle_id" id="editRouteVehicleId" class="form-select">
                   <option value="">-- No Vehicle Assigned --</option>
                   <?php foreach($vehicles as $vh): ?>
                     <option value="<?php echo $vh['id']; ?>"><?php echo htmlspecialchars($vh['registration_no'] . ' [' . $vh['make_model'] . ']'); ?></option>
                   <?php endforeach; ?>
                 </select>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Fare (PKR Monthly) *</label>
                 <input type="number" name="fare" id="editRouteFare" class="form-control" required>
               </div>
               <div class="col-12">
                 <label class="form-label">Route Stops *</label>
                 <textarea name="stops" id="editRouteStops" class="form-control" rows="3" required></textarea>
               </div>
             </div>
             <div class="mt-4 text-end">
               <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="updateRouteBtn">Update Route</button>
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
       let activeTab = document.querySelector('#transportTabs button.active').id;
       let targetTable = activeTab === 'routes-tab' ? '#routesTable' : '#vehiclesTable';
       
       let rows = document.querySelectorAll(targetTable + ' tbody tr');
       rows.forEach(row => {
         let text = row.textContent.toLowerCase();
         row.style.display = text.includes(filter) ? '' : 'none';
       });
     });

     // Add Vehicle AJAX
     document.getElementById('addVehicleForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('saveVehicleBtn');
       const alertDiv = document.getElementById('vehicleAlert');
       const originalText = btn.innerHTML;
       
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       
       const formData = new FormData(this);
       fetch('add_vehicle_action.php', {
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

     // Add Route AJAX
     document.getElementById('addRouteForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('saveRouteBtn');
       const alertDiv = document.getElementById('routeAlert');
       const originalText = btn.innerHTML;
       
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       
       const formData = new FormData(this);
       fetch('add_route_action.php', {
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

     // Edit Route Modal
     document.querySelectorAll('.btn-edit-route').forEach(button => {
       button.addEventListener('click', function() {
         document.getElementById('editRouteId').value = this.dataset.id;
         document.getElementById('editRouteName').value = this.dataset.name;
         document.getElementById('editRouteVehicleId').value = this.dataset.vehicleId || '';
         document.getElementById('editRouteFare').value = this.dataset.fare;
         document.getElementById('editRouteStops').value = this.dataset.stops;
         new bootstrap.Modal(document.getElementById('editRouteModal')).show();
       });
     });

     // Update Route AJAX
     document.getElementById('editRouteForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('updateRouteBtn');
       const alertDiv = document.getElementById('editRouteAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('update_route_action.php', { method: 'POST', body: new FormData(this) })
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

     // Delete Route AJAX
     document.querySelectorAll('.btn-delete-route').forEach(button => {
       button.addEventListener('click', function() {
         if (confirm('Are you sure you want to delete this route?')) {
           fetch('delete_route_action.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + encodeURIComponent(this.dataset.id) })
           .then(res => res.json())
           .then(data => { if(data.status === 'success') { window.location.reload(); } else { alert(data.message); } });
         }
       });
     });

     // Edit Vehicle Modal
     document.querySelectorAll('.btn-edit-vehicle').forEach(button => {
       button.addEventListener('click', function() {
         document.getElementById('editVehicleId').value = this.dataset.id;
         document.getElementById('editVehicleReg').value = this.dataset.reg;
         document.getElementById('editVehicleModel').value = this.dataset.model;
         document.getElementById('editVehicleCapacity').value = this.dataset.capacity;
         document.getElementById('editVehicleStatus').value = this.dataset.status;
         document.getElementById('editVehicleDriver').value = this.dataset.driver;
         document.getElementById('editVehiclePhone').value = this.dataset.phone;
         new bootstrap.Modal(document.getElementById('editVehicleModal')).show();
       });
     });

     // Update Vehicle AJAX
     document.getElementById('editVehicleForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('updateVehicleBtn');
       const alertDiv = document.getElementById('editVehicleAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('update_vehicle_action.php', { method: 'POST', body: new FormData(this) })
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

     // Delete Vehicle AJAX
     document.querySelectorAll('.btn-delete-vehicle').forEach(button => {
       button.addEventListener('click', function() {
         if (confirm('Are you sure you want to delete this vehicle?')) {
           fetch('delete_vehicle_action.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + encodeURIComponent(this.dataset.id) })
           .then(res => res.json())
           .then(data => { if(data.status === 'success') { window.location.reload(); } else { alert(data.message); } });
         }
       });
     });
   </script>
</body>
</html>
