<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/File.php';
require_once __DIR__ . '/../app/services/FileService.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

session_start();
AuthController::requireLogin();

// AJAX Upload Handler (JSON response)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        $db = getDatabaseConnection();
        $fileModel = new File($db);
        $uploadDir = __DIR__ . '/../storage/uploads';
        $fileService = new FileService($uploadDir);

        $fileData = $fileService->handleUpload($_FILES['file']);
        
        $fileModel->create(
            $fileData['original_name'], 
            $fileData['stored_name'], 
            $fileData['file_path'], 
            $fileData['file_size'], 
            $fileData['file_type'],
            $_SESSION['user_id']
        );

        echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully!']);
        exit;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

require_once __DIR__ . '/../app/views/header.php';
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h1>Secure Upload</h1>
    <p style="color: var(--text-muted); text-align: center; margin-bottom: 2.5rem;">Upload high-value assets to your encrypted vault.</p>

    <div class="drop-zone" id="dropZone">
        <span style="font-size: 3rem; margin-bottom: 1rem;">📤</span>
        <p>Drag & drop your files here or <span style="color: var(--primary); font-weight: 600;">Browse</span></p>
        <span style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1rem;">MAX 5MB (JPG, PNG, PDF, DOCX)</span>
        <input type="file" name="file" id="fileInput">
    </div>

    <div class="progress-container" id="progressContainer">
        <div class="progress-bar" id="progressBar"></div>
    </div>
    <div class="status-text" id="statusText">Uploading... 0%</div>

    <div id="messageBox" style="margin-top: 1.5rem;"></div>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="index.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.8rem;">← Return to Assets</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const progressBar = document.getElementById('progressBar');
    const progressContainer = document.getElementById('progressContainer');
    const statusText = document.getElementById('statusText');
    const messageBox = document.getElementById('messageBox');

    // Click to browse
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag events
    ['dragenter', 'dragover'].forEach(e => {
        dropZone.addEventListener(e, (ev) => {
            ev.preventDefault();
            dropZone.classList.add('hover');
        });
    });

    ['dragleave', 'drop'].forEach(e => {
        dropZone.addEventListener(e, (ev) => {
            ev.preventDefault();
            dropZone.classList.remove('hover');
        });
    });

    // Drop handler
    dropZone.addEventListener('drop', (ev) => {
        const files = ev.dataTransfer.files;
        if (files.length) handleUpload(files[0]);
    });

    // Input change handler
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) handleUpload(fileInput.files[0]);
    });

    function handleUpload(file) {
        const formData = new FormData();
        formData.append('file', file);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'upload.php', true);

        // Reset UI
        progressContainer.style.display = 'block';
        statusText.style.display = 'block';
        progressBar.style.width = '0%';
        messageBox.innerHTML = '';

        // Progress event
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                statusText.innerText = `Uploading... ${percent}%`;
            }
        });

        // Response handling
        xhr.onload = () => {
            const response = JSON.parse(xhr.responseText);
            if (xhr.status === 200) {
                messageBox.innerHTML = `<div class="alert alert-success">${response.message}</div>`;
                statusText.innerText = 'Upload Complete!';
                setTimeout(() => window.location.href = 'index.php', 1000);
            } else {
                messageBox.innerHTML = `<div class="alert alert-error">${response.message}</div>`;
                statusText.innerText = 'Upload Failed';
            }
        };

        xhr.send(formData);
    }
});
</script>

<?php require_once __DIR__ . '/../app/views/footer.php'; ?>
