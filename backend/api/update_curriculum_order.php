<?php
require_once '../config.php'; // Ensure this points to your DB connection
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_data'])) {
    $orderData = json_decode($_POST['order_data'], true);

    try {
        $conn->beginTransaction();
        
        // Prepare the statement to update rank, category, and type
        $stmt = $conn->prepare("UPDATE curriculum_items SET sort_order = ?, category = ?, type = ? WHERE id = ?");
        
        foreach ($orderData as $item) {
            $stmt->execute([
                $item['sort_order'], 
                $item['category'], 
                $item['type'], 
                $item['id']
            ]);
        }
        
        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
}
?>