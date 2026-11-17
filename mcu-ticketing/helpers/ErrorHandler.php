<?php

/**
 * Global handler for uncaught exceptions and fatal errors.
 * Users see a readable alert; technical details go to the PHP error log, tagged with an error ID.
 */
class ErrorHandler {
    private static $handled = false;

    public static function register() {
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        error_reporting(E_ALL);

        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleException($e) {
        self::render(
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
    }

    public static function handleShutdown() {
        $error = error_get_last();
        $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if ($error && in_array($error['type'], $fatal, true)) {
            self::render('FatalError', $error['message'], $error['file'], $error['line'], '');
        }
    }

    private static function render($type, $message, $file, $line, $trace) {
        if (self::$handled) return;
        self::$handled = true;

        $errorId = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $page = $_GET['page'] ?? '-';
        $user = $_SESSION['user_id'] ?? '-';
        error_log("[ERR-$errorId] page=$page user=$user $type: $message in $file:$line\n$trace");

        $userMessage = self::friendlyMessage($type, $message);

        while (ob_get_level()) ob_end_clean();

        if (!headers_sent()) {
            http_response_code(500);
        }

        if (self::expectsJson()) {
            if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'status' => 'error',
                'message' => $userMessage . " (Kode: ERR-$errorId)",
                'error_id' => "ERR-$errorId",
            ]);
            return;
        }

        if (!headers_sent()) header('Content-Type: text/html; charset=utf-8');

        $isSuperadmin = ($_SESSION['role'] ?? '') === 'superadmin';
        $detail = $isSuperadmin ? "$type: $message\n" . basename($file) . ":$line" : '';

        self::renderPage($userMessage, "ERR-$errorId", $detail);
    }

    private static function friendlyMessage($type, $message) {
        if (stripos($message, 'Unsupported operand types') !== false
            || stripos($message, 'non-numeric') !== false
            || stripos($message, 'must be of type int') !== false
            || stripos($message, 'must be of type float') !== false) {
            return 'Ada kolom angka yang kosong atau isinya bukan angka. Periksa kembali isian nominal, jumlah, dan hari, lalu coba simpan lagi.';
        }
        if (stripos($type, 'PDOException') !== false || stripos($message, 'SQLSTATE') !== false) {
            if (stripos($message, 'Duplicate entry') !== false) {
                return 'Data yang sama sudah pernah tersimpan. Periksa kembali data Anda.';
            }
            if (stripos($message, 'cannot be null') !== false || stripos($message, "doesn't have a default value") !== false) {
                return 'Ada kolom wajib yang belum diisi. Lengkapi formulir lalu coba simpan lagi.';
            }
            return 'Data gagal disimpan ke database. Coba lagi beberapa saat. Jika masih gagal, hubungi tim IT.';
        }
        if (stripos($message, 'Allowed memory size') !== false || stripos($message, 'Maximum execution time') !== false) {
            return 'Proses terlalu berat atau terlalu lama. Coba dengan data yang lebih sedikit, atau hubungi tim IT.';
        }
        return 'Terjadi kesalahan saat memproses permintaan Anda. Coba lagi, dan jika masih terjadi, hubungi tim IT.';
    }

    private static function expectsJson() {
        $xhr = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $accept = stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
        $page = $_GET['page'] ?? '';
        $ajaxPage = (bool) preg_match('/(_ajax|^api_)/', $page);
        return $xhr || $accept || $ajaxPage;
    }

    private static function renderPage($message, $errorId, $detail) {
        $m = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $id = htmlspecialchars($errorId, ENT_QUOTES, 'UTF-8');
        $d = htmlspecialchars($detail, ENT_QUOTES, 'UTF-8');
        $jsMessage = json_encode($message);
        $jsId = json_encode($errorId);
        $jsDetail = json_encode($detail);
        echo <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Terjadi Kesalahan</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  body { font-family: system-ui, -apple-system, "Segoe UI", sans-serif; background: #f4f6f9; color: #1f2937; margin: 0; padding: 16px; }
  .box { max-width: 520px; margin: 10vh auto; background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
  h1 { font-size: 20px; margin: 0 0 12px; color: #b91c1c; }
  p { line-height: 1.5; }
  code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
  pre { background: #f3f4f6; padding: 10px; border-radius: 6px; white-space: pre-wrap; font-size: 12px; }
  .actions a, .actions button { display: inline-block; margin-right: 8px; padding: 8px 14px; border-radius: 6px; border: 0; cursor: pointer; text-decoration: none; font-size: 14px; }
  .primary { background: #2563eb; color: #fff; }
  .secondary { background: #e5e7eb; color: #111827; }
</style>
</head>
<body>
<div class="box">
  <h1>Gagal memproses permintaan</h1>
  <p>{$m}</p>
  <p>Kode error: <code>{$id}</code><br><small>Sertakan kode ini saat melapor ke tim IT.</small></p>
  <pre style="display:none" id="detail">{$d}</pre>
  <div class="actions">
    <button class="primary" onclick="history.back()">Kembali ke formulir</button>
    <a class="secondary" href="index.php?page=dashboard">Ke Dashboard</a>
  </div>
</div>
<script>
  if ({$jsDetail}) document.getElementById('detail').style.display = 'block';
  if (window.Swal) {
    Swal.fire({
      icon: 'error',
      title: 'Gagal memproses permintaan',
      html: {$jsMessage}.replace(/[&<>"']/g, function (c) { return '&#' + c.charCodeAt(0) + ';'; })
        + '<br><br><small>Kode error: <b>' + {$jsId} + '</b></small>',
      confirmButtonText: 'Kembali ke formulir',
      showCancelButton: true,
      cancelButtonText: 'Tutup'
    }).then(function (r) { if (r.isConfirmed) history.back(); });
  }
</script>
</body>
</html>
HTML;
    }
}
