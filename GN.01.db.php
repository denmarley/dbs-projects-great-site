<?php
// GN.01.db.php - Automatic Environment Detection Database Connection (MySQLi)

if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'bimerp';
} else {
    $host = 'sql203.infinityfree.com';
    $user = 'if0_42942273';
    $pass = 'Da11sar11';
    $dbname = 'if0_42942273_bimerp';
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset("utf8mb4");
    $conn->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_turkish_ci'");

} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Error - DBS ERP</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center vh-100 m-0">
        <div class="card shadow-lg border-0 rounded-4 p-4 text-center" style="max-width: 480px; width: 100%;">
            <div class="card-body">
                <div class="mb-3 text-danger">
                    <i class="fa-solid fa-triangle-exclamation fa-3x"></i>
                </div>
                <h4 class="card-title fw-bold text-dark mb-2">Database Connection Failed</h4>
                <p class="text-muted small mb-4">
                    The system is currently unable to communicate with the database server. Please check your network connection or try again later.
                </p>
                <div class="alert alert-danger text-start small font-monospace p-2 mb-4 overflow-auto" style="max-height: 100px;">
                    <?php echo htmlspecialchars($e->getMessage()); ?>
                </div>
                <button onclick="location.reload();" class="btn btn-dark w-100 py-2 rounded-3 fw-semibold">
                    <i class="fa-solid fa-rotate-right me-2"></i> Try Again
                </button>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>