<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/File.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

session_start();

if (!isset($_GET['file'])) {
    die("No file specified.");
}

$storedName = $_GET['file'];
$db = getDatabaseConnection();
$fileModel = new File($db);

$file = $fileModel->findByStoredName($storedName);

if (!$file) {
    die("File not found in database.");
}

// Security Check: Absolute path must exist
// In a real EC2 environment, you might use a specific absolute path prefix
$filePath = $file['file_path'];

if (file_exists($filePath)) {
    // Increment download count or log if needed
    
    // Set headers to force download (Secure: real path is hidden from URL)
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $file['file_type']); // Use real mime type from DB
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . $file['file_size']);
    
    // Clear buffer and stream file
    ob_clean();
    flush();
    readfile($filePath);
    exit;
} else {
    die("File missing on server storage.");
}
