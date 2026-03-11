<?php
require_once 'db_config.php';

$partId = $_GET['part_id'] ?? 0;

if (!$partId) {
    header('Location: /parts.php');
    exit;
}

$part = fetchOne("SELECT * FROM parts WHERE part_id = ?", [$partId]);

if (!$part) {
    header('Location: /parts.php');
    exit;
}

$merchants = fetchAll("SELECT pp.*, m.merchant_name, m.website_url 
    FROM part_prices pp 
    JOIN merchants m ON pp.merchant_id = m.merchant_id 
    WHERE pp.part_id = ? AND pp.in_stock = 1
    ORDER BY pp.price ASC", [$partId]);

$reviews = fetchAll("SELECT r.*, u.username 
    FROM reviews r 
    LEFT JOIN users u ON r.user_id = u.user_id 
    WHERE r.part_id = ? 
    ORDER BY r.review_id DESC", [$partId]);

$avgRating = fetchOne("SELECT AVG(rating) as avg FROM reviews WHERE part_id = ?", [$partId]);

$pageTitle = htmlspecialchars($part['part_name']) . ' - PC Part Sniper';

include 'includes/header.php';
?>

<div class="container">
    <div class="two-column">
        <div>
            <?php if (!empty($part['image_url'])): ?>
                <div style="width: 100%; height: 400px; background: var(--secondary); border-radius: 10px; margin-bottom: 2rem; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                    <img src="<?php echo htmlspecialchars($part['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($part['part_name']); ?>" 
                         style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
            <?php endif; ?>
            
            <h1><?php echo htmlspecialchars($part['part_name']); ?></h1>
            
            <div style="margin: 1rem 0;">
                <span class="badge badge-new"><?php echo htmlspecialchars($part['category']); ?></span>
                <?php if ($part['is_used']): ?>
                    <span class="badge badge-used">Used</span>
                <?php else: ?>
                    <span class="badge badge-new">New</span>
                <?php endif; ?>
            </div>
            
            <p style="color: var(--text-secondary); font-size: 1.1rem; margin: 1rem 0;">
                <?php echo htmlspecialchars($part['brand'] ?? ''); ?> 
                <?php echo htmlspecialchars($part['model'] ?? ''); ?>
            </p>
            
            <?php if ($avgRating && $avgRating['avg']): ?>
                <p style="color: var(--highlight); font-size: 1.2rem;">
                    ⭐ <?php echo number_format($avgRating['avg'], 1); ?> / 5.0 
                    (<?php echo count($reviews); ?> reviews)
                </p>
            <?php endif; ?>
            
            <div class="build-section">
                <h2>Specifications</h2>
                <table class="specs-table">
                    <tbody>
                        <tr>
                            <th>Category</th>
                            <td><?php echo htmlspecialchars($part['category']); ?></td>
                        </tr>
                        <tr>
                            <th>Brand</th>
                            <td><?php echo htmlspecialchars($part['brand'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Model</th>
                            <td><?php echo htmlspecialchars($part['model'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php if ($part['socket']): ?>
                        <tr>
                            <th>Socket</th>
                            <td><?php echo htmlspecialchars($part['socket']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($part['form_factor']): ?>
                        <tr>
                            <th>Form Factor</th>
                            <td><?php echo htmlspecialchars($part['form_factor']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($part['tdp']): ?>
                        <tr>
                            <th>TDP</th>
                            <td><?php echo htmlspecialchars($part['tdp']); ?> W</td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($part['wattage']): ?>
                        <tr>
                            <th>Wattage</th>
                            <td><?php echo htmlspecialchars($part['wattage']); ?> W</td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($part['specs']): ?>
                        <tr>
                            <th>Additional Specs</th>
                            <td><?php echo nl2br(htmlspecialchars($part['specs'])); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="build-section">
                <h2>Customer Reviews</h2>
                
                <!-- Add Review Form -->
                <form action="/submit_review.php" method="POST" style="margin-bottom: 2rem;">
                    <input type="hidden" name="part_id" value="<?php echo $partId; ?>">
                    <input type="hidden" name="user_id" value="1"> <!-- Assume user is logged in and user ID is 1 for now -->
                    <div style="margin-bottom: 1rem;">
                        <label for="rating" style="display: block; margin-bottom: 0.5rem;">Rate this part:</label>
                        <select name="rating" id="rating" style="padding: 0.5rem; border-radius: 4px; border: 1px solid var(--border-color);">
                            <option value="1">1 Star</option>
                            <option value="2">2 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="5">5 Stars</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="review_text" style="display: block; margin-bottom: 0.5rem;">Write a review:</label>
                        <textarea name="review_text" id="review_text" rows="4" style="width: 100%; padding: 0.5rem; border-radius: 4px; border: 1px solid var(--border-color);"></textarea>
                    </div>
                    <button type="submit" style="padding: 0.5rem 1rem; background-color: var(--primary-color); color: white; border: none; border-radius: 4px; cursor: pointer;">Submit Review</button>
                </form>
                
                <?php if (empty($reviews)): ?>
                    <p style="color: var(--text-secondary);">No reviews yet. Be the first to review this product!</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="build-item">
                            <div>
                                <strong><?php echo htmlspecialchars($review['username'] ?? 'Anonymous'); ?></strong>
                                <span style="color: var(--highlight); margin-left: 1rem;">
                                    <?php echo str_repeat('⭐', $review['rating']); ?>
                                </span>
                                <p style="margin-top: 0.5rem; color: var(--text-secondary);">
                                    <?php echo htmlspecialchars($review['review_text']); ?>
                                </p>
                                <small style="color: var(--text-secondary);">
                                    <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="build-summary">
                <h3>Available From</h3>
                
                <?php if (empty($merchants)): ?>
                    <?php if ($part['price']): ?>
                        <div class="total-price"><?php echo formatCurrency($part['price']); ?></div>
                        <p style="color: var(--text-secondary); margin-bottom: 1rem;">Base price</p>
                    <?php else: ?>
                        <p style="color: var(--text-secondary);">Price not available</p>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="merchant-list">
                        <?php foreach ($merchants as $merchant): ?>
                            <div class="merchant-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($merchant['merchant_name']); ?></strong>
                                    <div class="price" style="margin: 0.5rem 0;">
                                        <?php echo formatCurrency($merchant['price']); ?>
                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($merchant['url']); ?>" 
                                   target="_blank" 
                                   class="btn"
                                   onclick="trackClick(<?php echo $partId; ?>, <?php echo $merchant['merchant_id']; ?>)">
                                    Buy Now
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <button onclick="addToBuild(<?php echo $partId; ?>)" class="btn" style="width: 100%; margin-top: 1rem;">
                    Add to Build
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>