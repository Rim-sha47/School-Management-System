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

$id = $_POST['id'] ?? '';

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID is required.']);
    exit;
}

try {
    // Get user_id first
    $stmt = $pdo->prepare("SELECT user_id FROM parents WHERE id = ?");
    $stmt->execute([$id]);
    $parent = $stmt->fetch();
    
    if (!$parent) {
        throw new Exception("Parent record not found.");
    }
    
    $user_id = $parent['user_id'];
    
    if ($user_id) {
        // Delete user, which cascades to parents
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
    } else {
        // If there's no user_id, delete from parents table directly
        $stmt = $pdo->prepare("DELETE FROM parents WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Parent deleted successfully.']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
