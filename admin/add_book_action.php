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
$author = trim($_POST['author'] ?? '');
$isbn = trim($_POST['isbn'] ?? '');
$publisher = trim($_POST['publisher'] ?? '');
$category = trim($_POST['category'] ?? '');
$shelf_location = trim($_POST['shelf_location'] ?? '');
$total_copies = trim($_POST['total_copies'] ?? '1');
$publish_year = trim($_POST['publish_year'] ?? '');

if (empty($title) || empty($author)) {
    echo json_encode(['status' => 'error', 'message' => 'Title and Author are required.']);
    exit;
}

try {
    // Check ISBN uniqueness if provided
    if (!empty($isbn)) {
        $stmt = $pdo->prepare("SELECT id FROM books WHERE isbn = ?");
        $stmt->execute([$isbn]);
        if ($stmt->fetch()) {
            throw new Exception("A book with this ISBN already exists.");
        }
    }

    $year = empty($publish_year) ? null : $publish_year;

    $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, publisher, category, total_copies, available_copies, shelf_location, publish_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $author, $isbn, $publisher, $category, $total_copies, $total_copies, $shelf_location, $year]);

    echo json_encode(['status' => 'success', 'message' => 'Book added to catalog!']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
