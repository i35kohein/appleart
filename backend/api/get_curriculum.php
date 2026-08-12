<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

try {
    // Fetch the syllabus, grouped by your custom drag-and-drop sort_order!
    $stmt = $conn->query("SELECT id, type, category, title, tags, practice, details, sort_order FROM curriculum_items ORDER BY sort_order ASC, id ASC");
    $curriculum = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Attach course materials (uploaded files) per lesson.
    $mats = $conn->query("SELECT id, item_id, file_name, file_path, file_type, file_size, uploaded_at FROM curriculum_materials ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $byItem = [];
    foreach ($mats as $m) $byItem[$m['item_id']][] = $m;
    foreach ($curriculum as &$c) {
        $c['materials'] = $byItem[$c['id']] ?? [];
    }
    unset($c);

    echo json_encode(["status" => "success", "data" => $curriculum]);
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
