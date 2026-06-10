<?php
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
    <button class="topbar-icon-btn">
      <i class="far fa-bell"></i>
      <span class="badge-dot"></span>
    </button>
    
    <div class="dropdown">
      <button class="topbar-profile" data-bs-toggle="dropdown" aria-label="Profile menu">
        <div class="topbar-avatar" style="background:var(--accent);"><?php echo strtoupper(substr($_SESSION['username'] ?? 'TE', 0, 2)); ?></div>
        <div class="topbar-profile-info d-none d-sm-block">
          <div class="topbar-profile-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Teacher'); ?></div>
          <div class="topbar-profile-role">Teacher</div>
        </div>
      </button>
      <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
        <li>
          <a class="dropdown-item py-2" href="profile.php">
            <i class="far fa-user me-2 text-primary"></i>My Profile
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