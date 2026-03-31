<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/File.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

session_start();
AuthController::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id'])) {
    try {
        $db = getDatabaseConnection();
        $fileModel = new File($db);
        $userId = $_SESSION['user_id'];
        $fileId = $_POST['file_id'];

        // 1. Fetch file info
        $stmt = $db->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch();

        if ($file) {
            // Security: Must be owner OR Admin
            $isAdmin = AuthController::isAdmin();
            $isOwner = ($file['user_id'] == $userId);

            if ($isOwner || $isAdmin) {
                if ($fileModel->deleteGlobal($fileId)) {
                    if (file_exists($file['file_path'])) {
                        unlink($file['file_path']);
                    }
                    $redirect = $isAdmin ? "admin_dashboard.php?deleted=1" : "index.php?deleted=1";
                    header("Location: $redirect");
                    exit;
                }
            }
        }
    } catch (Exception $e) {
        die("Error deleting file: " . $e->getMessage());
    }
}

header("Location: index.php");
exit;
