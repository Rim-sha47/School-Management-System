<?php
// update_event_action.php
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
$description = trim($_POST['description'] ?? '');
$event_date = $_POST['event_date'] ?? '';
$type = $_POST['type'] ?? 'other';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$venue = trim($_POST['venue'] ?? '');

if (empty($id) || empty($title) || empty($event_date)) {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, type = ?, start_time = ?, end_time = ?, venue = ? WHERE id = ?");
    $stmt->execute([$title, $description ?: null, $event_date, $type, $start_time ?: null, $end_time ?: null, $venue ?: null, $id]);
    echo json_encode(['status' => 'success', 'message' => 'Event updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}