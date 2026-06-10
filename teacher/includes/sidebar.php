<?php
$activePage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
  <a href="dashboard.php" class="sidebar-brand">
    <img src="../assets/images/logo.png" alt="Logo" class="sidebar-logo" onerror="this.src='https://via.placeholder.com/42?text=PS'">
    <div class="sidebar-title">
      <h6>The Providence School</h6>
      <span>Teacher Portal</span>
    </div>
  </a>
  
  <div class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="dashboard.php" class="sidebar-link <?= ($activePage == 'dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-chalkboard"></i> <span class="link-text">My Dashboard</span></a>
    <a href="classes.php" class="sidebar-link <?= ($activePage == 'classes.php') ? 'active' : ''; ?>"><i class="fas fa-users-class"></i> <span class="link-text">My Classes</span></a>
    
    <div class="nav-section-label">Academics</div>
    <a href="attendance.php" class="sidebar-link <?= ($activePage == 'attendance.php') ? 'active' : ''; ?>"><i class="fas fa-clipboard-check"></i> <span class="link-text">Attendance</span></a>
    <a href="assignments.php" class="sidebar-link <?= ($activePage == 'assignments.php') ? 'active' : ''; ?>"><i class="fas fa-tasks"></i> <span class="link-text">Assignments</span></a>
    <a href="exams.php" class="sidebar-link <?= ($activePage == 'exams.php') ? 'active' : ''; ?>"><i class="fas fa-file-signature"></i> <span class="link-text">Exams</span></a>
    <a href="results.php" class="sidebar-link <?= ($activePage == 'results.php') ? 'active' : ''; ?>"><i class="fas fa-poll"></i> <span class="link-text">Results Entry</span></a>
    <a href="timetable.php" class="sidebar-link <?= ($activePage == 'timetable.php') ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i> <span class="link-text">My Timetable</span></a>
    
    <div class="nav-section-label">Communication</div>
    <a href="notices.php" class="sidebar-link <?= ($activePage == 'notices.php') ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i> <span class="link-text">Notice Board</span></a>
    <a href="messages.php" class="sidebar-link <?= ($activePage == 'messages.php') ? 'active' : ''; ?>"><i class="fas fa-comments"></i> <span class="link-text">Parent Messages</span> <span class="sidebar-badge bg-danger">2</span></a>
    
    <div class="nav-section-label">Personal</div>
    <a href="profile.php" class="sidebar-link <?= ($activePage == 'profile.php') ? 'active' : ''; ?>"><i class="fas fa-user-edit"></i> <span class="link-text">My Profile</span></a>
  </div>
  
  <div class="sidebar-logout">
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
  </div>
</aside>