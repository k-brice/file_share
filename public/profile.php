<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/File.php';
require_once __DIR__ . '/../app/models/User.php';

session_start();
AuthController::requireLogin();

$db = getDatabaseConnection();
$userModel = new User($db);
$fileModel = new File($db);

$user = $userModel->findById($_SESSION['user_id']);
$fileCount = $fileModel->getCountByUserId($user['id']);

require_once __DIR__ . '/../app/views/header.php';
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; align-items: center; gap: 30px; margin-bottom: 4rem;">
        <div style="width: 100px; height: 100px; background: var(--gold-gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #000; font-weight: 800;">
            <?php echo strtoupper($user['name'][0]); ?>
        </div>
        <div>
            <h1 style="text-align: left; margin: 0;"><?php echo htmlspecialchars($user['name']); ?></h1>
            <p style="color: var(--text-muted);"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <div class="btn btn-secondary" style="padding: 2rem; text-align: center; pointer-events: none;">
            <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.5rem;">TOTAL UPLOADS</div>
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary);"><?php echo $fileCount; ?></div>
        </div>
        <div class="btn btn-secondary" style="padding: 2rem; text-align: center; pointer-events: none;">
            <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.5rem;">ACCOUNT SINCE</div>
            <div style="font-size: 1.2rem; font-weight: 600; color: #fff; margin-top: 1rem;">
                <?php echo date('F Y', strtotime($user['created_at'])); ?>
            </div>
        </div>
    </div>

    <div style="margin-top: 4rem; border-top: 1px solid var(--border); padding-top: 2rem;">
        <h3>System Requirements & Limits</h3>
        <ul style="color: var(--text-muted); margin-top: 1rem; line-height: 2;">
            <li>Maximum File Size: <strong>5MB</strong></li>
            <li>Allowed Extensions: <strong>JPG, PNG, PDF, DOCX</strong></li>
            <li>Storage Location: <strong>Private Space (storage/uploads)</strong></li>
            <li>Security: <strong>PDO Prepared Statements & Secure Downloads</strong></li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../app/views/footer.php'; ?>
