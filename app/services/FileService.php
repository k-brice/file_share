<?php
/**
 * File Service
 * Handles file validation, renaming, and server storage.
 */

class FileService {
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    private $maxSize = 5 * 1024 * 1024; // 5MB

    public function __construct($uploadDir) {
        $this->uploadDir = rtrim(str_replace('\\', '/', $uploadDir), '/') . '/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    /**
     * Validate and Upload file
     */
    public function handleUpload($file) {
        // 1. Basic error check
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload error code: " . $file['error']);
        }

        // 2. Validate Size
        if ($file['size'] > $this->maxSize) {
            throw new Exception("File is too large. Maximum size is 5MB.");
        }

        // 3. Validate Type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new Exception("Invalid file type. Only JPG, PNG, PDF, and DOCX are allowed.");
        }

        // 4. Generate Unique Name
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = $this->uploadDir . $storedName;

        // 5. Move to storage
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Failed to move uploaded file to destination.");
        }

        return [
            'original_name' => $file['name'],
            'stored_name'   => $storedName,
            'file_path'     => $targetPath,
            'file_size'     => $file['size'],
            'file_type'     => $mimeType
        ];
    }
}
