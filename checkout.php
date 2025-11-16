<?php
require_once 'db_config.php';
$pageTitle = 'Checkout - PC Part Sniper';

$buildId = $_GET['build_id'] ?? null;

if (!$buildId) {
    header('Location: /build.php');
    exit;
}

$build = fetchOne("SELECT * FROM builds WHERE build_id = ?", [$buildId]);

if (!$build) {
    header('Location: /build.php');
    exit;
}

$buildParts = fetchAll("SELECT bp.*, p.* 
    FROM build_parts bp 
    JOIN parts p ON bp.part_id = p.part_id 
    WHERE bp.build_id = ?", [$buildId]);

$totalPrice = 0;
$partsMerchants = [];

foreach ($buildParts as $part) {
    $merchants = fetchAll("SELECT pp.*, m.merchant_name, m.website_url 
        FROM part_prices pp 
        JOIN merchants m ON pp.merchant_id = m.merchant_id 
        WHERE pp.part_id = ? AND pp.in_stock = 1
        ORDER BY pp.price ASC 
        LIMIT 1", [$part['part_id']]);
    
    if (!empty($merchants)) {
        $partsMerchants[$part['part_id']] = $merchants[0];
        $totalPrice += $merchants[0]['price'];
    } else {
        $totalPrice += $part['price'] ?? 0;
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h1 class="section-title">Checkout</h1>
    
    <div class="two-column">
        <div>
            <div class="build-section">
                <h2><?php echo htmlspecialchars($build['build_name']); ?></h2>
                <p style="color: var(--text-secondary);">
                    <?php echo htmlspecialchars($build['description'] ?? ''); ?>
                </p>
            </div>
            
            <div class="build-section">
                <h3>Parts List</h3>
                
                <?php if (empty($buildParts)): ?>
                    <p style="color: var(--text-secondary);">No parts in this build.</p>
                    <a href="/build.php?build_id=<?php echo $buildId; ?>" class="btn">Add Parts</a>
                <?php else: ?>
                    <?php foreach ($buildParts as $part): ?>
                        <div class="build-item compatible">
                            <div style="flex: 1;">
                                <span class="badge badge-new"><?php echo htmlspecialchars($part['category']); ?></span>
                                <h4 style="margin-top: 0.5rem;">
                                    <?php echo htmlspecialchars($part['part_name']); ?>
                                </h4>
                                <p style="color: var(--text-secondary); margin: 0.5rem 0;">
                                    <?php echo htmlspecialchars($part['brand']); ?> 
                                    <?php echo htmlspecialchars($part['model'] ?? ''); ?>
                                </p>
                                
                                <?php if (isset($partsMerchants[$part['part_id']])): ?>
                                    <?php $merchant = $partsMerchants[$part['part_id']]; ?>
                                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                        From: <?php echo htmlspecialchars($merchant['merchant_name']); ?>
                                    </p>
                                    <div class="price" style="margin-top: 0.5rem;">
                                        $<?php echo number_format($merchant['price'], 2); ?>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($merchant['url']); ?>" 
                                       target="_blank" 
                                       class="btn btn-secondary"
                                       style="margin-top: 0.5rem;"
                                       onclick="trackClick(<?php echo $part['part_id']; ?>, <?php echo $merchant['merchant_id']; ?>)">
                                        Buy from <?php echo htmlspecialchars($merchant['merchant_name']); ?>
                                    </a>
                                <?php else: ?>
                                    <?php if ($part['price']): ?>
                                        <div class="price" style="margin-top: 0.5rem;">
                                            $<?php echo number_format($part['price'], 2); ?>
                                        </div>
                                    <?php endif; ?>
                                    <a href="/details.php?part_id=<?php echo $part['part_id']; ?>" class="btn btn-secondary" style="margin-top: 0.5rem;">
                                        View Merchants
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="build-summary">
                <h3>Order Summary</h3>
                
                <div style="margin: 1.5rem 0;">
                    <p style="color: var(--text-secondary);">Total Parts</p>
                    <p style="font-size: 1.5rem; font-weight: bold;">
                        <?php echo count($buildParts); ?>
                    </p>
                </div>
                
                <div style="margin: 1.5rem 0; padding-top: 1.5rem; border-top: 2px solid var(--primary);">
                    <p style="color: var(--text-secondary);">Estimated Total</p>
                    <div class="total-price">$<?php echo number_format($totalPrice, 2); ?></div>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">
                        * Prices may vary. Check merchant sites for current pricing.
                    </p>
                </div>
                
                <div class="alert alert-success" style="margin-top: 1.5rem;">
                    <strong>How it works:</strong>
                    <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                        <li>Click "Buy" next to each part</li>
                        <li>Complete purchase on merchant site</li>
                        <li>Parts ship directly to you</li>
                    </ul>
                </div>
                
                <a href="/build.php?build_id=<?php echo $buildId; ?>" class="btn btn-secondary" style="width: 100%; margin-top: 1rem;">
                    Back to Build
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
