<?php
require_once 'db_config.php';
startSession();
$pageTitle = 'Register - PC Part Sniper';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = 'Username must be between 3 and 50 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username already exists
        $existingUser = fetchOne("SELECT user_id FROM users WHERE username = ?", [$username]);
        if ($existingUser) {
            $error = 'Username already taken.';
        } else {
            // Check if email already exists
            $existingEmail = fetchOne("SELECT user_id FROM users WHERE email = ?", [$email]);
            if ($existingEmail) {
                $error = 'Email already registered.';
            } else {
                // Create user
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $result = execute("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)", 
                    [$username, $email, $passwordHash]);
                
                if ($result) {
                    $userId = lastInsertId();
                    loginUser($userId, $username);
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div style="max-width: 500px; margin: 50px auto;">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 30px;">Create Account</h2>
            
            <?php if ($error): ?>
                <div style="background: #f44336; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="background: #4CAF50; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div style="margin-bottom: 20px;">
                    <label for="username" style="display: block; margin-bottom: 8px; font-weight: 500;">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                        style="width: 100%; font-size: 1rem;"
                    >
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="email" style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        required
                        style="width: 100%; font-size: 1rem;"
                    >
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="password" style="display: block; margin-bottom: 8px; font-weight: 500;">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        style="width: 100%; font-size: 1rem;"
                    >
                    <small style="color: var(--text-secondary); display: block; margin-top: 5px;">At least 6 characters</small>
                </div>
                
                <div style="margin-bottom: 25px;">
                    <label for="confirm_password" style="display: block; margin-bottom: 8px; font-weight: 500;">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        style="width: 100%; font-size: 1rem;"
                    >
                </div>
                
                <button type="submit" class="btn" style="width: 100%; padding: 15px; font-size: 1rem;">
                    Register
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 20px; color: var(--text-secondary);">
                Already have an account? <a href="login.php" style="color: var(--primary);">Login here</a>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
