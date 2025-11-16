<?php
require_once 'db_config.php';
startSession();
$pageTitle = 'PC Part Sniper - Build Your Dream PC';

$featuredBuilds = fetchAll("SELECT b.*, u.username 
    FROM builds b 
    LEFT JOIN users u ON b.user_id = u.user_id 
    WHERE b.is_public = ? 
    ORDER BY b.created_at DESC 
    LIMIT 6", [1]);

$trendingParts = fetchAll("SELECT p.*, COUNT(bp.build_part_id) as popularity
    FROM parts p
    LEFT JOIN build_parts bp ON p.part_id = bp.part_id
    GROUP BY p.part_id
    ORDER BY popularity DESC
    LIMIT 6");

include 'includes/header.php';
?>

<div class="container">
    <div class="hero">
        <h1>
            <span style="position: relative; display: inline-block; width: 60px; height: 60px; vertical-align: middle; margin-right: 15px;">
                <span style="color: #FF3B3B; font-size: 4rem; font-weight: bold; position: absolute; left: 0; top: -10px;">⊕</span>
                <span style="color: #FF3B3B; font-size: 2.5rem; font-weight: bold; position: absolute; left: 18px; top: 5px;">+</span>
            </span>
            PC Part Sniper
        </h1>
        <p>Build your dream PC with real-time pricing and compatibility checking</p>
        
        <form id="searchForm" class="search-bar">
            <input type="text" id="searchInput" placeholder="Search for parts (e.g., RTX 5090, AMD Ryzen...)" required>
            <button type="submit" class="btn">Search</button>
        </form>
    </div>

    <h2 class="section-title">Featured Builds</h2>
    <div class="grid">
        <?php if (empty($featuredBuilds)): ?>
            <div class="card">
                <h3>Gaming Beast</h3>
                <p>High-end gaming build with RTX 4090</p>
                <div class="price">$3,299</div>
                <a href="/build.php?new=1" class="btn">Create Your Own</a>
            </div>
            <div class="card">
                <h3>Budget Builder</h3>
                <p>Affordable 1080p gaming setup</p>
                <div class="price">$799</div>
                <a href="/build.php?new=1" class="btn">Create Your Own</a>
            </div>
            <div class="card">
                <h3>Workstation Pro</h3>
                <p>Professional content creation rig</p>
                <div class="price">$2,599</div>
                <a href="/build.php?new=1" class="btn">Create Your Own</a>
            </div>
        <?php else: ?>
            <?php foreach ($featuredBuilds as $build): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($build['build_name']); ?></h3>
                    <p><?php echo htmlspecialchars($build['description'] ?? 'Custom PC build'); ?></p>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        by <?php echo htmlspecialchars($build['username'] ?? 'Anonymous'); ?>
                    </p>
                    <a href="/build.php?build_id=<?php echo $build['build_id']; ?>" class="btn">View Build</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <h2 class="section-title">Trending Parts</h2>
    <div class="grid">
        <?php if (empty($trendingParts)): ?>
            <div class="card">
                <h3>Browse Our Catalog</h3>
                <p>Explore thousands of PC components</p>
                <a href="/parts.php" class="btn">Browse Parts</a>
            </div>
        <?php else: ?>
            <?php foreach ($trendingParts as $part): ?>
                <div class="card">
                    <?php if ($part['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($part['image_url']); ?>" alt="<?php echo htmlspecialchars($part['part_name']); ?>">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($part['part_name']); ?></h3>
                    <span class="badge badge-new"><?php echo htmlspecialchars($part['category']); ?></span>
                    <?php if ($part['price']): ?>
                        <div class="price">$<?php echo number_format($part['price'], 2); ?></div>
                    <?php endif; ?>
                    <a href="/details.php?part_id=<?php echo $part['part_id']; ?>" class="btn btn-secondary">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
