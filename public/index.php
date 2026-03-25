<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/File.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

session_start();

$db = getDatabaseConnection();
$fileModel = new File($db);

// If logged in, show history. If not, show public files 
$userId = $_SESSION['user_id'] ?? null;
$files = $userId ? $fileModel->getByUserId($userId) : [];

// Fallback: If absolutely no files are found for the user, show ALL files (for easier debugging/visibility)
if (empty($files)) {
    $files = $fileModel->getAll();
}

function formatSize($bytes) {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}

require_once __DIR__ . '/../app/views/header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; color: #fff;">
            <?php echo $userId ? "My Upload History" : "Public Files"; ?>
        </h2>
        <span style="color: var(--text-muted); font-size: 0.8rem;"><?php echo count($files); ?> items</span>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success" style="margin-bottom: 2rem;">File deleted successfully.</div>
    <?php endif; ?>

    <?php if (!$userId && empty($files)): ?>
        <div style="text-align: center; padding: 4rem;">
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Login to see your upload history and secure your files.</p>
            <a href="login.php" class="btn btn-primary">Sign In Now</a>
        </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>File Name</th>
                <th>Size</th>
                <th>Upload Date</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($files)): ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                        No uploads found. Start by adding a file!
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($files as $file): ?>
                    <tr>
                        <td data-label="File Name" style="font-weight: 500; font-family: 'Inter';">
                            <a href="share.php?file=<?php echo urlencode($file['stored_name']); ?>" style="color: var(--text-main); text-decoration: none;">
                                <?php echo htmlspecialchars($file['original_name']); ?>
                            </a>
                        </td>
                        <td data-label="Size" style="color: var(--text-muted);"><?php echo formatSize($file['file_size']); ?></td>
                        <td data-label="Upload Date" style="color: var(--text-muted);"><?php echo date('M d, Y', strtotime($file['upload_date'])); ?></td>
                        <td style="text-align: right; display: flex; justify-content: flex-end; gap: 8px; align-items: center;">
                            <a href="share.php?file=<?php echo urlencode($file['stored_name']); ?>" class="btn btn-secondary btn-table">Share Link</a>
                            <a href="download.php?file=<?php echo urlencode($file['stored_name']); ?>" class="btn btn-primary btn-table" style="color: #000;">Download</a>
                            
                            <?php if ($userId && $file['user_id'] == $userId): ?>
                                <form action="delete.php" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                    <button type="submit" class="btn btn-secondary btn-table" style="color: #ef4444; border-color: rgba(239,68,68,0.3);">Remove</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../app/views/footer.php'; ?>
