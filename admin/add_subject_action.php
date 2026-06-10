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

$name = trim($_POST['name'] ?? '');
$code = trim($_POST['code'] ?? '');
$type = trim($_POST['type'] ?? 'theory');
$class_id = trim($_POST['class_id'] ?? '');

if (empty($name) || empty($code)) {
    echo json_encode(['status' => 'error', 'message' => 'Subject name and code are required.']);
    exit;
}

try {
    // Check if code is unique
    $stmt = $pdo->prepare("SELECT id FROM subjects WHERE code = ?");
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        throw new Exception("Subject code already exists.");
    }

    $c_id = empty($class_id) ? null : $class_id;

    // Insert subject
    $stmt = $pdo->prepare("INSERT INTO subjects (name, code, class_id, type) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $code, $c_id, $type]);

    echo json_encode(['status' => 'success', 'message' => 'Subject added successfully!']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
