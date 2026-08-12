<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;

header('Content-Type: application/json');
try {
    $stmt = $conn->query("SELECT * FROM trainers ORDER BY name ASC");
    echo json_encode(["status" => "success", "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch(PDOException $e) { echo json_encode(["status" => "error"]); }
?>