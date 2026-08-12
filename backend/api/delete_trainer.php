<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

$id = $_POST['id'] ?? '';

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing instructor ID']);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM trainers WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
