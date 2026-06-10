<?php
// login_action.php
// Handles authentication requests via AJAX

session_start();
header('Content-Type: application/json');

// Include database config
require_once 'config.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Get POST data
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';

// Validate input
if (empty($username) || empty($password) || empty($role)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide username, password, and role.']);
    exit;
}

try {
    // Check if the user exists and matches the role
    if ($role === 'admin') {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username AND role IN ('admin', 'super_admin') LIMIT 1");
        $stmt->execute(['username' => $username]);
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username AND role = :role LIMIT 1");
        $stmt->execute(['username' => $username, 'role' => $role]);
    }
    $user = $stmt->fetch();

    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Get specific role details (Optional, just for demonstration)
            if ($role === 'student') {
                $studentStmt = $pdo->prepare("SELECT first_name, last_name, registration_number FROM students WHERE user_id = :user_id");
                $studentStmt->execute(['user_id' => $user['id']]);
                $_SESSION['user_details'] = $studentStmt->fetch();
            } elseif ($role === 'teacher') {
                $teacherStmt = $pdo->prepare("SELECT first_name, last_name, teacher_id FROM teachers WHERE user_id = :user_id");
                $teacherStmt->execute(['user_id' => $user['id']]);
                $_SESSION['user_details'] = $teacherStmt->fetch();
            } else {
                $_SESSION['user_details'] = ['first_name' => 'Admin', 'last_name' => 'User'];
            }

            // Define redirect URLs based on role
            $redirects = [
                'admin' => 'admin/dashboard.php',
                'super_admin' => 'admin/dashboard.php',
                'teacher' => 'teacher/dashboard.php',
                'student' => 'student/dashboard.php'
            ];

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'redirect' => $redirects[$user['role']]
            ]);
        } else {
            // Invalid password
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
        }
    } else {
        // User not found or role mismatch
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
    }
} catch (PDOException $e) {
    // Database error
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
