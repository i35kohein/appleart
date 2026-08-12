<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST only']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing id']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT file_path FROM curriculum_materials WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Material not found']);
        exit;
    }
    $del = $conn->prepare("DELETE FROM curriculum_materials WHERE id = :id");
    $del->execute(['id' => $id]);

    $full = dirname(__DIR__) . '/' . $row['file_path'];
    if (is_file($full)) @unlink($full);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
