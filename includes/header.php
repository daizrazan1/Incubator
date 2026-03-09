<?php
require_once 'db_config.php';
require_once 'includes/translations.php';
startSession();

// Track page visit for analytics
if (function_exists('isLoggedIn')) {
    $pageUrl = $_SERVER['REQUEST_URI'] ?? '/';
    $userId = isLoggedIn() ? ($_SESSION['user_id'] ?? null) : null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    try {
        execute("INSERT INTO page_visits (page_url, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?)",
               [$pageUrl, $userId, $ipAddress, $userAgent]);
    } catch (Exception $e) {
        // Silently fail if table doesn't exist yet
    }
}

$currentTheme = $_SESSION['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'PC Part Sniper'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="<?php echo $currentTheme === 'dark' ? 'dark-mode' : ''; ?>">
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="/index.php">
                    <span class="logo-icon">
                        <img src="/assets/images/logo.png" alt="PC Part Sniper Logo">
                    </span>
                    PC Part Sniper
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="/index.php"><?php echo __('home'); ?></a></li>
                <li><a href="/parts.php"><?php echo __('browse_parts'); ?></a></li>
                <li><a href="/build.php"><?php echo __('build_pc'); ?></a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="/profile.php"><?php echo __('my_profile'); ?></a></li>
                    <li><a href="/contact.php"><?php echo __('contact'); ?></a></li>
                    <li>
                        <span style="color: var(--text); margin-right: 10px;">
                            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                        </span>
                        <a href="/logout.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9rem;"><?php echo __('logout'); ?></a>
                    </li>
                <?php else: ?>
                    <li><a href="/contact.php"><?php echo __('contact'); ?></a></li>
                    <li><a href="/login.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9rem;"><?php echo __('login'); ?></a></li>
                    <li><a href="/register.php" class="btn" style="padding: 8px 16px; font-size: 0.9rem;"><?php echo __('register'); ?></a></li>
                <?php endif; ?>
                <li>
                    <a href="/settings.php" class="settings-link" title="<?php echo __('settings'); ?>">⚙️</a>
                </li>
            </ul>
        </div>
    </nav>
    <main class="main-content">
