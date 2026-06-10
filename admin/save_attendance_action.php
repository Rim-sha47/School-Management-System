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

$class_id = $_POST['class_id'] ?? '';
$section_id = $_POST['section_id'] ?? '';
$date = $_POST['date'] ?? '';
$attendance_data = $_POST['attendance'] ?? [];
$remarks_data = $_POST['remarks'] ?? [];
$marked_by = $_SESSION['user_id'];

if (empty($class_id) || empty($section_id) || empty($date)) {
    echo json_encode(['status' => 'error', 'message' => 'Class, Section and Date are required.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Prepare upsert statement
    $stmt = $pdo->prepare("
        INSERT INTO attendance (student_id, class_id, section_id, date, status, remarks, marked_by)
        VALUES (:student_id, :class_id, :section_id, :date, :status, :remarks, :marked_by)
        ON DUPLICATE KEY UPDATE 
            status = VALUES(status), 
            remarks = VALUES(remarks),
            marked_by = VALUES(marked_by)
    ");

    foreach ($attendance_data as $student_id => $status) {
        $remarks = $remarks_data[$student_id] ?? '';
        $stmt->execute([
            'student_id' => $student_id,
            'class_id' => $class_id,
            'section_id' => $section_id,
            'date' => $date,
            'status' => $status,
            'remarks' => $remarks,
            'marked_by' => $marked_by
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Attendance saved successfully.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
