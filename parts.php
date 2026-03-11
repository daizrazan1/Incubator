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
    <h1 class="section-title"><?php echo __('browse_parts'); ?></h1>

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
                        <div class="price"><?php echo formatCurrency($part['min_price'] ?? $part['price']); ?></div>
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
.filters {
    background: var(--secondary);
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.filters h3 {
    margin-bottom: 1rem;
    color: var(--accent);
}

.filter-group {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.filter-group select,
.filter-group input {
    padding: 0.75rem;
    border: 2px solid var(--primary);
    background: var(--primary);
    color: var(--text);
    border-radius: 5px;
    flex: 1;
    min-width: 150px;
}

.filter-group select:focus,
.filter-group input:focus {
    outline: none;
    border-color: var(--accent);
}
</style>
