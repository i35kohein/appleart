<?php
error_reporting(E_ERROR | E_PARSE); // never let notices pollute binary output
require_once '../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

// FPDF has no built-in rotation — small subclass exposing a rotate/restore pair.
class WmFpdi extends Fpdi
{
    public function wmRotate($angle, $cx, $cy)
    {
        $rad = $angle * M_PI / 180;
        $c = cos($rad);
        $s = sin($rad);
        $this->_out(sprintf(
            'q %.5F %.5F %.5F %.5F %.5F %.5F cm',
            $c, $s, -$s, $c,
            $cx - $cx * $c + $cy * $s,
            $cy - $cx * $s - $cy * $c
        ));
    }
    public function wmRestore()
    {
        $this->_out('Q');
    }
}

// ============================================================
// Protected material serving with SERVER-SIDE watermark burning.
// - Requires a logged-in session: admin (user_id) or student (student_id).
// - Raw files live OUTSIDE the docroot (../appleart-private/materials) — no
//   direct URL access. Only this endpoint can read them.
// - PDFs are re-rendered with the student's name/email/phone burned INTO the
//   file; images get a GD-drawn watermark; video is streamed (client overlay).
// - Even if a file leaks, it carries the full watermark.
// ============================================================

$mid = intval($_GET['mid'] ?? 0);
if ($mid <= 0) { http_response_code(400); exit('Bad request'); }

// --- Auth: admin OR student session -------------------------
$isAdmin = !empty($_SESSION['user_id']);
$studentId = $_SESSION['student_id'] ?? null;
if (!$isAdmin && empty($studentId)) {
    http_response_code(403);
    exit('Forbidden');
}

try {
    $stmt = $conn->prepare("SELECT id, item_id, file_name, file_path, file_type, file_size FROM curriculum_materials WHERE id = :mid");
    $stmt->execute(['mid' => $mid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { http_response_code(404); exit('Not found'); }

    // Private storage OUTSIDE the web root — direct URL access impossible.
    $privateDir = dirname(__DIR__, 2) . '/appleart-private/materials';
    if (!is_dir($privateDir)) $privateDir = '/Users/user/appleart-private/materials';
    $file = $privateDir . '/' . basename($row['file_path']);
    if (!is_file($file)) { http_response_code(404); exit('File missing'); }

    // --- Watermark identity (students only; admins see clean originals) --
    $wmText = null;
    if (!$isAdmin && $studentId) {
        $s = $conn->prepare("SELECT name, email, phone FROM students WHERE id = :id");
        $s->execute(['id' => intval($studentId)]);
        $stu = $s->fetch(PDO::FETCH_ASSOC);
        if ($stu) {
            $wmText = implode('  |  ', array_filter([$stu['name'], $stu['email'] ?? '', $stu['phone'] ?? '']));
        }
    }

    $mime = $row['file_type'] ?: 'application/octet-stream';
    $isPdf = $mime === 'application/pdf';
    $isImage = strpos($mime, 'image/') === 0;

    // --- PDF: burn watermark with FPDI+FPDF -------------------
    if ($isPdf) {
        $pdf = new WmFpdi();
        $pdf->SetCompression(false); // keep watermark text extractable in raw bytes
        $pageCount = $pdf->setSourceFile($file);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
            if ($wmText) {
                $W = $size['width'];
                $H = $size['height'];
                $pdf->SetFont('Helvetica', 'B', max(8, round($W / 38)));
                $pdf->SetTextColor(170, 170, 170);
                $pdf->SetAutoPageBreak(false); // grid rows are drawn off-page; no new pages
                $pdf->wmRotate(-28, $W / 2, $H / 2);
                $stepX = max(500, $W * 2.6);
                $stepY = max(330, $H * 1.8);
                for ($ty = -$H; $ty <= 2 * $H; $ty += $stepY) {
                    for ($tx = -$W; $tx <= 2 * $W; $tx += $stepX) {
                        $pdf->SetXY($tx, $ty);
                        $pdf->Cell(0, 0, $wmText, 0, 0, 'L', false);
                    }
                }
                $pdf->wmRestore();
            }
        }
        $disp = isset($_GET['dl']) && $_GET['dl'] === '1' ? 'attachment' : 'inline';
        $out = $pdf->Output('', 'S'); // capture — FPDF's own Output('I') would override our headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . $disp . '; filename="' . basename($row['file_name']) . '"');
        header('Content-Length: ' . strlen($out));
        header('Cache-Control: no-store, private');
        echo $out;
        exit;
    }

    // --- Image: burn watermark with GD ------------------------
    if ($isImage) {
        $img = @imagecreatefromstring(file_get_contents($file));
        if ($img && $wmText) {
            $w = imagesx($img);
            $h = imagesy($img);
            $font = '/System/Library/Fonts/Supplemental/Arial.ttf';
            $size = max(14, round(min($w, $h) / 22));
            $color = imagecolorallocatealpha($img, 255, 255, 255, 70);
            $b = imagettfbbox($size, 0, $font, $wmText);
            $tw = $b[2] - $b[0];
            $th = $b[1] - $b[7];
            for ($ty = -$h; $ty < 2 * $h; $ty += max(60, $th * 2.2)) {
                for ($tx = -$w; $tx < 2 * $w; $tx += max(120, $tw * 1.6)) {
                    imagettftext($img, $size, -25, (int)$tx, (int)$ty, $color, $font, $wmText);
                }
            }
            header('Content-Type: ' . $mime);
            header('Cache-Control: no-store, private');
            $mime === 'image/png' ? imagepng($img) : ($mime === 'image/webp' ? imagewebp($img) : imagejpeg($img));
            imagedestroy($img);
            exit;
        }
    }

    // --- Video / other: stream (session-protected) ------------
    $size = filesize($file);
    $start = 0;
    $end = $size - 1;
    if (isset($_SERVER['HTTP_RANGE'])) {
        if (preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
            if ($m[1] !== '') $start = intval($m[1]);
            if ($m[2] !== '') $end = min(intval($m[2]), $end);
            http_response_code(206);
            header("Content-Range: bytes $start-$end/$size");
        }
    }
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . ($end - $start + 1));
    header('Accept-Ranges: bytes');
    header('Cache-Control: no-store, private');
    $fh = fopen($file, 'rb');
    if ($start > 0) fseek($fh, $start);
    $chunk = 512 * 1024;
    while (!feof($fh) && ($p = ftell($fh)) <= $end) {
        $len = min($chunk, $end - $p + 1);
        echo fread($fh, $len);
        flush();
    }
    fclose($fh);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    error_log('serve_material: ' . $e->getMessage());
    exit('Server error: ' . $e->getMessage());
}
?>
