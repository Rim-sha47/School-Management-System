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
$class_id = trim($_POST['class_id'] ?? '');
$type = trim($_POST['type'] ?? 'monthly');
$amount = trim($_POST['amount'] ?? '');
$late_fee = trim($_POST['late_fee'] ?? '0');
$due_day = trim($_POST['due_day'] ?? '10');

if (empty($name) || empty($amount)) {
    echo json_encode(['status' => 'error', 'message' => 'Structure name and amount are required.']);
    exit;
}

try {
    $c_id = empty($class_id) ? null : $class_id;
    
    // Fetch current session
    $sessionStmt = $pdo->query("SELECT id FROM academic_sessions WHERE is_current = 1 LIMIT 1");
    $session = $sessionStmt->fetch();
    $session_id = $session ? $session['id'] : null;

    $stmt = $pdo->prepare("INSERT INTO fee_structures (name, class_id, session_id, amount, type, due_day, late_fee, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->execute([$name, $c_id, $session_id, $amount, $type, $due_day, $late_fee]);

    echo json_encode(['status' => 'success', 'message' => 'Fee structure added successfully!']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
