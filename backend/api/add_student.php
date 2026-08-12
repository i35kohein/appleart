<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$shop_name = $_POST['shop_name'] ?? '';
$photo_path = $_POST['photo_path'] ?? '';
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
$role = ($_POST['role'] ?? 'student') === 'online' ? 'online' : 'student';
$rollcall_group = ($_POST['rollcall_group'] ?? 'Weekday') === 'Weekend' ? 'Weekend' : 'Weekday';
// Time Machine support: optional enrollment_date (YYYY-MM-DD); defaults to today
$enrollment_date = trim($_POST['enrollment_date'] ?? '');
if ($enrollment_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $enrollment_date)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid enrollment_date (use YYYY-MM-DD)']);
    exit;
}
$enrollment_date = $enrollment_date !== '' ? $enrollment_date : date('Y-m-d');

if(!empty($name)) {
    try {
        $stmt = $conn->prepare("INSERT INTO students (name, phone, email, address, photo_path, is_active, role, rollcall_group, shop_name, enrollment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $address, $photo_path, $is_active ? 1 : 0, $role, $rollcall_group, $shop_name, $enrollment_date]);
        echo json_encode(['status' => 'success']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Name is required']);
}
?>
