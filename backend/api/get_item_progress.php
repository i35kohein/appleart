<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');

$item_id = $_GET['item_id'] ?? '';

if (!empty($item_id)) {
    try {
        $stmt = $conn->prepare("
            SELECT s.id, s.name, s.phone, s.shop_name, sp.completion_date, sp.trainer_name
            FROM student_progress sp
            JOIN students s ON sp.student_id = s.id
            WHERE sp.item_id = :item_id AND sp.status = 'Completed' AND sp.detail_idx IS NULL
            ORDER BY s.name ASC
        ");
        $stmt->execute(['item_id' => $item_id]);

        echo json_encode(["status" => "success", "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing item ID."]);
}
?>
