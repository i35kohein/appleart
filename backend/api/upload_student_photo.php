<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

try {
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $errorCode = $_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE;
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Photo is larger than the server upload limit. Choose a photo under 2MB.',
            UPLOAD_ERR_FORM_SIZE => 'Photo is larger than the allowed upload size.',
            UPLOAD_ERR_PARTIAL => 'Photo upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_FILE => 'No photo uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Upload temp folder is missing on the server.',
            UPLOAD_ERR_CANT_WRITE => 'Could not write uploaded photo on the server.',
            UPLOAD_ERR_EXTENSION => 'A server extension stopped the upload.'
        ];
        echo json_encode(['status' => 'error', 'message' => $messages[$errorCode] ?? 'Photo upload failed.']);
        exit;
    }

    $studentId = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
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

    $uploadDir = dirname(__DIR__) . '/uploads/students';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $extension = $mimeMap[$mime];
    $filename = 'student_' . ($studentId > 0 ? $studentId . '_' : '') . bin2hex(random_bytes(8)) . '.' . $extension;
    $targetPath = $uploadDir . '/' . $filename;
    $relativePath = 'uploads/students/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode(['status' => 'error', 'message' => 'Could not save uploaded photo.']);
        exit;
    }

    if ($studentId > 0) {
        $stmt = $conn->prepare("UPDATE students SET photo_path = ? WHERE id = ?");
        $stmt->execute([$relativePath, $studentId]);
    }

    echo json_encode(['status' => 'success', 'photo_path' => $relativePath]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
