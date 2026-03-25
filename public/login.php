<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

session_start();

if (AuthController::isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = isset($_GET['registered']) ? 'Registration successful! Please login.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDatabaseConnection();
        $auth = new AuthController($db);
        if ($auth->login($_POST['email'], $_POST['password'])) {
            header("Location: index.php");
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once __DIR__ . '/../app/views/header.php';
?>

<div class="card" style="max-width: 500px; margin: 0 auto;">
    <h1>Welcome Back</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="btn btn-secondary" style="width: 100%; text-align: left; background: rgba(255,255,255,0.05);" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="btn btn-secondary" style="width: 100%; text-align: left; background: rgba(255,255,255,0.05);" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Login</button>
    </form>
    <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
        Don't have an account? <a href="register.php" style="color: var(--primary);">Create one</a>
    </p>
</div>

<?php require_once __DIR__ . '/../app/views/footer.php'; ?>
