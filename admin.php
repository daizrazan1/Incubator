<?php
require_once 'db_config.php';
$pageTitle = 'Admin Dashboard - PC Part Sniper';

$db = getDB();
$db->query("CREATE TABLE IF NOT EXISTS page_visits (
    visit_id INT AUTO_INCREMENT PRIMARY KEY,
    page_url VARCHAR(255) NOT NULL,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_page (page_url),
    INDEX idx_visited (visited_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$revenuePerClick = 0.05;

$totalClicks = fetchOne("SELECT COUNT(*) as total FROM click_tracking");
$totalRevenue = ($totalClicks['total'] ?? 0) * $revenuePerClick;

$revenueByPart = fetchAll("
    SELECT p.part_name, p.category, COUNT(ct.click_id) as clicks, 
           COUNT(ct.click_id) * ? as revenue
    FROM click_tracking ct
    JOIN parts p ON ct.part_id = p.part_id
    GROUP BY ct.part_id, p.part_name, p.category
    ORDER BY clicks DESC
    LIMIT 10
", [$revenuePerClick]);

$sellerRanking = fetchAll("
    SELECT m.merchant_name, m.website_url, COUNT(ct.click_id) as clicks,
           COUNT(ct.click_id) * ? as revenue
    FROM click_tracking ct
    JOIN merchants m ON ct.merchant_id = m.merchant_id
    GROUP BY ct.merchant_id, m.merchant_name, m.website_url
    ORDER BY clicks DESC
", [$revenuePerClick]);

$messages = fetchAll("
    SELECT st.*, u.username, u.email
    FROM support_tickets st
    LEFT JOIN users u ON st.user_id = u.user_id
    ORDER BY st.created_at DESC
    LIMIT 50
");

$totalVisits = fetchOne("SELECT COUNT(*) as total FROM page_visits");
$todayVisits = fetchOne("SELECT COUNT(*) as total FROM page_visits WHERE DATE(visited_at) = CURDATE()");
$uniqueVisitors = fetchOne("SELECT COUNT(DISTINCT ip_address) as total FROM page_visits");

$trafficByPage = fetchAll("
    SELECT page_url, COUNT(*) as visits
    FROM page_visits
    GROUP BY page_url
    ORDER BY visits DESC
    LIMIT 10
");

$recentTraffic = fetchAll("
    SELECT DATE(visited_at) as date, COUNT(*) as visits
    FROM page_visits
    WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(visited_at)
    ORDER BY date DESC
");

$totalUsers = fetchOne("SELECT COUNT(*) as total FROM users");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $ticketId = $_POST['ticket_id'] ?? 0;
    $newStatus = $_POST['new_status'] ?? 'open';
    execute("UPDATE support_tickets SET status = ? WHERE ticket_id = ?", [$newStatus, $ticketId]);
    header("Location: admin.php#messages");
    exit;
}

include 'includes/header.php';
?>

<style>
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.admin-header {
    margin-bottom: 2rem;
}

.admin-header h1 {
    font-size: 2rem;
    color: var(--text);
    margin-bottom: 0.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
}

.stat-card h3 {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
}

.stat-card .value {
    font-size: 2rem;
    font-weight: bold;
    color: var(--highlight);
}

.stat-card .value.revenue {
    color: #4CAF50;
}

.admin-section {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.admin-section h2 {
    color: var(--text);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--accent);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border);
}

.data-table th {
    background: var(--bg);
    color: var(--text-secondary);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
}

.data-table tr:hover {
    background: var(--bg);
}

.data-table td {
    color: var(--text);
}

.rank-badge {
    display: inline-block;
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    border-radius: 50%;
    font-weight: bold;
    font-size: 0.9rem;
}

.rank-1 { background: #FFD700; color: #000; }
.rank-2 { background: #C0C0C0; color: #000; }
.rank-3 { background: #CD7F32; color: #fff; }
.rank-default { background: var(--accent); color: #fff; }

.message-card {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.message-subject {
    font-weight: bold;
    color: var(--text);
    font-size: 1.1rem;
}

.message-meta {
    color: var(--text-secondary);
    font-size: 0.85rem;
}

.message-body {
    color: var(--text);
    margin: 1rem 0;
    padding: 1rem;
    background: var(--card-bg);
    border-radius: 6px;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-open { background: #FF9800; color: #fff; }
.status-in_progress { background: #2196F3; color: #fff; }
.status-resolved { background: #4CAF50; color: #fff; }
.status-closed { background: #9E9E9E; color: #fff; }

.status-form {
    display: inline-flex;
    gap: 0.5rem;
    align-items: center;
}

.status-form select {
    padding: 0.5rem;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
}

.traffic-bar {
    height: 20px;
    background: var(--accent);
    border-radius: 4px;
    transition: width 0.3s;
}

.nav-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--border);
    padding-bottom: 1rem;
}

.nav-tab {
    padding: 0.75rem 1.5rem;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px 8px 0 0;
    color: var(--text);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
}

.nav-tab:hover,
.nav-tab.active {
    background: var(--accent);
    color: #fff;
    border-color: var(--accent);
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .data-table {
        display: block;
        overflow-x: auto;
    }
    
    .message-header {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<div class="admin-container">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <p style="color: var(--text-secondary);">Monitor revenue, seller performance, messages, and traffic</p>
    </div>

    <div class="nav-tabs">
        <a href="#overview" class="nav-tab active">Overview</a>
        <a href="#revenue" class="nav-tab">Revenue</a>
        <a href="#sellers" class="nav-tab">Seller Ranking</a>
        <a href="#messages" class="nav-tab">Messages</a>
        <a href="#traffic" class="nav-tab">Traffic</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Revenue</h3>
            <div class="value revenue">$<?php echo number_format($totalRevenue, 2); ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Clicks</h3>
            <div class="value"><?php echo number_format($totalClicks['total'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Page Views</h3>
            <div class="value"><?php echo number_format($totalVisits['total'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3>Today's Visits</h3>
            <div class="value"><?php echo number_format($todayVisits['total'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3>Unique Visitors</h3>
            <div class="value"><?php echo number_format($uniqueVisitors['total'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Users</h3>
            <div class="value"><?php echo number_format($totalUsers['total'] ?? 0); ?></div>
        </div>
    </div>

    <div id="revenue" class="admin-section">
        <h2>Revenue by Part</h2>
        <p style="color: var(--text-secondary); margin-bottom: 1rem;">
            Revenue is calculated at $<?php echo $revenuePerClick; ?> per click
        </p>
        
        <?php if (empty($revenueByPart)): ?>
            <p style="color: var(--text-secondary);">No click data available yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Part Name</th>
                        <th>Category</th>
                        <th>Clicks</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($revenueByPart as $index => $part): ?>
                        <tr>
                            <td>
                                <span class="rank-badge <?php echo $index < 3 ? 'rank-' . ($index + 1) : 'rank-default'; ?>">
                                    <?php echo $index + 1; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($part['part_name']); ?></td>
                            <td><?php echo htmlspecialchars($part['category']); ?></td>
                            <td><?php echo number_format($part['clicks']); ?></td>
                            <td style="color: #4CAF50; font-weight: bold;">
                                $<?php echo number_format($part['revenue'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div id="sellers" class="admin-section">
        <h2>Seller/Website Ranking</h2>
        <p style="color: var(--text-secondary); margin-bottom: 1rem;">
            Ranked by total clicks and revenue generated
        </p>
        
        <?php if (empty($sellerRanking)): ?>
            <p style="color: var(--text-secondary);">No seller data available yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Seller/Merchant</th>
                        <th>Website</th>
                        <th>Total Clicks</th>
                        <th>Revenue Generated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sellerRanking as $index => $seller): ?>
                        <tr>
                            <td>
                                <span class="rank-badge <?php echo $index < 3 ? 'rank-' . ($index + 1) : 'rank-default'; ?>">
                                    <?php echo $index + 1; ?>
                                </span>
                            </td>
                            <td style="font-weight: bold;"><?php echo htmlspecialchars($seller['merchant_name']); ?></td>
                            <td>
                                <?php if ($seller['website_url']): ?>
                                    <a href="<?php echo htmlspecialchars($seller['website_url']); ?>" target="_blank" style="color: var(--accent);">
                                        <?php echo htmlspecialchars($seller['website_url']); ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary);">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($seller['clicks']); ?></td>
                            <td style="color: #4CAF50; font-weight: bold;">
                                $<?php echo number_format($seller['revenue'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div id="messages" class="admin-section">
        <h2>Contact Messages</h2>
        <p style="color: var(--text-secondary); margin-bottom: 1rem;">
            Messages received from users via the contact form
        </p>
        
        <?php if (empty($messages)): ?>
            <p style="color: var(--text-secondary);">No messages yet.</p>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <div class="message-card">
                    <div class="message-header">
                        <div>
                            <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                            <div class="message-meta">
                                From: <?php echo $message['username'] ? htmlspecialchars($message['username']) . ' (' . htmlspecialchars($message['email']) . ')' : 'Guest User'; ?>
                                | <?php echo date('M d, Y g:i A', strtotime($message['created_at'])); ?>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span class="status-badge status-<?php echo $message['status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $message['status'])); ?>
                            </span>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="ticket_id" value="<?php echo $message['ticket_id']; ?>">
                                <select name="new_status">
                                    <option value="open" <?php echo $message['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                    <option value="in_progress" <?php echo $message['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="resolved" <?php echo $message['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    <option value="closed" <?php echo $message['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                                    Update
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="message-body">
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="traffic" class="admin-section">
        <h2>Website Traffic</h2>
        
        <h3 style="margin: 1.5rem 0 1rem;">Last 7 Days</h3>
        <?php if (empty($recentTraffic)): ?>
            <p style="color: var(--text-secondary);">No traffic data available yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Visits</th>
                        <th>Graph</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $maxVisits = max(array_column($recentTraffic, 'visits') ?: [1]);
                    foreach ($recentTraffic as $day): 
                        $percentage = ($day['visits'] / $maxVisits) * 100;
                    ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                            <td><?php echo number_format($day['visits']); ?></td>
                            <td style="width: 40%;">
                                <div class="traffic-bar" style="width: <?php echo $percentage; ?>%;"></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h3 style="margin: 1.5rem 0 1rem;">Top Pages</h3>
        <?php if (empty($trafficByPage)): ?>
            <p style="color: var(--text-secondary);">No page data available yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Page URL</th>
                        <th>Visits</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trafficByPage as $page): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($page['page_url']); ?></td>
                            <td><?php echo number_format($page['visits']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.nav-tab').forEach(tab => {
    tab.addEventListener('click', function(e) {
        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>

<?php include 'includes/footer.php'; ?>
