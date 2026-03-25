<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

session_start();

if (AuthController::isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDatabaseConnection();
        $auth = new AuthController($db);
        if ($auth->register($_POST['name'], $_POST['email'], $_POST['password'])) {
            header("Location: login.php?registered=1");
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once __DIR__ . '/../app/views/header.php';
?>

<div class="card" style="max-width: 500px; margin: 0 auto;">
    <h1>Create Account</h1>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" class="btn btn-secondary" style="width: 100%; text-align: left; background: rgba(255,255,255,0.05);" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="btn btn-secondary" style="width: 100%; text-align: left; background: rgba(255,255,255,0.05);" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="btn btn-secondary" style="width: 100%; text-align: left; background: rgba(255,255,255,0.05);" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Sign Up</button>
    </form>
    <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
        Already have an account? <a href="login.php" style="color: var(--primary);">Login here</a>
    </p>
</div>

<?php require_once __DIR__ . '/../app/views/footer.php'; ?>
