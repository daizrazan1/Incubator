<?php
require_once 'db_config.php';
$pageTitle = 'Browse Parts - PC Part Sniper';

$query = $_GET['query'] ?? '';
$category = $_GET['category'] ?? '';
$brand = $_GET['brand'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$isUsed = $_GET['used'] ?? '';

$sql = "SELECT p.*, MIN(pp.price) as min_price 
        FROM parts p 
        LEFT JOIN part_prices pp ON p.part_id = pp.part_id 
        WHERE 1=1";

$params = [];
$paramIndex = 1;

if ($query) {
    $sql .= " AND (p.part_name LIKE :query OR p.brand LIKE :query2 OR p.model LIKE :query3)";
    $params[':query'] = '%' . $query . '%';
    $params[':query2'] = '%' . $query . '%';
    $params[':query3'] = '%' . $query . '%';
}

if ($category) {
    $sql .= " AND p.category = :category";
    $params[':category'] = $category;
}

if ($brand) {
    $sql .= " AND p.brand = :brand";
    $params[':brand'] = $brand;
}

if ($minPrice) {
    $sql .= " AND p.price >= :minPrice";
    $params[':minPrice'] = $minPrice;
}

if ($maxPrice) {
    $sql .= " AND p.price <= :maxPrice";
    $params[':maxPrice'] = $maxPrice;
}

if ($isUsed !== '') {
    $sql .= " AND p.is_used = :isUsed";
    $params[':isUsed'] = $isUsed;
}

$sql .= " GROUP BY p.part_id ORDER BY p.created_at DESC";

$parts = fetchAll($sql, $params);

$categories = fetchAll("SELECT DISTINCT category FROM parts ORDER BY category");
$brands = fetchAll("SELECT DISTINCT brand FROM parts WHERE brand IS NOT NULL ORDER BY brand");

include 'includes/header.php';
?>

<div class="container">
    <h1 class="section-title">Browse PC Parts</h1>
    
    <div class="filters">
        <h3>Filters</h3>
        <form method="GET" id="filterForm">
            <div class="filter-group">
                <input type="text" name="query" placeholder="Search parts..." value="<?php echo htmlspecialchars($query); ?>">
                
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="CPU" <?php echo $category === 'CPU' ? 'selected' : ''; ?>>CPU</option>
                    <option value="GPU" <?php echo $category === 'GPU' ? 'selected' : ''; ?>>GPU</option>
                    <option value="Motherboard" <?php echo $category === 'Motherboard' ? 'selected' : ''; ?>>Motherboard</option>
                    <option value="RAM" <?php echo $category === 'RAM' ? 'selected' : ''; ?>>RAM</option>
                    <option value="Storage" <?php echo $category === 'Storage' ? 'selected' : ''; ?>>Storage</option>
                    <option value="PSU" <?php echo $category === 'PSU' ? 'selected' : ''; ?>>PSU</option>
                    <option value="Case" <?php echo $category === 'Case' ? 'selected' : ''; ?>>Case</option>
                    <option value="Cooling" <?php echo $category === 'Cooling' ? 'selected' : ''; ?>>Cooling</option>
                </select>
                
                <select name="brand">
                    <option value="">All Brands</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?php echo htmlspecialchars($b['brand']); ?>" 
                                <?php echo $brand === $b['brand'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($b['brand']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <input type="number" name="min_price" placeholder="Min Price" value="<?php echo htmlspecialchars($minPrice); ?>">
                <input type="number" name="max_price" placeholder="Max Price" value="<?php echo htmlspecialchars($maxPrice); ?>">
                
                <select name="used">
                    <option value="">New & Used</option>
                    <option value="0" <?php echo $isUsed === '0' ? 'selected' : ''; ?>>New Only</option>
                    <option value="1" <?php echo $isUsed === '1' ? 'selected' : ''; ?>>Used Only</option>
                </select>
                
                <button type="submit" class="btn">Apply Filters</button>
            </div>
        </form>
    </div>

    <?php if (empty($parts)): ?>
        <div class="alert alert-error">
            No parts found matching your criteria. Try adjusting your filters.
        </div>
    <?php else: ?>
        <p style="color: var(--text-secondary); margin-bottom: 1rem;">
            Found <?php echo count($parts); ?> parts
        </p>
        
        <div class="grid">
            <?php foreach ($parts as $part): ?>
                <div class="card">
                    <?php if ($part['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($part['image_url']); ?>" alt="<?php echo htmlspecialchars($part['part_name']); ?>">
                    <?php endif; ?>
                    
                    <h3><?php echo htmlspecialchars($part['part_name']); ?></h3>
                    <p><?php echo htmlspecialchars($part['brand'] ?? ''); ?> <?php echo htmlspecialchars($part['model'] ?? ''); ?></p>
                    
                    <div>
                        <span class="badge badge-new"><?php echo htmlspecialchars($part['category']); ?></span>
                        <?php if ($part['is_used']): ?>
                            <span class="badge badge-used">Used</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($part['min_price'] || $part['price']): ?>
                        <div class="price">$<?php echo number_format($part['min_price'] ?? $part['price'], 2); ?></div>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <a href="/details.php?part_id=<?php echo $part['part_id']; ?>" class="btn" style="flex: 1;">View Details</a>
                        <button onclick="addToBuild(<?php echo $part['part_id']; ?>)" class="btn btn-secondary">Add to Build</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
