<?php
require_once '../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'] ?? '';

    // Student portal: a logged-in student may only update their OWN progress.
    // Admins (user session) are exempt — they manage every student.
    if (empty($_SESSION['user_id']) && !empty($_SESSION['student_id']) && intval($student_id) !== intval($_SESSION['student_id'])) {
        echo json_encode(["status" => "error", "message" => "You can only update your own progress."]);
        exit;
    }
    $item_id = $_POST['item_id'] ?? '';
    $status = $_POST['status'] ?? 'Completed';
    $comment = $_POST['comment'] ?? '';
    $trainer_name = $_POST['trainer_name'] ?? 'Instructor';
    // Optional: detail step index (1-based). NULL = whole-lesson mark.
    $detail_raw = $_POST['detail_idx'] ?? '';
    $detail_idx = ($detail_raw !== '' && $detail_raw !== null) ? intval($detail_raw) : null;

    // Time Machine support: optional completion_date (YYYY-MM-DD); defaults to now
    $completion_date = trim($_POST['completion_date'] ?? '');
    if ($completion_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $completion_date)) {
        echo json_encode(["status" => "error", "message" => "Invalid completion_date (use YYYY-MM-DD)"]);
        exit;
    }
    $date = $completion_date !== '' ? $completion_date . ' 12:00:00' : date('Y-m-d H:i:s');

    if (empty($student_id) || empty($item_id)) {
        echo json_encode(["status" => "error", "message" => "Missing IDs."]);
        exit;
    }

    try {
        $conn->beginTransaction();

        // Unmarking a detail step removes its row entirely.
        if ($detail_idx !== null && $status === 'Pending') {
            $stmt = $conn->prepare("DELETE FROM student_progress WHERE student_id = :sid AND item_id = :iid AND detail_idx = :di");
            $stmt->execute(['sid' => $student_id, 'iid' => $item_id, 'di' => $detail_idx]);
            $conn->commit();
            echo json_encode(["status" => "success", "message" => "Step unmarked."]);
            exit;
        }

        if ($detail_idx !== null) {
            // Upsert a detail-step row (status forced to Completed).
            $check = $conn->prepare("SELECT id FROM student_progress WHERE student_id = :sid AND item_id = :iid AND detail_idx = :di");
            $check->execute(['sid' => $student_id, 'iid' => $item_id, 'di' => $detail_idx]);
            if ($check->rowCount() > 0) {
                $stmt = $conn->prepare("UPDATE student_progress SET status = 'Completed', completion_date = :date, trainer_name = :trainer WHERE student_id = :sid AND item_id = :iid AND detail_idx = :di");
            } else {
                $stmt = $conn->prepare("INSERT INTO student_progress (student_id, item_id, detail_idx, status, completion_date, trainer_name) VALUES (:sid, :iid, :di, 'Completed', :date, :trainer)");
            }
            $stmt->execute(['sid' => $student_id, 'iid' => $item_id, 'di' => $detail_idx, 'date' => $date, 'trainer' => $trainer_name]);

            $hist_stmt = $conn->prepare("INSERT INTO progress_history (student_id, item_id, status, comment, trainer_name, created_at) VALUES (:sid, :iid, :status, :comment, :trainer, :date)");
            $hist_stmt->execute(['sid' => $student_id, 'iid' => $item_id, 'status' => 'Completed', 'comment' => $comment, 'trainer' => $trainer_name, 'date' => $date]);

            $conn->commit();
            echo json_encode(["status" => "success", "message" => "Step updated."]);
            exit;
        }

        // Whole-lesson upsert (existing behavior).
        $check = $conn->prepare("SELECT id FROM student_progress WHERE student_id = :sid AND item_id = :iid AND detail_idx IS NULL");
        $check->execute(['sid' => $student_id, 'iid' => $item_id]);

        if ($check->rowCount() > 0) {
            $stmt = $conn->prepare("UPDATE student_progress SET status = :status, completion_date = :date, trainer_name = :trainer WHERE student_id = :sid AND item_id = :iid AND detail_idx IS NULL");
        } else {
            $stmt = $conn->prepare("INSERT INTO student_progress (student_id, item_id, status, completion_date, trainer_name) VALUES (:sid, :iid, :status, :date, :trainer)");
        }
        $stmt->execute(['sid' => $student_id, 'iid' => $item_id, 'status' => $status, 'date' => $date, 'trainer' => $trainer_name]);

        $hist_stmt = $conn->prepare("INSERT INTO progress_history (student_id, item_id, status, comment, trainer_name, created_at) VALUES (:sid, :iid, :status, :comment, :trainer, :date)");
        $hist_stmt->execute(['sid' => $student_id, 'iid' => $item_id, 'status' => $status, 'comment' => $comment, 'trainer' => $trainer_name, 'date' => $date]);

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Progress updated."]);
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>
