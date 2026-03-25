<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/File.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

session_start();

if (!isset($_GET['file'])) {
    header("Location: index.php");
    exit;
}

$db = getDatabaseConnection();
$fileModel = new File($db);
$file = $fileModel->findByStoredName($_GET['file']);

if (!$file) {
    die("File not found.");
}

function formatSize($bytes) {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}

require_once __DIR__ . '/../app/views/header.php';
?>

<div class="card" style="max-width: 700px; margin: 0 auto; text-align: center;">
    <div style="font-size: 4rem; margin-bottom: 2rem;">📦</div>
    <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($file['original_name']); ?></h1>
    <p style="color: var(--text-muted); margin-bottom: 2rem;">Ready for Secure Download</p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 3rem;">
        <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border);">
            <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.5rem;">FILE SIZE</div>
            <div style="font-size: 1.2rem; font-weight: 600;"><?php echo formatSize($file['file_size']); ?></div>
        </div>
        <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border);">
            <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.5rem;">FILE TYPE</div>
            <div style="font-size: 1.2rem; font-weight: 600; text-transform: uppercase;">
                <?php echo pathinfo($file['original_name'], PATHINFO_EXTENSION); ?>
            </div>
        </div>
    </div>

    <a href="download.php?file=<?php echo urlencode($file['stored_name']); ?>" class="btn btn-primary" style="width: 100%; padding: 1.2rem; font-size: 1.1rem; margin-bottom: 1.5rem;">
        Download Securely
    </a>

    <div class="form-group">
        <label>Share this link</label>
        <input type="text" value="<?php echo (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>" 
               class="btn btn-secondary" style="width: 100%; color: var(--text-main); font-size: 0.8rem; padding: 1rem; background: rgba(255,255,255,0.05); text-align: center;" readonly>
    </div>
</div>

<?php require_once __DIR__ . '/../app/views/footer.php'; ?>
