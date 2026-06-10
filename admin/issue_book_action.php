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

$book_id = trim($_POST['book_id'] ?? '');
$student_id = trim($_POST['student_id'] ?? '');
$issue_date = trim($_POST['issue_date'] ?? '');
$due_date = trim($_POST['due_date'] ?? '');
$issued_by = $_SESSION['user_id'];

if (empty($book_id) || empty($student_id) || empty($issue_date) || empty($due_date)) {
    echo json_encode(['status' => 'error', 'message' => 'Book, student, issue date and due date are required.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verify copies are available
    $bookStmt = $pdo->prepare("SELECT available_copies FROM books WHERE id = ? FOR UPDATE");
    $bookStmt->execute([$book_id]);
    $book = $bookStmt->fetch();
    
    if (!$book || intval($book['available_copies']) <= 0) {
        throw new Exception("No available copies of this book to issue.");
    }

    // Insert issue log
    $stmt = $pdo->prepare("INSERT INTO book_issues (book_id, student_id, issue_date, due_date, status, issued_by) VALUES (?, ?, ?, ?, 'issued', ?)");
    $stmt->execute([$book_id, $student_id, $issue_date, $due_date, $issued_by]);

    // Decrement available copies
    $updateStmt = $pdo->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
    $updateStmt->execute([$book_id]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Book issued successfully!']);
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
