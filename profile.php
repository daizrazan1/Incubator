<?php
require_once 'db_config.php';
$pageTitle = 'My Profile - PC Part Sniper';

$userId = 1;

$user = fetchOne("SELECT * FROM users WHERE user_id = ?", [$userId]);
$builds = fetchAll("SELECT * FROM builds WHERE user_id = ? ORDER BY updated_at DESC", [$userId]);
$reviews = fetchAll("SELECT r.*, p.part_name 
    FROM reviews r 
    JOIN parts p ON r.part_id = p.part_id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC", [$userId]);

include 'includes/header.php';
?>

<div class="container">
    <h1 class="section-title">My Profile</h1>
    
    <div class="two-column">
        <div>
            <div class="build-section">
                <h2>My Builds</h2>
                
                <?php if (empty($builds)): ?>
                    <p style="color: var(--text-secondary);">You haven't created any builds yet.</p>
                    <a href="/build.php?new=1" class="btn" style="margin-top: 1rem;">Create Your First Build</a>
                <?php else: ?>
                    <?php foreach ($builds as $build): ?>
                        <?php
                        $partCount = fetchOne("SELECT COUNT(*) as count FROM build_parts WHERE build_id = ?", 
                                             [$build['build_id']]);
                        $buildParts = fetchAll("SELECT p.price FROM build_parts bp 
                                              JOIN parts p ON bp.part_id = p.part_id 
                                              WHERE bp.build_id = ?", [$build['build_id']]);
                        $total = 0;
                        foreach ($buildParts as $part) {
                            $total += $part['price'] ?? 0;
                        }
                        ?>
                        
                        <div class="build-item compatible">
                            <div style="flex: 1;">
                                <h3><?php echo htmlspecialchars($build['build_name']); ?></h3>
                                <p style="color: var(--text-secondary); margin: 0.5rem 0;">
                                    <?php echo htmlspecialchars($build['description'] ?? 'No description'); ?>
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <span class="badge badge-new">
                                        <?php echo $partCount['count']; ?> parts
                                    </span>
                                    <?php if ($build['is_public']): ?>
                                        <span class="badge badge-compatible">Public</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($total > 0): ?>
                                    <div class="price" style="margin-top: 0.5rem;">
                                        $<?php echo number_format($total, 2); ?>
                                    </div>
                                <?php endif; ?>
                                <small style="color: var(--text-secondary);">
                                    Updated: <?php echo date('M d, Y', strtotime($build['updated_at'])); ?>
                                </small>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <a href="/build.php?build_id=<?php echo $build['build_id']; ?>" class="btn">
                                    Edit
                                </a>
                                <a href="/checkout.php?build_id=<?php echo $build['build_id']; ?>" class="btn btn-secondary">
                                    Buy Parts
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="build-section">
                <h2>My Reviews</h2>
                
                <?php if (empty($reviews)): ?>
                    <p style="color: var(--text-secondary);">You haven't written any reviews yet.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="build-item">
                            <div>
                                <h4><?php echo htmlspecialchars($review['part_name']); ?></h4>
                                <span style="color: var(--highlight);">
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
                <h3>Account Settings</h3>
                
                <?php if ($user): ?>
                    <div style="margin: 1.5rem 0;">
                        <p style="color: var(--text-secondary);">Username</p>
                        <p style="font-size: 1.2rem; font-weight: bold;">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </p>
                    </div>
                    
                    <div style="margin: 1.5rem 0;">
                        <p style="color: var(--text-secondary);">Email</p>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <div style="margin: 1.5rem 0;">
                        <p style="color: var(--text-secondary);">Member Since</p>
                        <p><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-secondary);">Please log in to view your profile.</p>
                <?php endif; ?>
            </div>
            
            <div class="build-summary" style="margin-top: 2rem;">
                <h3>Quick Stats</h3>
                
                <div style="margin: 1.5rem 0;">
                    <p style="color: var(--text-secondary);">Total Builds</p>
                    <p style="font-size: 1.5rem; font-weight: bold;">
                        <?php echo count($builds); ?>
                    </p>
                </div>
                
                <div style="margin: 1.5rem 0;">
                    <p style="color: var(--text-secondary);">Reviews Written</p>
                    <p style="font-size: 1.5rem; font-weight: bold;">
                        <?php echo count($reviews); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
