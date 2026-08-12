<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

try {
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'No photo uploaded.']);
        exit;
    }

    $trainerId = isset($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : 0;
    $file = $_FILES['photo'];
    $maxBytes = 2 * 1024 * 1024;

    if ($file['size'] > $maxBytes) {
        echo json_encode(['status' => 'error', 'message' => 'Photo must be 2MB or smaller.']);
        exit;
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        echo json_encode(['status' => 'error', 'message' => 'Upload a valid image file.']);
        exit;
    }

    $mimeMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif'
    ];

    $mime = $info['mime'] ?? '';
    if (!isset($mimeMap[$mime])) {
        echo json_encode(['status' => 'error', 'message' => 'Supported formats: JPG, PNG, WebP, GIF.']);
        exit;
    }

    $uploadDir = dirname(__DIR__) . '/uploads/trainers';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'trainer_' . ($trainerId > 0 ? $trainerId . '_' : '') . bin2hex(random_bytes(8)) . '.' . $mimeMap[$mime];
    $targetPath = $uploadDir . '/' . $filename;
    $relativePath = 'uploads/trainers/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode(['status' => 'error', 'message' => 'Could not save uploaded photo.']);
        exit;
    }

    if ($trainerId > 0) {
        $stmt = $conn->prepare("UPDATE trainers SET photo_path = ? WHERE id = ?");
        $stmt->execute([$relativePath, $trainerId]);
    }

    echo json_encode(['status' => 'success', 'photo_path' => $relativePath]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
