<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FileShare Luxe</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container nav-wrapper">
            <a href="index.php" class="logo">FILESHARE LUXE</a>
            
            <button class="menu-btn" id="menuToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="nav-links" id="navLinks">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="index.php">History</a>
                    <a href="profile.php">My Profile</a>
                    <a href="upload.php" class="btn btn-secondary" style="padding: 0.5rem 1.2rem;">+ Upload</a>
                    <a href="logout.php" style="color: #ef4444;">Logout</a>
                <?php else: ?>
                    <a href="index.php">Browse</a>
                    <a href="login.php">Login</a>
                    <a href="register.php" class="btn btn-primary">Join Now</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <script src="assets/js/main.js"></script>
    <main class="container">
