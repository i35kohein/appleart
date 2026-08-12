<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

$id = $_POST['id'] ?? '';
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$shop_name = $_POST['shop_name'] ?? '';
$photo_path = $_POST['photo_path'] ?? null;
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
$role = ($_POST['role'] ?? 'student') === 'online' ? 'online' : 'student';
$rollcall_group = ($_POST['rollcall_group'] ?? 'Weekday') === 'Weekend' ? 'Weekend' : 'Weekday';

if(!empty($id) && !empty($name)) {
    try {
        if ($photo_path !== null && $photo_path !== '') {
            $stmt = $conn->prepare("UPDATE students SET name=?, phone=?, email=?, address=?, photo_path=?, is_active=?, role=?, rollcall_group=?, shop_name=? WHERE id=?");
            $stmt->execute([$name, $phone, $email, $address, $photo_path, $is_active ? 1 : 0, $role, $rollcall_group, $shop_name, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE students SET name=?, phone=?, email=?, address=?, is_active=?, role=?, rollcall_group=?, shop_name=? WHERE id=?");
            $stmt->execute([$name, $phone, $email, $address, $is_active ? 1 : 0, $role, $rollcall_group, $shop_name, $id]);
        }
        echo json_encode(['status' => 'success']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
}
?>
