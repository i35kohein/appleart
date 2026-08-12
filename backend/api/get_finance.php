<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

try {
    $out = [];
    foreach (['assets' => 'finance_assets', 'expenses' => 'finance_expenses', 'shares' => 'finance_shares', 'money_out' => 'finance_money_out'] as $key => $table) {
        $rows = $conn->query("SELECT * FROM $table ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $out[$key] = $rows;
    }
    // Income = student payments (derived, read-only): paid = first + (second once dated).
    $income = $conn->query("
        SELECT p.student_id, s.name AS student_name, p.total_amount, p.first_amount, p.first_paid_at,
               p.second_amount, p.second_paid_at, p.reminder_date, p.note
        FROM student_payments p
        JOIN students s ON s.id = p.student_id
        ORDER BY p.updated_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($income as &$r) {
        $r['paid_amount'] = floatval($r['first_amount']) + ($r['second_paid_at'] ? floatval($r['second_amount']) : 0);
        $r['remaining_amount'] = floatval($r['total_amount']) - $r['paid_amount'];
    }
    unset($r);
    $out['income'] = $income;
    echo json_encode(['status' => 'success', 'data' => $out]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => []]);
}
?>
