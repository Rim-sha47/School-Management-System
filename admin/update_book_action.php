<?php
// update_book_action.php
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
$author = trim($_POST['author'] ?? '');
$isbn = trim($_POST['isbn'] ?? '');
$publisher = trim($_POST['publisher'] ?? '');
$shelf_location = trim($_POST['shelf_location'] ?? '');

if (empty($id) || empty($title)) {
    echo json_encode(['status' => 'error', 'message' => 'Title is required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, isbn = ?, publisher = ?, shelf_location = ? WHERE id = ?");
    $stmt->execute([$title, $author ?: null, $isbn ?: null, $publisher ?: null, $shelf_location ?: null, $id]);
    echo json_encode(['status' => 'success', 'message' => 'Book updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}