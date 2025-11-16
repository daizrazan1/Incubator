<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'PC Part Sniper'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="/index.php"><span style="color: #FF3B3B; font-size: 1.3rem;">⊕</span> PC Part Sniper</a>
            </div>
            <ul class="nav-menu">
                <li><a href="/index.php">Home</a></li>
                <li><a href="/parts.php">Browse Parts</a></li>
                <li><a href="/build.php">Build PC</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="/profile.php">My Profile</a></li>
                    <li><a href="/contact.php">Contact</a></li>
                    <li>
                        <span style="color: var(--primary); margin-right: 10px;">
                            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                        </span>
                        <a href="/logout.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9rem;">Logout</a>
                    </li>
                <?php else: ?>
                    <li><a href="/contact.php">Contact</a></li>
                    <li><a href="/login.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9rem;">Login</a></li>
                    <li><a href="/register.php" class="btn" style="padding: 8px 16px; font-size: 0.9rem;">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main class="main-content">
