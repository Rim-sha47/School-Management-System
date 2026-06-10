<?php
// update_notice_action.php
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

$id = $_POST['id'] ?? null;
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$audience = $_POST['audience'] ?? 'all';
$priority = $_POST['priority'] ?? 'normal';

if (empty($id) || empty($title) || empty($content)) {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE notices SET title = ?, content = ?, audience = ?, priority = ? WHERE id = ?");
    $stmt->execute([$title, $content, $audience, $priority, $id]);
    echo json_encode(['status' => 'success', 'message' => 'Notice updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}