<?php
// update_settings_action.php
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

$allowed_keys = [
    'school_name', 'school_address', 'school_phone', 'school_email', 'school_website',
    'school_logo', 'academic_year', 'currency', 'date_format', 'timezone',
    'sms_enabled', 'email_enabled'
];

try {
    $pdo->beginTransaction();
    
    foreach ($allowed_keys as $key) {
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            if ($key === 'sms_enabled' || $key === 'email_enabled') {
                $value = $value === '1' ? '1' : '0';
            }
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_val) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_val = VALUES(setting_val)");
            $stmt->execute([$key, $value]);
        }
    }
    
    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Settings saved successfully!']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}