<?php
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
$teacher_id = trim($_POST['teacher_id'] ?? '');
$email = trim($_POST['email'] ?? '');
$department = trim($_POST['department'] ?? '');
$qualification = trim($_POST['qualification'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($first_name) || empty($last_name) || empty($teacher_id) || empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'First name, last name, teacher ID, username and password are required.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        throw new Exception("Username already exists.");
    }

    // Check teacher ID
    $stmt = $pdo->prepare("SELECT id FROM teachers WHERE teacher_id = ?");
    $stmt->execute([$teacher_id]);
    if ($stmt->fetch()) {
        throw new Exception("Teacher ID already exists.");
    }

    // Insert user
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'teacher')");
    $stmt->execute([$username, $hashedPassword]);
    
    $user_id = $pdo->lastInsertId();

    // Insert teacher
    $stmt = $pdo->prepare("INSERT INTO teachers (user_id, teacher_id, first_name, last_name, email, department, qualification, join_date) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())");
    $stmt->execute([$user_id, $teacher_id, $first_name, $last_name, $email, $department, $qualification]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Teacher added successfully!']);
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
