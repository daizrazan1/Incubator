<?php
require_once 'db_config.php';
$pageTitle = 'Browse Parts - PC Part Sniper';

$query = $_GET['query'] ?? '';
$category = $_GET['category'] ?? '';
$brand = $_GET['brand'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$isUsed = $_GET['used'] ?? '';
$buildId = $_GET['build_id'] ?? null; // Added to get build_id from URL

$sql = "SELECT p.*, MIN(pp.price) as min_price 
        FROM parts p 
        LEFT JOIN part_prices pp ON p.part_id = pp.part_id 
        WHERE 1=1";

$params = [];

if ($query) {
    $sql .= " AND (p.part_name LIKE ? OR p.brand LIKE ? OR p.model LIKE ?)";
    $params[] = '%' . $query . '%';
    $params[] = '%' . $query . '%';
    $params[] = '%' . $query . '%';
}

if ($category) {
    $sql .= " AND p.category = ?";
    $params[] = $category;
}

if ($brand) {
    $sql .= " AND p.brand = ?";
    $params[] = $brand;
}

if ($minPrice) {
    $sql .= " AND p.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice) {
    $sql .= " AND p.price <= ?";
    $params[] = $maxPrice;
}

if ($isUsed !== '') {
    $sql .= " AND p.is_used = ?";
    $params[] = $isUsed;
}

$sql .= " GROUP BY p.part_id ORDER BY p.part_id DESC";

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
            <input type="hidden" name="build_id" value="<?php echo htmlspecialchars($buildId ?? ''); ?>">
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
                    <?php if (!empty($part['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($part['image_url']); ?>" alt="<?php echo htmlspecialchars($part['part_name']); ?>">
                    <?php endif; ?>

                    <h3><?php echo htmlspecialchars($part['part_name']); ?></h3>
                    <p><?php echo htmlspecialchars($part['brand'] ?? ''); ?> <?php echo htmlspecialchars($part['model'] ?? ''); ?></p>

                    <div>
                        <span class="badge badge-new"><?php echo htmlspecialchars($part['category']); ?></span>
                        <?php if (!empty($part['is_used'])): ?>
                            <span class="badge badge-used">Used</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($part['min_price'] || $part['price']): ?>
                        <div class="price">$<?php echo number_format($part['min_price'] ?? $part['price'], 2); ?></div>
                    <?php endif; ?>

                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <a href="/details.php?part_id=<?php echo $part['part_id']; ?><?php echo $buildId ? '&build_id=' . $buildId : ''; ?>" class="btn" style="flex: 1;">View Details</a>
                        <button onclick="addToBuild(<?php echo $part['part_id']; ?>, '<?php echo $buildId ?? ''; ?>')" class="btn btn-secondary">Add to Build</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function addToBuild(partId, buildId) {
    // If buildId is empty string or null, set it to 'new'
    if (!buildId || buildId === '') {
        buildId = 'new';
    }
    
    fetch('/api/add_to_build.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ part_id: partId, build_id: buildId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to the build page with the returned build_id
            window.location.href = `/build.php?build_id=${data.build_id}`;
        } else {
            alert('Failed to add part to build: ' + data.message);
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred while adding the part.');
    });
}

function removeFromBuild(partId, buildId) {
    if (!buildId) {
        alert('Cannot remove part: Build ID is missing.');
        return;
    }
    if (!confirm('Are you sure you want to remove this part from your build?')) {
        return;
    }

    fetch('/api/remove_from_build.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ part_id: partId, build_id: buildId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the build page to reflect the removal
            window.location.reload();
        } else {
            alert('Failed to remove part from build: ' + data.message);
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred while removing the part.');
    });
}
</script>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.section-title {
    text-align: center;
    margin-bottom: 30px;
    color: var(--text-primary);
}

.filters {
    background-color: var(--background-secondary);
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filters h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: var(--text-primary);
}

.filter-group {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
    align-items: center;
}

.filter-group input[type="text"],
.filter-group input[type="number"],
.filter-group select {
    padding: 10px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    font-size: 1rem;
    background-color: var(--background-primary);
    color: var(--text-primary);
    flex: 1; /* Allow items to grow */
    min-width: 150px; /* Minimum width for better responsiveness */
}

.filter-group button[type="submit"] {
    padding: 10px 20px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s ease;
}

.filter-group button[type="submit"]:hover {
    background-color: var(--primary-color-dark);
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.card {
    background-color: var(--background-secondary);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* Pushes buttons to the bottom */
}

.card img {
    max-width: 100%;
    height: 150px;
    object-fit: contain;
    margin-bottom: 15px;
    border-radius: 4px;
}

.card h3 {
    margin-top: 0;
    margin-bottom: 5px;
    font-size: 1.3rem;
    color: var(--text-primary);
}

.card p {
    color: var(--text-secondary);
    margin-bottom: 10px;
    font-size: 0.95rem;
}

.card .badge {
    display: inline-block;
    background-color: var(--accent-color);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.8rem;
    margin-right: 5px;
}

.card .badge-used {
    background-color: var(--warning-color);
}

.card .price {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.card .btn {
    display: inline-block;
    padding: 10px 15px;
    background-color: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 1rem;
    transition: background-color 0.3s ease;
    cursor: pointer;
    border: none; /* Ensure button styling is consistent */
    text-align: center;
    flex: 1; /* Allows buttons to share space */
}

.card .btn-secondary {
    background-color: var(--secondary-color);
}

.card .btn:hover {
    background-color: var(--primary-color-dark);
}

.card .btn-secondary:hover {
    background-color: var(--secondary-color-dark);
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
    text-align: center;
}

.alert-error {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

/* Styles for build page */
.build-container {
    display: flex;
    gap: 30px;
    margin-top: 30px;
    flex-wrap: wrap; /* Allow wrapping on smaller screens */
}

.build-parts-section {
    flex: 2; /* Takes up more space */
    min-width: 300px; /* Minimum width */
}

.build-summary-section {
    flex: 1; /* Takes up less space */
    min-width: 250px; /* Minimum width */
    background-color: var(--background-secondary);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    height: fit-content; /* Adjust height based on content */
}

.build-parts-section h2, .build-summary-section h2 {
    margin-top: 0;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.build-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background-color: var(--background-primary);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    margin-bottom: 15px;
    transition: box-shadow 0.3s ease;
}

.build-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.build-item-details {
    display: flex;
    align-items: center;
    gap: 15px;
}

.build-item-details img {
    width: 50px;
    height: 50px;
    object-fit: contain;
    border-radius: 4px;
}

.build-item-details span {
    font-weight: bold;
    color: var(--text-primary);
}

.build-item-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.build-item-actions .remove-btn {
    background-color: var(--danger-color);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 5px 10px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
}

.build-item-actions .remove-btn:hover {
    background-color: var(--danger-color-dark);
}

.build-item-actions .view-details-btn {
    background-color: var(--secondary-color);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 5px 10px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
    text-decoration: none;
}

.build-item-actions .view-details-btn:hover {
    background-color: var(--secondary-color-dark);
}

.summary-total {
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--text-primary);
    margin-top: 20px;
    text-align: right;
}

.save-build-btn {
    display: block;
    width: 100%;
    padding: 12px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 20px;
}

.save-build-btn:hover {
    background-color: var(--primary-color-dark);
}

/* Specific styles for the X symbol */
.remove-symbol {
    font-size: 1.2rem;
    line-height: 1;
    cursor: pointer;
    color: var(--text-secondary);
    padding: 5px;
    transition: color 0.3s ease;
}

.remove-symbol:hover {
    color: var(--danger-color);
}
</style>