<?php
/**
 * File Model
 * Handles database operations for files.
 */

class File {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Create a new file record linked to a user
     */
    public function create($originalName, $storedName, $filePath, $fileSize, $fileType, $userId = null) {
        $sql = "INSERT INTO files (user_id, original_name, stored_name, file_path, file_size, file_type) 
                VALUES (:user_id, :original_name, :stored_name, :file_path, :file_size, :file_type)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id'       => $userId,
            ':original_name' => $originalName,
            ':stored_name'   => $storedName,
            ':file_path'     => $filePath,
            ':file_size'     => $fileSize,
            ':file_type'     => $fileType
        ]);
    }

    /**
     * Get all files (global history)
     */
    public function getAll() {
        $sql = "SELECT * FROM files ORDER BY upload_date DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get files for a specific user
     */
    public function getByUserId($userId) {
        $sql = "SELECT * FROM files WHERE user_id = :user_id ORDER BY upload_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get count for profile
     */
    public function getCountByUserId($userId) {
        $sql = "SELECT COUNT(*) FROM files WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    /**
     * Find file by its stored name (ID) for secure download
     */
    public function findByStoredName($storedName) {
        $sql = "SELECT * FROM files WHERE stored_name = :stored_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':stored_name' => $storedName]);
        return $stmt->fetch();
    }

    /**
     * Delete a file record by ID and user_id (for security)
     */
    /**
     * Delete a file record globally (for Admin)
     */
    public function deleteGlobal($id) {
        $sql = "DELETE FROM files WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get system-wide stats
     */
    public function getSystemStats() {
        $stats = [];
        $stats['total_files'] = $this->db->query("SELECT COUNT(*) FROM files")->fetchColumn();
        $stats['total_size'] = $this->db->query("SELECT SUM(file_size) FROM files")->fetchColumn();
        return $stats;
    }
}
