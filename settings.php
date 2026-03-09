<?php
require_once 'db_config.php';
require_once 'includes/translations.php';
startSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['lang'] = $_POST['language'] ?? 'en';
    $_SESSION['currency'] = $_POST['currency'] ?? 'USD';
    $_SESSION['theme'] = $_POST['theme'] ?? 'light';
    
    $success = "Settings saved successfully!";
}

$pageTitle = __('settings') . ' - PC Part Sniper';
include 'includes/header.php';
?>

<div class="container">
    <div class="build-section" style="max-width: 600px; margin: 0 auto;">
        <h2><?php echo __('settings'); ?></h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="language"><?php echo __('language'); ?></label>
                <select name="language" id="language">
                    <option value="en" <?php echo ($_SESSION['lang'] ?? 'en') === 'en' ? 'selected' : ''; ?>>English</option>
                    <option value="es" <?php echo ($_SESSION['lang'] ?? '') === 'es' ? 'selected' : ''; ?>>Español</option>
                    <option value="fr" <?php echo ($_SESSION['lang'] ?? '') === 'fr' ? 'selected' : ''; ?>>Français</option>
                    <option value="hy" <?php echo ($_SESSION['lang'] ?? '') === 'hy' ? 'selected' : ''; ?>>Հայերեն (Armenian)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="currency"><?php echo __('currency'); ?></label>
                <select name="currency" id="currency">
                    <option value="USD" <?php echo ($_SESSION['currency'] ?? 'USD') === 'USD' ? 'selected' : ''; ?>>US Dollar (USD)</option>
                    <option value="EUR" <?php echo ($_SESSION['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                    <option value="GBP" <?php echo ($_SESSION['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>British Pound (GBP)</option>
                    <option value="JPY" <?php echo ($_SESSION['currency'] ?? '') === 'JPY' ? 'selected' : ''; ?>>Japanese Yen (JPY)</option>
                    <option value="CAD" <?php echo ($_SESSION['currency'] ?? '') === 'CAD' ? 'selected' : ''; ?>>Canadian Dollar (CAD)</option>
                    <option value="AUD" <?php echo ($_SESSION['currency'] ?? '') === 'AUD' ? 'selected' : ''; ?>>Australian Dollar (AUD)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="theme"><?php echo __('theme'); ?></label>
                <select name="theme" id="theme">
                    <option value="light" <?php echo ($_SESSION['theme'] ?? 'light') === 'light' ? 'selected' : ''; ?>><?php echo __('light'); ?></option>
                    <option value="dark" <?php echo ($_SESSION['theme'] ?? '') === 'dark' ? 'selected' : ''; ?>><?php echo __('dark'); ?></option>
                </select>
            </div>
            
            <button type="submit" class="btn"><?php echo __('save_settings'); ?></button>
        </form>
    </div>
</div>

<script>
// Sync theme to localStorage on save
document.querySelector('form').addEventListener('submit', function() {
    const theme = document.getElementById('theme').value;
    localStorage.setItem('theme', theme);
});
</script>

<?php include 'includes/footer.php'; ?>
