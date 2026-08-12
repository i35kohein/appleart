<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

$id = $_POST['id'] ?? ''; $type = $_POST['type'] ?? ''; $category = $_POST['category'] ?? ''; $title = $_POST['title'] ?? '';
$tagsRaw = $_POST['tags'] ?? ''; $practice = $_POST['practice'] ?? ''; $details = $_POST['details'] ?? '';
// Normalize tags: split on spaces/commas, lowercase, trim, dedupe, cap at 8.
$tagArr = array_values(array_unique(array_filter(array_map(
    fn($t) => strtolower(trim($t)),
    preg_split('/[\s,]+/', $tagsRaw)
), fn($t) => $t !== '')));
$tags = implode(' ', array_slice($tagArr, 0, 8));

if(!empty($title) && !empty($category)) {
    if(!empty($id)) {
        $stmt = $conn->prepare("UPDATE curriculum_items SET type=?, category=?, title=?, tags=?, practice=?, details=? WHERE id=?");
        $stmt->execute([$type, $category, $title, $tags, $practice, $details, $id]);
        $return_id = $id;
    } else {
        $stmt = $conn->prepare("INSERT INTO curriculum_items (type, category, title, tags, practice, details) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$type, $category, $title, $tags, $practice, $details]);
        $return_id = $conn->lastInsertId();
    }
    echo json_encode(['status' => 'success', 'id' => $return_id]);
} else { echo json_encode(['status' => 'error']); }
?>
