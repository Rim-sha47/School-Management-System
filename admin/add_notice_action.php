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

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$audience = trim($_POST['audience'] ?? 'all');
$priority = trim($_POST['priority'] ?? 'normal');
$publish_date = trim($_POST['publish_date'] ?? '');
$expiry_date = trim($_POST['expiry_date'] ?? '');
$created_by = $_SESSION['user_id'];

if (empty($title) || empty($content) || empty($publish_date)) {
    echo json_encode(['status' => 'error', 'message' => 'Title, content and publish date are required.']);
    exit;
}

try {
    $exp = empty($expiry_date) ? null : $expiry_date;
    
    $stmt = $pdo->prepare("INSERT INTO notices (title, content, audience, priority, is_published, publish_date, expiry_date, created_by) VALUES (?, ?, ?, ?, 1, ?, ?, ?)");
    $stmt->execute([$title, $content, $audience, $priority, $publish_date, $exp, $created_by]);

    echo json_encode(['status' => 'success', 'message' => 'Notice published successfully!']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
