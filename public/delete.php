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

        // 1. Fetch the file to get the physical path
        $stmt = $db->prepare("SELECT file_path FROM files WHERE id = ? AND user_id = ?");
        $stmt->execute([$fileId, $userId]);
        $file = $stmt->fetch();

        if ($file) {
            // 2. Delete the record from the database
            if ($fileModel->delete($fileId, $userId)) {
                // 3. Delete the physical file from the server
                if (file_exists($file['file_path'])) {
                    unlink($file['file_path']);
                }
                header("Location: index.php?deleted=1");
                exit;
            }
        }
    } catch (Exception $e) {
        die("Error deleting file: " . $e->getMessage());
    }
}

header("Location: index.php");
exit;
