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
$numeric_name = trim($_POST['numeric_name'] ?? '');
$capacity = trim($_POST['capacity'] ?? '40');
$section_name = trim($_POST['section_name'] ?? 'A');

if (empty($name) || empty($numeric_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Class name and numeric name are required.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Insert class
    $stmt = $pdo->prepare("INSERT INTO classes (name, numeric_name, capacity) VALUES (?, ?, ?)");
    $stmt->execute([$name, $numeric_name, $capacity]);
    
    $class_id = $pdo->lastInsertId();

    // Insert default section
    if (!empty($section_name)) {
        $stmt = $pdo->prepare("INSERT INTO sections (class_id, name) VALUES (?, ?)");
        $stmt->execute([$class_id, $section_name]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Class added successfully!']);
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
