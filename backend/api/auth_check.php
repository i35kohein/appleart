<?php
// Admin-only API guard — all management endpoints require a logged-in admin.
// Student portal endpoints (student_*) keep their own session handling.

function require_admin(): bool
{
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        return false;
    }
    return true;
}
