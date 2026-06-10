<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.html");
    exit;
}
require_once '../config.php';

$pageTitle = "My Profile";
$searchPlaceholder = "Search...";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/header.php'; ?>
  <style>
    .avatar-xl { width: 120px; height: 120px; border-radius: 50%; background: var(--grad-primary); display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 700; color: white; margin: 0 auto 15px; cursor: pointer; transition: transform 0.3s; }
    .avatar-xl:hover { transform: scale(1.05); }
    .avatar-xl img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
    #profilePhotoInput { display: none; }
    .form-control.edit-mode { background: #f8fafc; border-color: var(--primary); cursor: text; }
    .form-control.edit-mode:focus { background: #fff; }
    .photo-actions { display: none; margin-top: 10px; }
    .profile-section { position: relative; }
    .profile-section.editing .photo-actions { display: block; }
  </style>
</head>
<body class="dashboard-body">
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/topbar.php'; ?>

  <main class="main-content" id="mainContent">
    <div class="page-header">
      <h1>My Profile</h1>
      <p class="text-muted mt-1 mb-0">View and update your personal information.</p>
    </div>

    <div class="row g-4 profile-section" id="profileSection">
      <div class="col-lg-4 col-md-12">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-user"></i> Profile Picture</h5>
          </div>
          <div class="dash-card-body text-center p-4">
            <label for="profilePhotoInput">
              <div class="avatar-xl" id="profileAvatar">ST</div>
            </label>
            <input type="file" id="profilePhotoInput" accept="image/*">
            <h5 class="mb-1">Student Name</h5>
            <p class="text-muted mb-2">Student</p>
            <div class="photo-actions">
              <button type="button" class="btn btn-sm btn-outline-primary me-2"><i class="fas fa-edit"></i> Edit</button>
              <button type="button" class="btn btn-sm btn-outline-danger" id="removePhotoBtn"><i class="fas fa-trash"></i> Remove</button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-8 col-md-12">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <h5><i class="fas fa-id-card"></i> Personal Information</h5>
            <div>
              <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="cancelEditBtn" style="display:none;">Cancel</button>
              <button type="button" class="btn btn-sm btn-success me-2" id="saveProfileBtn" style="display:none;">Save</button>
              <button type="button" class="btn btn-sm btn-primary" id="editProfileBtn"><i class="fas fa-edit me-1"></i> Edit Profile</button>
            </div>
          </div>
          <div class="dash-card-body">
            <form id="profileForm">
              <div class="row g-3">
                <div class="col-md-6 col-12">
                  <label class="form-label">First Name *</label>
                  <input type="text" name="first_name" class="form-control" value="Student" readonly>
                </div>
                <div class="col-md-6 col-12">
                  <label class="form-label">Last Name *</label>
                  <input type="text" name="last_name" class="form-control" value="Name" readonly>
                </div>
                <div class="col-md-6 col-12">
                  <label class="form-label">Registration No</label>
                  <input type="text" name="reg_no" class="form-control" value="STD-2021-001" readonly>
                </div>
                <div class="col-md-6 col-12">
                  <label class="form-label">Email *</label>
                  <input type="email" name="email" class="form-control" value="student@school.edu" readonly>
                </div>
                <div class="col-md-6 col-12">
                  <label class="form-label">Phone</label>
                  <input type="text" name="phone" class="form-control" value="" readonly>
                </div>
                <div class="col-md-6 col-12">
                  <label class="form-label">Class</label>
                  <input type="text" name="class" class="form-control" value="Class 10-A" readonly>
                </div>
                <div class="col-md-6 col-12">
                  <label class="form-label">DOB</label>
                  <input type="date" name="dob" class="form-control" value="" readonly>
                </div>
                <div class="col-12">
                  <label class="form-label">Address</label>
                  <textarea name="address" class="form-control" rows="2" readonly></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label">Parent/Guardian Name</label>
                  <input type="text" name="parent_name" class="form-control" value="Mr. Parent" readonly>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/student.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    
    const editBtn = document.getElementById('editProfileBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');
    const saveBtn = document.getElementById('saveProfileBtn');
    const inputs = document.querySelectorAll('#profileForm .form-control');
    const profileSection = document.getElementById('profileSection');
    const photoInput = document.getElementById('profilePhotoInput');
    const avatar = document.getElementById('profileAvatar');
    const removePhotoBtn = document.getElementById('removePhotoBtn');
    
    let originalAvatar = avatar.innerHTML;
    
    function enableEdit() {
      profileSection.classList.add('editing');
      inputs.forEach(i => {
        i.removeAttribute('readonly');
        i.classList.add('edit-mode');
      });
      editBtn.style.display = 'none';
      cancelBtn.style.display = 'inline-block';
      saveBtn.style.display = 'inline-block';
    }
    
    function disableEdit() {
      profileSection.classList.remove('editing');
      inputs.forEach(i => {
        i.setAttribute('readonly', true);
        i.classList.remove('edit-mode');
      });
      editBtn.style.display = 'inline-block';
      cancelBtn.style.display = 'none';
      saveBtn.style.display = 'none';
    }
    
    editBtn.addEventListener('click', enableEdit);
    cancelBtn.addEventListener('click', disableEdit);
    saveBtn.addEventListener('click', function() {
      alert('Profile saved successfully!');
      disableEdit();
    });
    
    photoInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          avatar.innerHTML = `<img src="${e.target.result}" alt="Profile">`;
          avatar.style.background = 'transparent';
        };
        reader.readAsDataURL(file);
      }
    });
    
    removePhotoBtn.addEventListener('click', function() {
      avatar.innerHTML = originalAvatar;
      avatar.style.background = '';
      photoInput.value = '';
    });
  });
  </script>
</body>
</html>