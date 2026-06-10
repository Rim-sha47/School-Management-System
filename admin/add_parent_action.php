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
$relation = trim($_POST['relation'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$cnic = trim($_POST['cnic'] ?? '');
$occupation = trim($_POST['occupation'] ?? '');
$student_id = trim($_POST['student_id'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($first_name) || empty($last_name) || empty($phone) || empty($student_id) || empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'First name, last name, phone, linked student, username and password are required.']);
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

    // Insert user
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'parent')");
    $stmt->execute([$username, $hashedPassword]);
    
    $user_id = $pdo->lastInsertId();

    // Insert parent
    $stmt = $pdo->prepare("INSERT INTO parents (user_id, first_name, last_name, relation, email, phone, cnic, occupation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $first_name, $last_name, $relation, $email, $phone, $cnic, $occupation]);
    
    $parent_id = $pdo->lastInsertId();

    // Link student
    $stmt = $pdo->prepare("INSERT INTO parent_students (parent_id, student_id) VALUES (?, ?)");
    $stmt->execute([$parent_id, $student_id]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Parent added successfully!']);
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
