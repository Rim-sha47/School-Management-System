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

$exam_id = $_POST['exam_id'] ?? '';
$subject_id = $_POST['subject_id'] ?? '';
$total_marks = $_POST['total_marks'] ?? '100';
$marks_data = $_POST['marks'] ?? [];
$grade_data = $_POST['grade'] ?? [];
$remarks_data = $_POST['remarks'] ?? [];

if (empty($exam_id) || empty($subject_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Exam and Subject are required.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Prepare upsert statement
    $stmt = $pdo->prepare("
        INSERT INTO results (student_id, exam_id, subject_id, marks_obtained, total_marks, grade, remarks, is_published)
        VALUES (:student_id, :exam_id, :subject_id, :marks_obtained, :total_marks, :grade, :remarks, 1)
        ON DUPLICATE KEY UPDATE 
            marks_obtained = VALUES(marks_obtained), 
            total_marks = VALUES(total_marks),
            grade = VALUES(grade),
            remarks = VALUES(remarks)
    ");

    foreach ($marks_data as $student_id => $marks) {
        if ($marks === '') continue; // Skip empty fields
        $grade = $grade_data[$student_id] ?? '';
        $remarks = $remarks_data[$student_id] ?? '';
        
        $stmt->execute([
            'student_id' => $student_id,
            'exam_id' => $exam_id,
            'subject_id' => $subject_id,
            'marks_obtained' => $marks,
            'total_marks' => $total_marks,
            'grade' => $grade,
            'remarks' => $remarks
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Marks recorded successfully.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
