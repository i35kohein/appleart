<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST only']);
    exit;
}

$item_id = intval($_POST['item_id'] ?? 0);
if ($item_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing item_id']);
    exit;
}
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    exit;
}
$file = $_FILES['file'];
if ($file['size'] > 50 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'File too large (max 50MB)']);
    exit;
}

// Allowed types + extensions — PDF / images / MP4 (all viewable in-browser).
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$extMap = [
    'application/pdf' => 'pdf',
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
    'video/mp4' => 'mp4',
];
if (!isset($extMap[$mime])) {
    echo json_encode(['status' => 'error', 'message' => 'Supported: PDF, JPG, PNG, WebP, GIF, MP4.']);
    exit;
}

// Images must be REAL decodable images (reject fake files with PHP code inside).
if (strpos($mime, 'image/') === 0) {
    $img = @imagecreatefromstring(file_get_contents($file['tmp_name']));
    if ($img === false) {
        echo json_encode(['status' => 'error', 'message' => 'Upload a valid image file.']);
        exit;
    }
    if (imagesx($img) > 12000 || imagesy($img) > 12000) {
        imagedestroy($img);
        echo json_encode(['status' => 'error', 'message' => 'Image dimensions too large']);
        exit;
    }
    imagedestroy($img);
    // Belt & braces: never allow PHP tag bytes inside uploaded images.
    $head = file_get_contents($file['tmp_name'], false, null, 0, 512);
    if (stripos($head, '<?php') !== false || stripos($head, '<?=') !== false) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file content']);
        exit;
    }
}

$uploadDir = dirname(__DIR__) . '/uploads/materials';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$ext = $extMap[$mime];
$filename = 'material_' . $item_id . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
$targetPath = $uploadDir . '/' . $filename;
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Could not save file']);
    exit;
}

$origName = mb_substr(basename($file['name']), 0, 255);
$relativePath = 'uploads/materials/' . $filename;

try {
    $stmt = $conn->prepare("INSERT INTO curriculum_materials (item_id, file_name, file_path, file_type, file_size) VALUES (:i, :n, :p, :t, :s)");
    $stmt->execute([
        'i' => $item_id,
        'n' => $origName,
        'p' => $relativePath,
        't' => $mime,
        's' => $file['size'],
    ]);
    echo json_encode(['status' => 'success', 'id' => $conn->lastInsertId(), 'file_path' => $relativePath, 'file_name' => $origName]);
} catch (PDOException $e) {
    @unlink($targetPath);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
