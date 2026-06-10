<?php
$activePage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
  <a href="dashboard.php" class="sidebar-brand">
    <img src="../assets/images/logo.png" alt="Logo" class="sidebar-logo" onerror="this.src='https://via.placeholder.com/42?text=PS'">
    <div class="sidebar-title">
      <h6>The Providence School</h6>
      <span>Admin Panel</span>
    </div>
  </a>
  
  <div class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="dashboard.php" class="sidebar-link <?= ($activePage == 'dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-th-large"></i> <span class="link-text">Dashboard</span></a>
    
    <div class="nav-section-label">People</div>
    <a href="students.php" class="sidebar-link <?= ($activePage == 'students.php') ? 'active' : ''; ?>"><i class="fas fa-user-graduate"></i> <span class="link-text">Students</span></a>
    <a href="teachers.php" class="sidebar-link <?= ($activePage == 'teachers.php') ? 'active' : ''; ?>"><i class="fas fa-chalkboard-teacher"></i> <span class="link-text">Teachers</span></a>
    <a href="parents.php" class="sidebar-link <?= ($activePage == 'parents.php') ? 'active' : ''; ?>"><i class="fas fa-users"></i> <span class="link-text">Parents</span></a>
    
    <div class="nav-section-label">Academics</div>
    <a href="classes.php" class="sidebar-link <?= ($activePage == 'classes.php') ? 'active' : ''; ?>"><i class="fas fa-school"></i> <span class="link-text">Classes</span></a>
    <a href="subjects.php" class="sidebar-link <?= ($activePage == 'subjects.php') ? 'active' : ''; ?>"><i class="fas fa-book"></i> <span class="link-text">Subjects</span></a>
    <a href="timetable.php" class="sidebar-link <?= ($activePage == 'timetable.php') ? 'active' : ''; ?>"><i class="fas fa-calendar-week"></i> <span class="link-text">Timetable</span></a>
    <a href="attendance.php" class="sidebar-link <?= ($activePage == 'attendance.php') ? 'active' : ''; ?>"><i class="fas fa-calendar-check"></i> <span class="link-text">Attendance</span></a>
    <a href="exams.php" class="sidebar-link <?= ($activePage == 'exams.php') ? 'active' : ''; ?>"><i class="fas fa-file-alt"></i> <span class="link-text">Exams</span></a>
    <a href="results.php" class="sidebar-link <?= ($activePage == 'results.php') ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> <span class="link-text">Results</span></a>
    
    <div class="nav-section-label">Management</div>
    <a href="fees.php" class="sidebar-link <?= ($activePage == 'fees.php') ? 'active' : ''; ?>"><i class="fas fa-file-invoice-dollar"></i> <span class="link-text">Fees</span></a>
    <a href="library.php" class="sidebar-link <?= ($activePage == 'library.php') ? 'active' : ''; ?>"><i class="fas fa-book-reader"></i> <span class="link-text">Library</span></a>
    <a href="transport.php" class="sidebar-link <?= ($activePage == 'transport.php') ? 'active' : ''; ?>"><i class="fas fa-bus"></i> <span class="link-text">Transport</span></a>
    
    <div class="nav-section-label">Communication</div>
    <a href="noticeboard.php" class="sidebar-link <?= ($activePage == 'noticeboard.php') ? 'active' : ''; ?>">
      <i class="fas fa-bullhorn"></i> <span class="link-text">Notice Board</span>
    </a>
    <a href="events.php" class="sidebar-link <?= ($activePage == 'events.php') ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i> <span class="link-text">Events</span></a>
    
    <div class="nav-section-label">System</div>
    <a href="reports.php" class="sidebar-link <?= ($activePage == 'reports.php') ? 'active' : ''; ?>"><i class="fas fa-chart-pie"></i> <span class="link-text">Reports</span></a>
    <a href="settings.php" class="sidebar-link <?= ($activePage == 'settings.php') ? 'active' : ''; ?>"><i class="fas fa-cog"></i> <span class="link-text">Settings</span></a>
  </div>
  
  <div class="sidebar-logout">
    <a href="../login.html"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
  </div>
</aside>
