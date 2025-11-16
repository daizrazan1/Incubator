<?php
require_once 'db_config.php';
startSession();
$pageTitle = 'Login - PC Part Sniper';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Check user credentials
        $user = fetchOne("SELECT user_id, username, password_hash FROM users WHERE username = ?", [$username]);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            loginUser($user['user_id'], $user['username']);
            
            // Sanitize redirect parameter to prevent open redirect attacks
            $redirect = $_GET['redirect'] ?? 'index.php';
            
            // Normalize: replace backslashes with forward slashes
            $redirect = str_replace('\\', '/', trim($redirect));
            
            // Parse the URL to detect scheme/host
            $parsed = parse_url($redirect);
            
            // Only allow redirects with no scheme and no host (i.e., relative paths only)
            if (isset($parsed['scheme']) || isset($parsed['host']) || strpos($redirect, '//') === 0) {
                $redirect = 'index.php';
            }
            
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div style="max-width: 500px; margin: 50px auto;">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 30px;">Login to PC Part Sniper</h2>
            
            <?php if ($error): ?>
                <div style="background: #f44336; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
                <div style="margin-bottom: 20px;">
                    <label for="username" style="display: block; margin-bottom: 8px; font-weight: 500;">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                        autofocus
                        style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--card-bg); color: var(--text); font-size: 1rem;"
                    >
                </div>
                
                <div style="margin-bottom: 25px;">
                    <label for="password" style="display: block; margin-bottom: 8px; font-weight: 500;">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--card-bg); color: var(--text); font-size: 1rem;"
                    >
                </div>
                
                <button type="submit" class="btn" style="width: 100%; padding: 15px; font-size: 1rem;">
                    Login
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 20px; color: var(--text-secondary);">
                Don't have an account? <a href="register.php" style="color: var(--primary);">Register here</a>
            </p>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border); text-align: center;">
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 10px;">Demo Account:</p>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">
                    Username: <strong>demo_user</strong> | Password: <strong>demo123</strong>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
