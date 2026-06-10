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
    $pdo->beginTransaction();

    // Fetch checkout details
    $issueStmt = $pdo->prepare("SELECT book_id, due_date FROM book_issues WHERE id = ? FOR UPDATE");
    $issueStmt->execute([$id]);
    $issue = $issueStmt->fetch();
    
    if (!$issue) {
        throw new Exception("Lending log not found.");
    }

    $book_id = $issue['book_id'];
    $due_date = $issue['due_date'];
    $return_date = date('Y-m-d');
    
    // Calculate fine (PKR 20 per day overdue)
    $fine = 0;
    if ($return_date > $due_date) {
        $diff = strtotime($return_date) - strtotime($due_date);
        $days = ceil($diff / (60 * 60 * 24));
        if ($days > 0) {
            $fine = $days * 20;
        }
    }

    // Update issue log
    $stmt = $pdo->prepare("UPDATE book_issues SET return_date = ?, fine = ?, status = 'returned' WHERE id = ?");
    $stmt->execute([$return_date, $fine, $id]);

    // Increment available copies
    $updateStmt = $pdo->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE id = ?");
    $updateStmt->execute([$book_id]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Book returned successfully. Fine: PKR ' . $fine]);
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
