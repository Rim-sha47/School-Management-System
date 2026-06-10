<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

try {
    // 1. Students Count by Class
    $classStudentsStmt = $pdo->query("
        SELECT c.name AS class_name, COUNT(s.id) AS student_count 
        FROM classes c 
        LEFT JOIN students s ON c.id = s.class_id AND s.status = 'active'
        GROUP BY c.id 
        ORDER BY c.numeric_name ASC
    ");
    $classStudentsData = $classStudentsStmt->fetchAll();
    
    $classNames = [];
    $studentCounts = [];
    foreach($classStudentsData as $row) {
        $classNames[] = $row['class_name'];
        $studentCounts[] = intval($row['student_count']);
    }
    
    // 2. Attendance Rates by Class
    $attendanceRateStmt = $pdo->query("
        SELECT c.name AS class_name, 
               COUNT(a.id) AS total_marked,
               SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_count
        FROM classes c 
        JOIN attendance a ON c.id = a.class_id
        GROUP BY c.id 
        ORDER BY c.numeric_name ASC
    ");
    $attendanceRatesData = $attendanceRateStmt->fetchAll();
    
    $attClassNames = [];
    $attPercentages = [];
    foreach($attendanceRatesData as $row) {
        $attClassNames[] = $row['class_name'];
        $pct = $row['total_marked'] > 0 ? round(($row['present_count'] / $row['total_marked']) * 100) : 0;
        $attPercentages[] = $pct;
    }
    
    // 3. Fee Collection Breakdown
    $feeBreakdownStmt = $pdo->query("
        SELECT status, SUM(total_paid) as collected, COUNT(*) as count 
        FROM fee_payments 
        GROUP BY status
    ");
    $feeBreakdownData = $feeBreakdownStmt->fetchAll();
    
    $feeStatuses = [];
    $feeTotals = [];
    foreach($feeBreakdownData as $row) {
        $feeStatuses[] = ucfirst($row['status']);
        $feeTotals[] = floatval($row['collected'] ?? 0);
    }
    
    // Stats for dashboard widgets
    $totalStudents = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'")->fetchColumn();
    $totalTeachers = $pdo->query("SELECT COUNT(*) FROM teachers WHERE status = 'active'")->fetchColumn();
    $todayAttendance = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE()")->fetchColumn();
    $todayPresent = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE() AND status = 'present'")->fetchColumn();
    $attendancePercent = $todayAttendance > 0 ? round(($todayPresent / $todayAttendance) * 100) : 0;
    $totalFeesCollected = $pdo->query("SELECT SUM(total_paid) FROM fee_payments WHERE status = 'paid'")->fetchColumn();
    $totalFeesDue = $pdo->query("SELECT SUM(amount) FROM fee_payments WHERE status = 'unpaid'")->fetchColumn();
    $feeCollectionPercent = ($totalFeesCollected + $totalFeesDue) > 0 ? round(($totalFeesCollected / ($totalFeesCollected + $totalFeesDue)) * 100) : 0;
} catch(PDOException $e) {
    $error = "Error generating reports: " . $e->getMessage();
    $totalStudents = 0;
    $totalTeachers = 0;
    $attendancePercent = 0;
    $feeCollectionPercent = 0;
}

// Fetch recent students
try {
    $recentStudentsStmt = $pdo->query("SELECT s.id, s.student_id, s.first_name, s.last_name, s.admission_date, s.status, c.name AS class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id ORDER BY s.id DESC LIMIT 5");
    $recentStudents = $recentStudentsStmt->fetchAll();
} catch(PDOException $e) {
    $recentStudents = [];
}

// Fetch recent notices
try {
    $recentNoticesStmt = $pdo->query("SELECT id, title, content, audience, priority, publish_date FROM notices ORDER BY publish_date DESC, id DESC LIMIT 3");
    $recentNotices = $recentNoticesStmt->fetchAll();
} catch(PDOException $e) {
    $recentNotices = [];
}

// Fetch upcoming events for calendar
try {
    $upcomingEventsStmt = $pdo->query("SELECT id, title, event_date, type FROM events ORDER BY event_date, id DESC LIMIT 10");
    $upcomingEvents = $upcomingEventsStmt->fetchAll();
} catch(PDOException $e) {
    $upcomingEvents = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "Admin Dashboard";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <!-- Sidebar Overlay for Mobile -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search student, teacher or class...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    
    <div class="page-header">
      <h1>Admin Dashboard</h1>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>
    </div>

    <!-- Stats Widgets -->
    <div class="row g-4 mb-4">
      <div class="col-xl-3 col-md-6">
        <div class="widget-card blue">
          <div class="widget-icon"><i class="fas fa-user-graduate"></i></div>
          <div class="widget-info">
            <h2 data-count="<?php echo $totalStudents ?? 0; ?>"><?php echo $totalStudents ?? 0; ?></h2>
            <p>Total Students</p>
          </div>
          <div class="widget-trend up"><i class="fas fa-arrow-up"></i> 5%</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="widget-card cyan">
          <div class="widget-icon"><i class="fas fa-chalkboard-teacher"></i></div>
          <div class="widget-info">
            <h2 data-count="156"><?php echo $totalTeachers ?? 0; ?></h2>
            <p>Total Teachers</p>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="widget-card orange">
          <div class="widget-icon"><i class="fas fa-calendar-check"></i></div>
          <div class="widget-info">
            <h2 data-count="94" data-suffix="%"><?php echo $attendancePercent; ?>%</h2>
            <p>Today's Attendance</p>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="widget-card green">
          <div class="widget-icon"><i class="fas fa-rupee-sign"></i></div>
          <div class="widget-info">
            <h2 data-count="85" data-suffix="%"><?php echo $feeCollectionPercent; ?>%</h2>
            <p>Fee Collection</p>
          </div>
          <div class="widget-trend down"><i class="fas fa-arrow-down"></i> 2%</div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <!-- Attendance Chart -->
      <div class="col-lg-8">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-chart-area"></i> Attendance Overview (Monthly)</h5>
            <div class="dropdown">
              <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#">Download Report</a></li>
              </ul>
            </div>
          </div>
          <div class="dash-card-body">
            <div class="chart-container" style="height: 300px;">
              <canvas id="attendanceChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Fee Status Chart -->
      <div class="col-lg-4">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-chart-pie"></i> Fee Status (Current Term)</h5>
          </div>
          <div class="dash-card-body">
            <div class="chart-container" style="height: 250px;">
              <canvas id="feeChart"></canvas>
            </div>
            <div class="mt-4 text-center">
              <span class="status-badge paid me-2">Paid: 68%</span>
              <span class="status-badge partial me-2">Partial: 22%</span>
              <span class="status-badge overdue">Overdue: 10%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <!-- Exam Performance -->
      <div class="col-lg-8">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-chart-bar"></i> Exam Performance by Class</h5>
          </div>
          <div class="dash-card-body">
            <div class="chart-container" style="height: 300px;">
              <canvas id="examChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Quick Actions & Gender -->
      <div class="col-lg-4">
        <div class="dash-card mb-4">
          <div class="dash-card-header">
            <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
          </div>
          <div class="dash-card-body">
            <div class="row g-3">
              <div class="col-6">
                <button class="quick-action w-100" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                  <i class="fas fa-user-plus"></i>
                  Add Student
                </button>
              </div>
              <div class="col-6">
                <button class="quick-action w-100" data-bs-toggle="modal" data-bs-target="#collectFeeModal">
                  <i class="fas fa-file-invoice-dollar"></i>
                  Collect Fee
                </button>
              </div>
              <div class="col-6">
                <button class="quick-action w-100" data-bs-toggle="modal" data-bs-target="#addNoticeModal">
                  <i class="fas fa-bullhorn"></i>
                  Add Notice
                </button>
              </div>
              <div class="col-6">
                <button class="quick-action w-100" data-bs-toggle="modal" data-bs-target="#addEventModal">
                  <i class="fas fa-calendar-plus"></i>
                  Add Event
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="dash-card">
          <div class="dash-card-header">
            <h5><i class="fas fa-venus-mars"></i> Gender Distribution</h5>
          </div>
          <div class="dash-card-body">
            <div class="chart-container" style="height: 180px;">
              <canvas id="genderChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Recent Students -->
      <div class="col-lg-8">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-user-graduate"></i> Recently Admitted Students</h5>
            <a href="#" class="view-all-link">View All <i class="fas fa-angle-right"></i></a>
          </div>
          <div class="dash-card-body p-0" style="overflow-x: auto;">
            <table class="dash-table">
<thead>
                 <tr>
                   <th>Student Name</th>
                   <th>ID Number</th>
                   <th>Class</th>
                   <th>Date</th>
                   <th>Status</th>
                   <th>Actions</th>
                 </tr>
               </thead>
               <tbody>
                 <?php foreach($recentStudents as $student): ?>
                 <tr>
                   <td>
                     <div class="avatar-sm">
                       <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                     </div>
                     <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                   </td>
                   <td><?php echo htmlspecialchars($student['student_id'] ?? 'STD-' . $student['id']); ?></td>
                   <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                   <td><?php echo $student['admission_date'] ? date('d M, Y', strtotime($student['admission_date'])) : '-'; ?></td>
                   <td><span class="status-badge <?php echo $student['status']; ?>"><?php echo ucfirst($student['status']); ?></span></td>
                   <td>
                     <button class="btn btn-sm btn-outline-primary edit-student-btn" data-id="<?php echo $student['id']; ?>" data-bs-toggle="modal" data-bs-target="#editStudentModal" title="Edit"><i class="fas fa-edit"></i></button>
                     <button class="btn btn-sm btn-outline-danger delete-student-btn" data-id="<?php echo $student['id']; ?>" title="Remove"><i class="fas fa-trash"></i></button>
                   </td>
                 </tr>
                 <?php endforeach; ?>
                 <?php if(empty($recentStudents)): ?>
                 <tr><td colspan="6" class="text-center">No students found</td></tr>
                 <?php endif; ?>
</tbody>
             </table>
           </div>
         </div>
       </div>

      <!-- Calendar & Notices -->
      <div class="col-lg-4">
        <!-- Calendar -->
        <div class="dash-card mb-4">
          <div class="dash-card-body p-3">
            <div class="mini-calendar">
              <div class="calendar-nav">
                <button id="calPrev"><i class="fas fa-chevron-left"></i></button>
                <h6 id="calMonth">June 2026</h6>
                <button id="calNext"><i class="fas fa-chevron-right"></i></button>
              </div>
              <div class="calendar-grid" id="calGrid"></div>
            </div>
          </div>
        </div>

        <!-- Notices -->
        <div class="dash-card">
          <div class="dash-card-header">
            <h5><i class="fas fa-bell"></i> Recent Notices</h5>
            <a href="#" class="view-all-link">All <i class="fas fa-angle-right"></i></a>
          </div>
          <div class="dash-card-body p-3">
            <?php foreach($recentNotices as $notice): 
              $priority = $notice['priority'] ?? 'normal';
              $dotClass = $priority === 'urgent' ? 'urgent' : ($priority === 'important' ? 'info' : '');
            ?>
            <div class="notice-item">
              <div class="notice-dot <?php echo $dotClass; ?>"></div>
              <div>
                <h6><?php echo htmlspecialchars($notice['title']); ?>
                  <div class="float-end">
                    <button class="btn btn-sm btn-outline-primary edit-notice-btn" data-id="<?php echo $notice['id']; ?>" data-title="<?php echo htmlspecialchars($notice['title']); ?>" data-content="<?php echo htmlspecialchars($notice['content']); ?>" data-audience="<?php echo htmlspecialchars($notice['audience']); ?>" data-priority="<?php echo htmlspecialchars($notice['priority']); ?>" data-bs-toggle="modal" data-bs-target="#editNoticeModal" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger delete-notice-btn" data-id="<?php echo $notice['id']; ?>" title="Remove"><i class="fas fa-trash"></i></button>
                  </div>
                </h6>
                <p><?php echo htmlspecialchars($notice['content']); ?></p>
                <div class="notice-date">
                  <?php echo $notice['publish_date'] ? date('d M, Y', strtotime($notice['publish_date'])) : 'Recently'; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($recentNotices)): ?>
            <div class="notice-item">
              <p class="text-center mb-0">No notices found</p>
            </div>
            <?php endif; ?>
          </div>
        </div>
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
               <button type="submit" class="btn btn-primary" id="saveStudentBtn">Save Student</button>
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
               <button type="submit" class="btn btn-primary" id="updateStudentBtn">Update Student</button>
             </div>
           </form>
         </div>
       </div>
     </div>
   </div>

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
             </div>
<div class="mt-4 text-end">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveEventBtn">Add Event</button>
              </div>
            </form>
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
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <script src="../assets/js/admin.js"></script>
   <script>
     // Add Student AJAX
     document.getElementById('addStudentForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('saveStudentBtn');
       const alertDiv = document.getElementById('formAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('add_student_action.php', {
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

     // Edit Student - populate modal and show
     document.querySelectorAll('.edit-student-btn').forEach(button => {
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

     // Add Notice AJAX
     document.getElementById('addNoticeForm').addEventListener('submit', function(e) {
       e.preventDefault();
       const btn = document.getElementById('saveNoticeBtn');
       const alertDiv = document.getElementById('noticeFormAlert');
       const originalText = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
       btn.disabled = true;
       alertDiv.className = 'alert d-none';
       fetch('add_notice_action.php', {
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

     // Edit Notice - populate modal
     document.querySelectorAll('.edit-notice-btn').forEach(button => {
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
       fetch('update_notice_action.php', {
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

     // Delete Student AJAX (from dashboard)
     document.querySelectorAll('.delete-student-btn').forEach(button => {
       button.addEventListener('click', function() {
         if (confirm('Are you sure you want to delete this student?')) {
           fetch('delete_student_action.php', {
             method: 'POST',
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
             body: 'id=' + encodeURIComponent(this.dataset.id)
           })
           .then(res => res.json())
           .then(data => {
             if(data.status === 'success') window.location.reload();
             else alert(data.message);
           })
           .catch(err => alert('Network error occurred.'));
         }
       });
     });

     // Delete Notice AJAX (from dashboard)
     document.querySelectorAll('.delete-notice-btn').forEach(button => {
       button.addEventListener('click', function() {
         if (confirm('Are you sure you want to delete this notice?')) {
           fetch('delete_notice_action.php', {
             method: 'POST',
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
             body: 'id=' + encodeURIComponent(this.dataset.id)
           })
           .then(res => res.json())
           .then(data => {
             if(data.status === 'success') window.location.reload();
             else alert(data.message);
           })
           .catch(err => alert('Network error occurred.'));
         }
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
        fetch('add_event_action.php', {
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
