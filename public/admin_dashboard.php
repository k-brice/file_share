<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/File.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

session_start();
AuthController::requireAdmin();

$db = getDatabaseConnection();
$fileModel = new File($db);
$userModel = new User($db);

$files = $fileModel->getAll();
$stats = $fileModel->getSystemStats();

// Get unique users count
$stmt = $db->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

function formatSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}

require_once __DIR__ . '/../app/views/header.php';
?>

<div style="margin-bottom: 3rem;">
    <h1 style="text-align: left;">Admin Overview</h1>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 2rem;">
        <div class="card" style="padding: 1.5rem; text-align: center;">
            <div style="color: var(--text-muted); font-size: 0.8rem;">TOTAL FILES</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?php echo $stats['total_files']; ?></div>
        </div>
        <div class="card" style="padding: 1.5rem; text-align: center;">
            <div style="color: var(--text-muted); font-size: 0.8rem;">TOTAL STORAGE</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?php echo formatSize($stats['total_size'] ?? 0); ?></div>
        </div>
        <div class="card" style="padding: 1.5rem; text-align: center;">
            <div style="color: var(--text-muted); font-size: 0.8rem;">TOTAL USERS</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?php echo $totalUsers; ?></div>
        </div>
    </div>
</div>

<div class="card">
    <h2 style="margin-bottom: 2rem; color: #fff;">System-Wide File History</h2>
    <table>
        <thead>
            <tr>
                <th>File Name</th>
                <th>Owner ID</th>
                <th>Size</th>
                <th>Status</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($files as $file): ?>
                <tr>
                    <td data-label="File">
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($file['original_name']); ?></div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);"><?php echo $file['stored_name']; ?></div>
                    </td>
                    <td data-label="Owner">#<?php echo $file['user_id'] ?? 'Guest'; ?></td>
                    <td data-label="Size"><?php echo formatSize($file['file_size']); ?></td>
                    <td data-label="Status">
                        <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem;">LIVE</span>
                    </td>
                    <td style="text-align: right;">
                        <form action="delete.php" method="POST" style="display:inline;" onsubmit="return confirm('ADMIN: Are you sure you want to delete this file globally?');">
                            <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                            <button type="submit" class="btn btn-secondary btn-table" style="color: #ef4444; border-color: rgba(239,68,68,0.3);">Terminate</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/views/footer.php'; ?>
