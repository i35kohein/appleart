<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST only']);
    exit;
}

$action = $_POST['action'] ?? 'add';
$type = $_POST['type'] ?? ''; // assets | expenses | shares | money_out

$tables = [
    'assets' => 'finance_assets',
    'expenses' => 'finance_expenses',
    'shares' => 'finance_shares',
    'money_out' => 'finance_money_out',
];
if (!isset($tables[$type])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid type']);
    exit;
}
$table = $tables[$type];

try {
    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['status' => 'error', 'message' => 'Missing id']); exit; }
        $stmt = $conn->prepare("DELETE FROM $table WHERE id = :id");
        $stmt->execute(['id' => $id]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    $date = trim($_POST['entry_date'] ?? $_POST['expense_date'] ?? $_POST['out_date'] ?? '');
    if ($date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid date (YYYY-MM-DD)']);
        exit;
    }
    if ($date === '') $date = date('Y-m-d');

    switch ($type) {
        case 'assets':
            $name = trim($_POST['name'] ?? '');
            $value = intval($_POST['value_amount'] ?? 0);
            $note = mb_substr(trim($_POST['note'] ?? ''), 0, 255);
            if ($name === '') { echo json_encode(['status' => 'error', 'message' => 'Name required']); exit; }
            $stmt = $conn->prepare("INSERT INTO $table (name, value_amount, entry_date, note) VALUES (:a, :b, :c, :d)");
            $stmt->execute(['a' => $name, 'b' => $value, 'c' => $date, 'd' => $note]);
            break;
        case 'expenses':
            $title = trim($_POST['title'] ?? '');
            $amount = intval($_POST['amount'] ?? 0);
            $category = mb_substr(trim($_POST['category'] ?? ''), 0, 100);
            $note = mb_substr(trim($_POST['note'] ?? ''), 0, 255);
            if ($title === '') { echo json_encode(['status' => 'error', 'message' => 'Title required']); exit; }
            $stmt = $conn->prepare("INSERT INTO $table (title, amount, expense_date, category, note) VALUES (:a, :b, :c, :d, :e)");
            $stmt->execute(['a' => $title, 'b' => $amount, 'c' => $date, 'd' => $category, 'e' => $note]);
            break;
        case 'shares':
            $partner = trim($_POST['partner_name'] ?? '');
            $percent = floatval($_POST['share_percent'] ?? 0);
            $note = mb_substr(trim($_POST['note'] ?? ''), 0, 255);
            if ($partner === '') { echo json_encode(['status' => 'error', 'message' => 'Partner name required']); exit; }
            $stmt = $conn->prepare("INSERT INTO $table (partner_name, share_percent, note) VALUES (:a, :b, :c)");
            $stmt->execute(['a' => $partner, 'b' => $percent, 'c' => $note]);
            break;
        case 'money_out':
            $amount = intval($_POST['amount'] ?? 0);
            $reason = mb_substr(trim($_POST['reason'] ?? ''), 0, 150);
            $note = mb_substr(trim($_POST['note'] ?? ''), 0, 255);
            $stmt = $conn->prepare("INSERT INTO $table (amount, out_date, reason, note) VALUES (:a, :b, :c, :d)");
            $stmt->execute(['a' => $amount, 'b' => $date, 'c' => $reason, 'd' => $note]);
            break;
    }
    echo json_encode(['status' => 'success', 'id' => $conn->lastInsertId()]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
