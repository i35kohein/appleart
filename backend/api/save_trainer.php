<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

$id = $_POST['id'] ?? ''; $name = $_POST['name'] ?? ''; $role = $_POST['role'] ?? 'Instructor'; $photo_path = $_POST['photo_path'] ?? null;
if(!empty($name)) {
    if(!empty($id)) {
        if ($photo_path !== null && $photo_path !== '') {
            $conn->prepare("UPDATE trainers SET name=?, role=?, photo_path=? WHERE id=?")->execute([$name, $role, $photo_path, $id]);
        } else {
            $conn->prepare("UPDATE trainers SET name=?, role=? WHERE id=?")->execute([$name, $role, $id]);
        }
    } else {
        $conn->prepare("INSERT INTO trainers (name, role, photo_path) VALUES (?, ?, ?)")->execute([$name, $role, $photo_path ?: null]);
    }
    echo json_encode(['status' => 'success']);
} else { echo json_encode(['status' => 'error']); }
?>
