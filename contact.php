<?php
require_once 'db_config.php';
$pageTitle = 'Contact Us - PC Part Sniper';

$submitted = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    if ($subject && $message) {
        try {
            execute("INSERT INTO support_tickets (user_id, subject, message) VALUES (?, ?, ?)",
                   [$userId, $subject, $message]);
            $submitted = true;
        } catch (Exception $e) {
            $error = true;
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h1 class="section-title">Contact Us</h1>
    
    <div class="two-column">
        <div>
            <?php if ($submitted): ?>
                <div class="alert alert-success">
                    <strong>Thank you!</strong> Your message has been received. We'll get back to you soon.
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> Failed to submit your message. Please try again.
                </div>
            <?php endif; ?>
            
            <div class="build-section">
                <h2>Send us a message</h2>
                
                <?php if (!isLoggedIn()): ?>
                    <div class="alert alert-error" style="text-align: center;">
                        <a href="/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" style="color: var(--highlight); text-decoration: underline;">Log in</a> to send us a message
                    </div>
                <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" placeholder="How can we help?" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" rows="8" placeholder="Tell us more about your inquiry..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn" style="width: 100%;">Send Message</button>
                </form>
                <?php endif; ?>
            </div>
            
            <div class="build-section">
                <h2>Frequently Asked Questions</h2>
                
                <div class="build-item">
                    <div>
                        <strong>How do I create a build?</strong>
                        <p style="color: var(--text-secondary); margin-top: 0.5rem;">
                            Click "Build PC" in the navigation menu, then select parts for each category. 
                            Our compatibility checker will ensure all parts work together.
                        </p>
                    </div>
                </div>
                
                <div class="build-item">
                    <div>
                        <strong>Where do you get your prices?</strong>
                        <p style="color: var(--text-secondary); margin-top: 0.5rem;">
                            We aggregate prices from major retailers like Amazon, Newegg, and Micro Center 
                            to help you find the best deals.
                        </p>
                    </div>
                </div>
                
                <div class="build-item">
                    <div>
                        <strong>How does compatibility checking work?</strong>
                        <p style="color: var(--text-secondary); margin-top: 0.5rem;">
                            We check socket compatibility (CPU/Motherboard), form factors, power requirements, 
                            and other technical specs to ensure your build will work.
                        </p>
                    </div>
                </div>
                
                <div class="build-item">
                    <div>
                        <strong>Can I save multiple builds?</strong>
                        <p style="color: var(--text-secondary); margin-top: 0.5rem;">
                            Yes! Save as many builds as you want in your profile. You can make them public 
                            to share with others or keep them private.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div>
            <div class="build-summary">
                <h3>Contact Information</h3>
                
                <div style="margin: 1.5rem 0;">
                    <p style="color: var(--text-secondary);">Email Support</p>
                    <p style="font-size: 1.1rem;">
                        support@pcpartsniper.com
                    </p>
                </div>
                
                <div style="margin: 1.5rem 0;">
                    <p style="color: var(--text-secondary);">Response Time</p>
                    <p>Usually within 24 hours</p>
                </div>
            </div>
            
            <div class="build-summary" style="margin-top: 2rem;">
                <h3>Quick Links</h3>
                
                <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem;">
                    <a href="/parts.php" class="btn btn-secondary">Browse Parts</a>
                    <a href="/build.php?new=1" class="btn btn-secondary">Start Building</a>
                    <a href="/profile.php" class="btn btn-secondary">My Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
