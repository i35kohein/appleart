<?php
require_once '../config.php';
require_once __DIR__ . '/auth_check.php';
if (!require_admin()) exit;
 
header('Content-Type: application/json');

try {
    // Fetch all student info, dates, and calculate progress in one query.
    // Default: only ACTIVE on-site students appear on the platform (no clutter from
    // inactive or ONLINE-role students). Admin can pass ?all=1 to see everyone.
    $filter = "";
    if (($_GET['all'] ?? '') !== '1') {
        $filter = " WHERE s.role = 'student' AND s.is_active = 1";
    }
    $query = "
        SELECT 
            s.id, s.name, s.phone, s.email, s.shop_name, s.address, s.photo_path, s.is_active, s.role, s.rollcall_group, s.created_at,
            (SELECT COUNT(*) FROM student_progress sp JOIN curriculum_items ci ON sp.item_id = ci.id WHERE sp.student_id = s.id AND sp.status = 'Completed' AND ci.type = 'Course') as course_completed,
            (SELECT COUNT(*) FROM curriculum_items WHERE type = 'Course') as total_course,
            (SELECT COUNT(*) FROM student_progress sp JOIN curriculum_items ci ON sp.item_id = ci.id WHERE sp.student_id = s.id AND sp.status = 'Completed' AND ci.type = 'Practical') as practical_completed,
            (SELECT COUNT(*) FROM curriculum_items WHERE type = 'Practical') as total_practical
        FROM students s 
        $filter
        ORDER BY s.id DESC
    ";
    
    $stmt = $conn->query($query);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "data" => $students]);
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
