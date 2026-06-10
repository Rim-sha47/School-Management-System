<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../config.php';

// Fetch settings
try {
    $stmt = $pdo->query("SELECT setting_key, setting_val FROM settings");
    $settings_raw = $stmt->fetchAll();
    $settings = [];
    foreach($settings_raw as $row) {
        $settings[$row['setting_key']] = $row['setting_val'];
    }
} catch(PDOException $e) {
    $error = "Error fetching settings: " . $e->getMessage();
    $settings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $pageTitle = "System Settings";
  include 'includes/header.php'; 
  ?>
</head>
<body class="dashboard-body">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── Sidebar ── -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ── Topbar ── -->
  <?php 
  $searchPlaceholder = "Search page options...";
  include 'includes/topbar.php'; 
  ?>

  <!-- ── Main Content ── -->
  <main class="main-content" id="mainContent">
    <div class="page-header">
      <h1>School Settings</h1>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Settings</li>
      </ol>
    </div>

    <?php if(isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form id="settingsForm">
      <div id="settingsAlert" class="alert d-none"></div>

      <div class="row g-4">
        <!-- Settings Category Selection (Left pane or Top bar on mobile) -->
        <div class="col-lg-3">
          <div class="dash-card">
            <div class="dash-card-header">
              <h5><i class="fas fa-sliders-h text-primary"></i> Categories</h5>
            </div>
            <div class="dash-card-body p-0">
              <div class="list-group list-group-flush" id="settingsTabs" role="tablist">
                <button class="list-group-item list-group-item-action active border-0 py-3" id="general-tab" data-bs-toggle="tab" data-bs-target="#generalSettings" type="button">
                  <i class="fas fa-university me-2 text-primary"></i> General Profile
                </button>
                <button class="list-group-item list-group-item-action border-0 py-3" id="localization-tab" data-bs-toggle="tab" data-bs-target="#localizationSettings" type="button">
                  <i class="fas fa-globe me-2 text-primary"></i> Localization
                </button>
                <button class="list-group-item list-group-item-action border-0 py-3" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notificationsSettings" type="button">
                  <i class="fas fa-paper-plane me-2 text-primary"></i> API Integrations
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Settings Inputs (Right pane) -->
        <div class="col-lg-9">
          <div class="tab-content" id="settingsTabContent">
            <!-- General Settings Tab -->
            <div class="tab-pane fade show active" id="generalSettings">
              <div class="dash-card">
                <div class="dash-card-header">
                  <h5><i class="fas fa-university text-primary"></i> General Institution Profile</h5>
                </div>
                <div class="dash-card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">School Name *</label>
                      <input type="text" name="school_name" class="form-control" value="<?php echo htmlspecialchars($settings['school_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">School Website</label>
                      <input type="text" name="school_website" class="form-control" value="<?php echo htmlspecialchars($settings['school_website'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Contact Phone *</label>
                      <input type="text" name="school_phone" class="form-control" value="<?php echo htmlspecialchars($settings['school_phone'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Official Email *</label>
                      <input type="email" name="school_email" class="form-control" value="<?php echo htmlspecialchars($settings['school_email'] ?? ''); ?>" required>
                    </div>
                    <div class="col-12">
                      <label class="form-label">School Postal Address *</label>
                      <textarea name="school_address" class="form-control" rows="3" required><?php echo htmlspecialchars($settings['school_address'] ?? ''); ?></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Localization Settings Tab -->
            <div class="tab-pane fade" id="localizationSettings">
              <div class="dash-card">
                <div class="dash-card-header">
                  <h5><i class="fas fa-globe text-primary"></i> Localization Configuration</h5>
                </div>
                <div class="dash-card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Current Academic Session *</label>
                      <input type="text" name="academic_year" class="form-control" value="<?php echo htmlspecialchars($settings['academic_year'] ?? ''); ?>" placeholder="e.g. 2025-2026" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Billing Currency Symbol *</label>
                      <input type="text" name="currency" class="form-control" value="<?php echo htmlspecialchars($settings['currency'] ?? ''); ?>" placeholder="e.g. PKR" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">System Timezone *</label>
                      <input type="text" name="timezone" class="form-control" value="<?php echo htmlspecialchars($settings['timezone'] ?? ''); ?>" placeholder="e.g. Asia/Karachi" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">System Date Format *</label>
                      <input type="text" name="date_format" class="form-control" value="<?php echo htmlspecialchars($settings['date_format'] ?? ''); ?>" placeholder="e.g. d M, Y" required>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notifications / API Settings Tab -->
            <div class="tab-pane fade" id="notificationsSettings">
              <div class="dash-card">
                <div class="dash-card-header">
                  <h5><i class="fas fa-paper-plane text-primary"></i> Messaging & Alerts Configuration</h5>
                </div>
                <div class="dash-card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label d-block">Enable SMS Notifications</label>
                      <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="sms_enabled" value="1" <?php echo ($settings['sms_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label text-muted small">Enable SMS integrations for attendance & marks alerts.</label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label d-block">Enable Email Alerts</label>
                      <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="email_enabled" value="1" <?php echo ($settings['email_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label text-muted small">Send automated emails for announcements & fee structures.</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary btn-lg px-4" id="saveSettingsBtn">
              <i class="fas fa-save me-1"></i> Save Changes
            </button>
          </div>
        </div>
      </div>
    </form>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/admin.js"></script>
  <script>
    // Save Settings AJAX
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = document.getElementById('saveSettingsBtn');
      const alertDiv = document.getElementById('settingsAlert');
      const originalText = btn.innerHTML;
      
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      btn.disabled = true;
      alertDiv.className = 'alert d-none';
      
      const formData = new FormData(this);
      
      // Ensure checkboxes send value even when unchecked
      if (!formData.has('sms_enabled')) {
        formData.append('sms_enabled', '0');
      }
      if (!formData.has('email_enabled')) {
        formData.append('email_enabled', '0');
      }
      
      fetch('update_settings_action.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if(data.status === 'success') {
          alertDiv.className = 'alert alert-success';
          alertDiv.textContent = data.message;
          btn.innerHTML = originalText;
          btn.disabled = false;
          window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
          alertDiv.className = 'alert alert-danger';
          alertDiv.textContent = data.message;
          btn.innerHTML = originalText;
          btn.disabled = false;
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      })
      .catch(err => {
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = 'A network error occurred.';
        btn.innerHTML = originalText;
        btn.disabled = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    });
  </script>
</body>
</html>
