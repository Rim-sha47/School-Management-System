<?php
// users.php - Admin Profile / My Account
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch current admin user data
try {
    $stmt = $pdo->prepare("SELECT id, username, role, last_login FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $error = "Error fetching profile: " . $e->getMessage();
    $user = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "My Profile";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- Topbar -->
  <?php 
  $searchPlaceholder = "Search...";
  include 'includes/topbar.php'; 
  ?>

  <!-- Main Content -->
  <main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center">
      <div>
        <h1>My Profile</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">My Profile</li>
        </ol>
      </div>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="dash-card">
          <div class="dash-card-header">
            <h5><i class="fas fa-user text-primary"></i> Account Information</h5>
          </div>
          <div class="dash-card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">Role</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars(ucfirst($user['role'] ?? '')); ?>" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">Last Login</label>
                <input type="text" class="form-control" value="<?php echo $user['last_login'] ? date('M d, Y h:i A', strtotime($user['last_login'])) : 'Never'; ?>" readonly>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="dash-card">
          <div class="dash-card-header">
            <h5><i class="fas fa-user-circle text-primary"></i> Profile Picture</h5>
          </div>
          <div class="dash-card-body text-center py-4">
            <div class="mb-3">
              <div class="avatar-xl mx-auto">
                <?php echo strtoupper(substr($_SESSION['username'] ?? 'AD', 0, 2)); ?>
              </div>
            </div>
            <small class="text-muted">Profile picture upload coming soon</small>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/admin.js"></script>
</body>
</html>