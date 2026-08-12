<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


$id = $_POST['id'] ?? '';

if(!empty($id)) {
    // Delete progress references first (to prevent SQL foreign key errors)
    $stmt1 = $conn->prepare("DELETE FROM student_progress WHERE item_id=?");
    $stmt1->execute([$id]);
    
    // Delete actual item
    $stmt2 = $conn->prepare("DELETE FROM curriculum_items WHERE id=?");
    $stmt2->execute([$id]);
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
?>