<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch all events
try {
    $stmt = $pdo->query("SELECT e.*, u.username 
                         FROM events e 
                         LEFT JOIN users u ON e.created_by = u.id 
                         ORDER BY e.event_date ASC, e.id DESC");
    $events = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching events: " . $e->getMessage();
    $events = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "School Events";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search events...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Events Calendar</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Events</li>
        </ol>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
        <i class="fas fa-plus me-2"></i> Add Event
      </button>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="dash-card">
      <div class="dash-card-header">
        <h5><i class="fas fa-calendar-alt text-primary"></i> Scheduled School Events</h5>
      </div>
      <div class="dash-card-body p-4">
        <div id="eventContainer">
          <?php if(empty($events)): ?>
            <div class="text-center py-4 text-muted">No school events scheduled.</div>
          <?php else: ?>
            <?php foreach($events as $event): ?>
              <?php 
                $dt = new DateTime($event['event_date']);
                $day = $dt->format('d');
                $month = $dt->format('M');
                
                $type = $event['type'];
                $badgeClass = 'leave'; // primary
                if ($type === 'sports') $badgeClass = 'late'; // warning
                elseif ($type === 'holiday') $badgeClass = 'overdue'; // danger
                elseif ($type === 'exam') $badgeClass = 'partial'; // dark orange
              ?>
<div class="event-item pb-3 mb-3 border-bottom d-flex justify-content-between align-items-start" data-searchable>
                 <div class="d-flex gap-3">
                   <div class="event-date-box">
                     <div class="day"><?php echo $day; ?></div>
                     <div class="month"><?php echo $month; ?></div>
                   </div>
                   <div>
                     <h5 class="mb-1 text-dark fw-bold"><?php echo htmlspecialchars($event['title']); ?></h5>
                     <p class="text-muted mb-2" style="font-size: 0.88rem; max-width: 700px;"><?php echo htmlspecialchars($event['description']); ?></p>
                     <div class="d-flex flex-wrap gap-2 align-items-center text-muted small" style="font-size: 0.75rem;">
                       <span class="status-badge <?php echo $badgeClass; ?>"><?php echo ucfirst(htmlspecialchars($type)); ?></span>
                       <span><i class="far fa-clock me-1"></i><?php echo date('h:i A', strtotime($event['start_time'] ?? '00:00:00')); ?> - <?php echo date('h:i A', strtotime($event['end_time'] ?? '00:00:00')); ?></span>
                       <span><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($event['venue'] ?? 'School Premises'); ?></span>
                       <span>• Created by @<?php echo htmlspecialchars($event['username'] ?? 'admin'); ?></span>
                     </div>
                   </div>
                 </div>
                 <div>
                   <button class="btn btn-sm btn-outline-primary btn-edit-event" data-id="<?php echo $event['id']; ?>" data-title="<?php echo htmlspecialchars($event['title']); ?>" data-description="<?php echo htmlspecialchars($event['description'] ?? ''); ?>" data-event-date="<?php echo htmlspecialchars($event['event_date']); ?>" data-type="<?php echo htmlspecialchars($event['type']); ?>" data-start-time="<?php echo htmlspecialchars($event['start_time'] ?? ''); ?>" data-end-time="<?php echo htmlspecialchars($event['end_time'] ?? ''); ?>" data-venue="<?php echo htmlspecialchars($event['venue'] ?? ''); ?>" title="Edit"><i class="fas fa-edit"></i></button>
                   <button class="btn btn-sm btn-outline-danger btn-delete-event" data-id="<?php echo $event['id']; ?>" title="Delete Event"><i class="fas fa-trash"></i></button>
                 </div>
               </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Event Modal -->
  <div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i> Schedule New Event</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addEventForm">
            <div id="eventFormAlert" class="alert d-none"></div>
            
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Event Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Science Fair Exhibition" required>
              </div>
              <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Provide event details..."></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Event Date *</label>
                <input type="date" name="event_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Event Type *</label>
                <select name="type" class="form-select" required>
                  <option value="academic">Academic</option>
                  <option value="sports">Sports Gala</option>
                  <option value="cultural">Cultural / Arts</option>
                  <option value="holiday">Holiday</option>
                  <option value="exam">Examination</option>
                  <option value="other">Other Event</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Start Time *</label>
                <input type="time" name="start_time" class="form-control" value="08:00" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">End Time *</label>
                <input type="time" name="end_time" class="form-control" value="14:00" required>
              </div>
              <div class="col-12">
                <label class="form-label">Venue *</label>
                <input type="text" name="venue" class="form-control" placeholder="e.g. Auditorium / Main Ground" required>
              </div>
            </div>
            
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveEventBtn">Schedule Event</button>
            </div>
          </form>
</div>
     </div>
   </div>
   </div>
   </div>

   <!-- Edit Event Modal -->
   <div class="modal fade" id="editEventModal" tabindex="-1">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
         <div class="modal-header bg-primary text-white border-0">
           <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Event</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body p-4">
           <form id="editEventForm">
             <div id="editEventAlert" class="alert d-none"></div>
             <input type="hidden" name="id" id="editEventId">
             <div class="row g-3">
               <div class="col-12">
                 <label class="form-label">Event Title *</label>
                 <input type="text" name="title" id="editEventTitle" class="form-control" required>
               </div>
               <div class="col-12">
                 <label class="form-label">Description</label>
                 <textarea name="description" id="editEventDescription" class="form-control" rows="3"></textarea>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Event Date *</label>
                 <input type="date" name="event_date" id="editEventDate" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Event Type *</label>
                 <select name="type" id="editEventType" class="form-select" required>
                   <option value="academic">Academic</option>
                   <option value="sports">Sports Gala</option>
                   <option value="cultural">Cultural / Arts</option>
                   <option value="holiday">Holiday</option>
                   <option value="exam">Examination</option>
                   <option value="other">Other Event</option>
                 </select>
               </div>
               <div class="col-md-6">
                 <label class="form-label">Start Time *</label>
                 <input type="time" name="start_time" id="editEventStartTime" class="form-control" required>
               </div>
               <div class="col-md-6">
                 <label class="form-label">End Time *</label>
                 <input type="time" name="end_time" id="editEventEndTime" class="form-control" required>
               </div>
               <div class="col-12">
                 <label class="form-label">Venue *</label>
                 <input type="text" name="venue" id="editEventVenue" class="form-control" required>
               </div>
             </div>
             <div class="mt-4 text-end">
               <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="updateEventBtn">Update Event</button>
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
      let elements = document.querySelectorAll('#eventContainer [data-searchable]');
      elements.forEach(el => {
        let text = el.textContent.toLowerCase();
        el.style.setProperty('display', text.includes(filter) ? 'flex' : 'none', 'important');
      });
    });

    // Add Event AJAX
    document.getElementById('addEventForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = document.getElementById('saveEventBtn');
      const alertDiv = document.getElementById('eventFormAlert');
      const originalText = btn.innerHTML;
      
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      btn.disabled = true;
      alertDiv.className = 'alert d-none';
      
      const formData = new FormData(this);
      fetch('add_event_action.php', {
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

    // Delete Event AJAX
    document.querySelectorAll('.btn-delete-event').forEach(button => {
      button.addEventListener('click', function() {
        if (confirm('Delete this scheduled event?')) {
          const id = this.dataset.id;
          
          fetch('delete_event_action.php', {
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

     // Edit Event Modal
     document.querySelectorAll('.btn-edit-event').forEach(button => {
       button.addEventListener('click', function() {
         document.getElementById('editEventId').value = this.dataset.id;
         document.getElementById('editEventTitle').value = this.dataset.title;
         document.getElementById('editEventDescription').value = this.dataset.description;
         document.getElementById('editEventDate').value = this.dataset.eventDate;
         document.getElementById('editEventType').value = this.dataset.type;
         document.getElementById('editEventStartTime').value = this.dataset.startTime || '';
         document.getElementById('editEventEndTime').value = this.dataset.endTime || '';
         document.getElementById('editEventVenue').value = this.dataset.venue;
         new bootstrap.Modal(document.getElementById('editEventModal')).show();
       });
     });

     // Update Event AJAX
     document.getElementById('editEventForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('updateEventBtn');
       const alertDiv = document.getElementById('editEventAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('update_event_action.php', { method: 'POST', body: new FormData(this) })
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
