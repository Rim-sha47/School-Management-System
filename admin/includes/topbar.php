<?php
/**
 * admin/includes/topbar.php
 * Reusable top navigation bar for all admin pages.
 */
$searchPlaceholder = $searchPlaceholder ?? 'Search...';
?>
<header class="topbar" id="topbar">
  <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
    <i class="fas fa-bars"></i>
  </button>

  <div class="topbar-search">
    <i class="fas fa-search"></i>
    <input type="text" id="globalSearch" placeholder="<?php echo htmlspecialchars($searchPlaceholder); ?>">
  </div>

  <div class="topbar-actions">
    <!-- Notifications -->
    <div class="dropdown">
      <button class="topbar-icon-btn" data-bs-toggle="dropdown" id="notifBtn" aria-label="Notifications">
        <i class="far fa-bell"></i>
        <span class="badge-dot"></span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end border-0 shadow notif-dropdown" style="width:320px; padding:0;">
        <li class="notif-header">
          <span><i class="fas fa-bell me-2"></i>Notifications</span>
          <span class="badge bg-danger rounded-pill">3</span>
        </li>
        <li>
          <a class="dropdown-item notif-item" href="noticeboard.php">
            <div class="notif-icon urgent"><i class="fas fa-exclamation"></i></div>
            <div class="notif-content">
              <div class="notif-title">Summer Vacation Announcement</div>
              <div class="notif-time"><i class="far fa-clock me-1"></i>1 hour ago</div>
            </div>
          </a>
        </li>
        <li>
          <a class="dropdown-item notif-item" href="fees.php">
            <div class="notif-icon warning"><i class="fas fa-rupee-sign"></i></div>
            <div class="notif-content">
              <div class="notif-title">Fee Submission Deadline</div>
              <div class="notif-time"><i class="far fa-clock me-1"></i>Yesterday</div>
            </div>
          </a>
        </li>
        <li>
          <a class="dropdown-item notif-item" href="events.php">
            <div class="notif-icon info"><i class="fas fa-calendar"></i></div>
            <div class="notif-content">
              <div class="notif-title">Annual Sports Day</div>
              <div class="notif-time"><i class="far fa-clock me-1"></i>3 days ago</div>
            </div>
          </a>
        </li>
        <li>
          <a class="dropdown-item text-center py-2 small text-primary fw-semibold" href="noticeboard.php">
            View all notifications
          </a>
        </li>
      </ul>
    </div>

    <!-- Messages -->
    <button class="topbar-icon-btn" aria-label="Messages">
      <i class="far fa-envelope"></i>
    </button>

    <!-- Profile Dropdown -->
    <div class="dropdown">
      <button class="topbar-profile" data-bs-toggle="dropdown" aria-label="Profile menu">
        <div class="topbar-avatar"><?php echo strtoupper(substr($_SESSION['username'] ?? 'AD', 0, 2)); ?></div>
        <div class="topbar-profile-info d-none d-sm-block">
          <div class="topbar-profile-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></div>
          <div class="topbar-profile-role">Super Admin</div>
        </div>
        <i class="fas fa-chevron-down ms-1 small d-none d-sm-block" style="color:var(--gray-500)"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2" style="min-width:180px;">
        <li>
          <a class="dropdown-item py-2" href="users.php">
            <i class="far fa-user me-2 text-primary"></i>My Profile
          </a>
        </li>
        <li>
          <a class="dropdown-item py-2" href="settings.php">
            <i class="fas fa-cog me-2 text-primary"></i>Settings
          </a>
        </li>
        <li><hr class="dropdown-divider my-1"></li>
        <li>
          <a class="dropdown-item py-2 text-danger" href="../logout.php">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</header>
