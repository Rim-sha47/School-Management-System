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

$student_id = trim($_POST['student_id'] ?? '');
$fee_structure_id = trim($_POST['fee_structure_id'] ?? '');
$challan_no = trim($_POST['challan_no'] ?? '');
$month_year = trim($_POST['month_year'] ?? '');
$total_paid = trim($_POST['total_paid'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? 'cash');
$remarks = trim($_POST['remarks'] ?? '');
$collected_by = $_SESSION['user_id'];

if (empty($student_id) || empty($fee_structure_id) || empty($challan_no) || empty($total_paid)) {
    echo json_encode(['status' => 'error', 'message' => 'Student, fee structure, challan number and amount paid are required.']);
    exit;
}

try {
    // Check unique challan number
    $stmt = $pdo->prepare("SELECT id FROM fee_payments WHERE challan_no = ?");
    $stmt->execute([$challan_no]);
    if ($stmt->fetch()) {
        throw new Exception("Challan number already exists.");
    }

    // Fetch fee structure details
    $structStmt = $pdo->prepare("SELECT amount, late_fee FROM fee_structures WHERE id = ?");
    $structStmt->execute([$fee_structure_id]);
    $structure = $structStmt->fetch();
    
    if (!$structure) {
        throw new Exception("Fee structure not found.");
    }

    $amount = $structure['amount'];
    $late_fee = $structure['late_fee'] ?? 0;
    
    // Determine payment status
    if (floatval($total_paid) >= (floatval($amount) + floatval($late_fee))) {
        $status = 'paid';
    } elseif (floatval($total_paid) > 0) {
        $status = 'partial';
    } else {
        $status = 'unpaid';
    }

    // Insert payment record
    $stmt = $pdo->prepare("INSERT INTO fee_payments (student_id, fee_structure_id, challan_no, amount, late_fee, total_paid, payment_date, due_date, payment_method, status, month_year, remarks, collected_by) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 DAY), ?, ?, ?, ?, ?)");
    $stmt->execute([$student_id, $fee_structure_id, $challan_no, $amount, $late_fee, $total_paid, $payment_method, $status, $month_year, $remarks, $collected_by]);

    echo json_encode(['status' => 'success', 'message' => 'Payment recorded successfully!']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
