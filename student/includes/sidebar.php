<?php
$activePage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
  <a href="dashboard.php" class="sidebar-brand">
    <img src="../assets/images/logo.png" alt="Logo" class="sidebar-logo" onerror="this.src='https://via.placeholder.com/42?text=PS'">
    <div class="sidebar-title">
      <h6>The Providence School</h6>
      <span>Student Portal</span>
    </div>
  </a>
  
  <div class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="dashboard.php" class="sidebar-link <?= ($activePage == 'dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-home"></i> <span class="link-text">My Dashboard</span></a>
    <a href="profile.php" class="sidebar-link <?= ($activePage == 'profile.php') ? 'active' : ''; ?>"><i class="fas fa-user-circle"></i> <span class="link-text">My Profile</span></a>
    
    <div class="nav-section-label">Academics</div>
    <a href="attendance.php" class="sidebar-link <?= ($activePage == 'attendance.php') ? 'active' : ''; ?>"><i class="fas fa-calendar-check"></i> <span class="link-text">Attendance Record</span></a>
    <a href="results.php" class="sidebar-link <?= ($activePage == 'results.php') ? 'active' : ''; ?>"><i class="fas fa-file-alt"></i> <span class="link-text">Exam Results</span></a>
    <a href="assignments.php" class="sidebar-link <?= ($activePage == 'assignments.php') ? 'active' : ''; ?>"><i class="fas fa-book-open"></i> <span class="link-text">Assignments</span></a>
    <a href="timetable.php" class="sidebar-link <?= ($activePage == 'timetable.php') ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i> <span class="link-text">Timetable</span></a>
    
    <div class="nav-section-label">Other</div>
    <a href="fee-status.php" class="sidebar-link <?= ($activePage == 'fee-status.php') ? 'active' : ''; ?>"><i class="fas fa-file-invoice-dollar"></i> <span class="link-text">Fee Status</span></a>
    <a href="notices.php" class="sidebar-link <?= ($activePage == 'notices.php') ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i> <span class="link-text">Notices</span></a>
    <a href="events.php" class="sidebar-link <?= ($activePage == 'events.php') ? 'active' : ''; ?>"><i class="fas fa-glass-cheers"></i> <span class="link-text">Events</span></a>
  </div>
  
  <div class="sidebar-logout">
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
  </div>
</aside>