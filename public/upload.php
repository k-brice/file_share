<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/File.php';
require_once __DIR__ . '/../app/services/FileService.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

session_start();
AuthController::requireLogin();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        $db = getDatabaseConnection();
        $fileModel = new File($db);
        $uploadDir = __DIR__ . '/../storage/uploads';
        $fileService = new FileService($uploadDir);

        $fileData = $fileService->handleUpload($_FILES['file']);
        
        if ($fileModel->create(
            $fileData['original_name'], 
            $fileData['stored_name'], 
            $fileData['file_path'], 
            $fileData['file_size'], 
            $fileData['file_type'],
            $_SESSION['user_id']
        )) {
            $message = "File uploaded successfully!";
            $messageType = "success";
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "error";
    }
}

require_once __DIR__ . '/../app/views/header.php';
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h1>Upload Asset</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Select Document (Max 5MB)</label>
            <input type="file" name="file" id="file" required>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; text-align: center;">Allowed: JPG, PNG, PDF, DOCX</p>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Confirm Upload</button>
    </form>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="index.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.8rem;">← Go back to Dashboard</a>
    </div>
</div>

<?php require_once __DIR__ . '/../app/views/footer.php'; ?>
