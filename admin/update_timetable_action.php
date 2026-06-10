<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $class_id = $_POST['class_id'] ?? '';
    $section_id = !empty($_POST['section_id']) ? $_POST['section_id'] : null;
    $subject_id = $_POST['subject_id'] ?? '';
    $teacher_id = $_POST['teacher_id'] ?? '';
    $day_of_week = $_POST['day_of_week'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $room_number = $_POST['room_number'] ?? '';

    if (empty($id) || empty($class_id) || empty($subject_id) || empty($teacher_id) || empty($day_of_week) || empty($start_time) || empty($end_time)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE timetables SET class_id=?, section_id=?, subject_id=?, teacher_id=?, day_of_week=?, start_time=?, end_time=?, room_number=? WHERE id=?");
        $stmt->execute([$class_id, $section_id, $subject_id, $teacher_id, $day_of_week, $start_time, $end_time, $room_number, $id]);
        
        echo json_encode(['status' => 'success', 'message' => 'Schedule updated successfully']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
