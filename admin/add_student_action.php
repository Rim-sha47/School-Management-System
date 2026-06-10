<?php
// add_student_action.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$reg_no = trim($_POST['registration_number'] ?? '');
$class_id = trim($_POST['class_id'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($first_name) || empty($last_name) || empty($reg_no) || empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check if username exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        throw new Exception("Username already exists.");
    }

    // Check if registration number exists
    $stmt = $pdo->prepare("SELECT id FROM students WHERE registration_number = ?");
    $stmt->execute([$reg_no]);
    if ($stmt->fetch()) {
        throw new Exception("Registration number already exists.");
    }

    // Hash password and insert into users
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
    $stmt->execute([$username, $hashedPassword]);
    
    $user_id = $pdo->lastInsertId();

    // Insert into students
    $stmt = $pdo->prepare("INSERT INTO students (user_id, registration_number, first_name, last_name, class_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $reg_no, $first_name, $last_name, $class_id]);

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Student added successfully!']);
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
