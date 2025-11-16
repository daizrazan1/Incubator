<?php
require_once 'db_config.php';
require_once 'compatibility.php';
$pageTitle = 'Build Your PC - PC Part Sniper';

$buildId = $_GET['build_id'] ?? null;
$build = null;
$buildParts = [];
$totalPrice = 0;
$compatibilityResult = null;

if ($buildId) {
    $build = fetchOne("SELECT * FROM builds WHERE build_id = ?", [$buildId]);
    if ($build) {
        $buildParts = fetchAll("SELECT bp.*, p.* 
            FROM build_parts bp 
            JOIN parts p ON bp.part_id = p.part_id 
            WHERE bp.build_id = ?", [$buildId]);
        
        foreach ($buildParts as $part) {
            $totalPrice += $part['price'] ?? 0;
        }
        
        if (!empty($buildParts)) {
            $compatibilityResult = checkCompatibility($buildParts);
        }
    }
}

$categories = ['CPU', 'GPU', 'Motherboard', 'RAM', 'Storage', 'PSU', 'Case', 'Cooling'];

include 'includes/header.php';
?>

<div class="container">
    <h1 class="section-title">Build Your PC</h1>
    
    <div class="two-column">
        <div>
            <?php if ($build): ?>
                <div class="build-section">
                    <h2><?php echo htmlspecialchars($build['build_name']); ?></h2>
                    <p style="color: var(--text-secondary);">
                        <?php echo htmlspecialchars($build['description'] ?? ''); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <div class="build-section">
                <h3>Selected Components</h3>
                
                <?php foreach ($categories as $cat): ?>
                    <?php
                    $categoryPart = null;
                    foreach ($buildParts as $part) {
                        if ($part['category'] === $cat) {
                            $categoryPart = $part;
                            break;
                        }
                    }
                    ?>
                    
                    <?php if ($categoryPart): ?>
                        <?php
                        $partCompat = $compatibilityResult['part_compatibility'][$categoryPart['part_id']] ?? ['compatible' => true, 'issues' => []];
                        $compatClass = $partCompat['compatible'] ? 'compatible' : 'incompatible';
                        ?>
                        <div class="build-item-compact <?php echo $compatClass; ?>">
                            <div style="min-width: 100px;">
                                <strong style="color: var(--accent);"><?php echo $cat; ?></strong>
                            </div>
                            <div style="flex: 1;">
                                <strong><?php echo htmlspecialchars($categoryPart['part_name']); ?></strong>
                                <span style="color: var(--text-secondary); margin-left: 0.5rem; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($categoryPart['brand']); ?>
                                </span>
                                <?php if (!empty($partCompat['issues'])): ?>
                                    <div style="margin-top: 0.25rem;">
                                        <?php foreach ($partCompat['issues'] as $issue): ?>
                                            <div style="color: #FF3B3B; font-size: 0.85rem;">⚠ <?php echo htmlspecialchars($issue); ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <?php if ($categoryPart['price']): ?>
                                    <span class="price" style="font-size: 1rem;">
                                        $<?php echo number_format($categoryPart['price'], 2); ?>
                                    </span>
                                <?php endif; ?>
                                <a href="/parts.php?category=<?php echo urlencode($cat); ?>&build_id=<?php echo $buildId; ?>" 
                                   class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Change</a>
                                <button onclick="removePart(<?php echo $categoryPart['build_part_id']; ?>)" 
                                        class="btn-remove" title="Remove part">✕</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="build-item-compact">
                            <div style="min-width: 100px;">
                                <strong style="color: var(--text-secondary);"><?php echo $cat; ?></strong>
                            </div>
                            <div style="flex: 1;">
                                <span style="color: var(--text-secondary);">Not selected</span>
                            </div>
                            <div>
                                <a href="/parts.php?category=<?php echo urlencode($cat); ?>&build_id=<?php echo $buildId; ?>" 
                                   class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Choose</a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($buildParts) && $compatibilityResult): ?>
                <div class="build-section">
                    <h3>Compatibility Check</h3>
                    
                    <?php if ($compatibilityResult['overall_compatible']): ?>
                        <div class="alert alert-success">
                            <strong>✓ All parts are compatible!</strong>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-error">
                            <strong>⚠ Compatibility Issues Found:</strong>
                            <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                                <?php foreach ($compatibilityResult['issues'] as $issue): ?>
                                    <li><?php echo htmlspecialchars($issue); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div>
            <div class="build-summary">
                <h3>Build Summary</h3>
                
                <div style="margin: 1.5rem 0;">
                    <p style="color: var(--text-secondary);">Total Components</p>
                    <p style="font-size: 1.5rem; font-weight: bold;">
                        <?php echo count($buildParts); ?> / 8
                    </p>
                </div>
                
                <div style="margin: 1.5rem 0;">
                    <p style="color: var(--text-secondary);">Estimated Total</p>
                    <div class="total-price">$<?php echo number_format($totalPrice, 2); ?></div>
                </div>
                
                <?php if (!empty($buildParts)): ?>
                    <a href="/checkout.php?build_id=<?php echo $buildId; ?>" class="btn" style="width: 100%; margin-bottom: 1rem;">
                        Proceed to Checkout
                    </a>
                <?php endif; ?>
                
                <?php if (!isLoggedIn()): ?>
                    <div class="alert alert-error" style="margin-top: 1rem; text-align: center;">
                        <a href="/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" style="color: var(--highlight); text-decoration: underline;">Login</a> to save your build
                    </div>
                <?php else: ?>
                <form method="POST" action="/api/save_build.php" style="margin-top: 1rem;">
                    <?php if ($buildId): ?>
                        <input type="hidden" name="build_id" value="<?php echo $buildId; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Build Name</label>
                        <input type="text" name="build_name" 
                               value="<?php echo htmlspecialchars($build['build_name'] ?? ''); ?>" 
                               placeholder="My Gaming PC" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3" 
                                  placeholder="Build description..."><?php echo htmlspecialchars($build['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_public" value="1" 
                                   <?php echo ($build['is_public'] ?? 0) ? 'checked' : ''; ?>>
                            Make build public
                        </label>
                    </div>
                    
                    <button type="submit" class="btn" style="width: 100%;">Save Build</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
