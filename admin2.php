<?php
require_once 'db_config.php';
$pageTitle = 'Admin Visual Dashboard - PC Part Sniper';

$db = getDB();

// Revenue and Traffic Chart Data (Last 30 points for the wave effect)
$revenueData = fetchAll("
    SELECT DATE(clicked_at) as date, COUNT(*) * 0.05 as amount 
    FROM click_tracking 
    WHERE clicked_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(clicked_at)
    ORDER BY date ASC
");

$trafficData = fetchAll("
    SELECT 
        DATE_FORMAT(visited_at, '%Y-%m') as month_date, 
        COUNT(*) as counts 
    FROM page_visits 
    WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY month_date
    ORDER BY month_date ASC
");

// Extract max value for scaling the Y-axis
$maxTraffic = 0;
foreach ($trafficData as $data) {
    if ($data['counts'] > $maxTraffic) $maxTraffic = $data['counts'];
}
$maxTraffic = $maxTraffic ?: 10; // Avoid division by zero
?>

<style>
:root {
    --admin-bg: #2D2D2D;
    --card-bg: #363636;
    --stroke-blue: #4A90E2;
    --text-blue: #7FB5FF;
    --amazon-orange: #FF9900;
    --bestbuy-yellow: #FFF200;
    --walmart-blue: #0071CE;
}

body {
    background-color: var(--admin-bg);
}

.admin2-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 1rem;
    color: #fff;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.chart-section {
    border: 2px solid var(--stroke-blue);
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    position: relative;
    background: var(--card-bg);
}

.chart-title {
    color: var(--text-blue);
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 1rem;
    position: absolute;
    top: 10px;
    left: 20px;
}

.wave-container {
    height: 120px;
    width: 100%;
    margin-top: 2rem;
    position: relative;
    overflow: hidden;
}

.grid-lines {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.grid-line {
    border-bottom: 1px solid rgba(255,255,255,0.1);
    height: 1px;
    width: 100%;
}

.svg-wave {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 2rem;
}

.ranking-card, .messages-card {
    border: 2px solid var(--stroke-blue);
    border-radius: 25px;
    padding: 2rem;
    background: var(--card-bg);
}

.section-label {
    text-align: center;
    color: var(--text-blue);
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 1.5rem;
}

/* Seller Ranking Styles */
.donut-container {
    width: 180px;
    height: 180px;
    margin: 0 auto 2rem;
    position: relative;
}

.donut-chart {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.seller-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.seller-item {
    display: flex;
    align-items: center;
    padding: 0.8rem 1rem;
    border-radius: 12px;
    border: 2px solid;
    background: rgba(255,255,255,0.05);
}

.seller-rank {
    margin-right: 10px;
    font-weight: bold;
}

.seller-icon {
    width: 24px;
    height: 24px;
    margin-right: 10px;
    background: #fff;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000;
    font-weight: bold;
    font-size: 0.8rem;
}

/* Messages Styles */
.message-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.message-item {
    border: 2px solid var(--stroke-blue);
    border-radius: 15px;
    padding: 1rem;
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    background: rgba(255,255,255,0.02);
}

.avatar-circle {
    width: 50px;
    height: 50px;
    background: #666;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.avatar-circle svg {
    width: 30px;
    height: 30px;
    fill: #ccc;
}

.chart-container {
    display: flex;
    height: 100%;
    width: 100%;
    position: relative;
    padding-left: 40px; /* Space for Y-axis */
    padding-bottom: 30px; /* Space for X-axis */
}

.y-axis {
    position: absolute;
    left: 0;
    top: 0;
    height: calc(100% - 30px);
    width: 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    font-size: 0.7rem;
    color: rgba(255,255,255,0.5);
    text-align: right;
    padding-right: 5px;
}

.x-axis {
    position: absolute;
    left: 40px;
    bottom: 0;
    width: calc(100% - 40px);
    height: 30px;
    display: flex;
    justify-content: space-between;
    font-size: 0.7rem;
    color: rgba(255,255,255,0.5);
    padding-top: 5px;
}
</style>

<div class="admin2-container">
    <!-- Revenue Chart -->
    <div class="chart-section">
        <div class="chart-title">Revenue</div>
        <div class="wave-container">
            <div class="grid-lines">
                <div class="grid-line"></div>
                <div class="grid-line"></div>
                <div class="grid-line"></div>
                <div class="grid-line"></div>
            </div>
            <svg class="svg-wave" viewBox="0 0 1000 100" preserveAspectRatio="none">
                <path d="M0,50 Q100,20 200,60 T400,40 T600,70 T800,30 T1000,50" fill="none" stroke="var(--text-blue)" stroke-width="3" />
            </svg>
        </div>
    </div>

    <!-- User Traffic Chart -->
    <div class="chart-section" style="height: 300px;">
        <div class="chart-title">User traffic</div>
        <div class="chart-container">
            <!-- Y-Axis Labels -->
            <div class="y-axis">
                <span><?php echo $maxTraffic; ?></span>
                <span><?php echo round($maxTraffic * 0.75); ?></span>
                <span><?php echo round($maxTraffic * 0.5); ?></span>
                <span><?php echo round($maxTraffic * 0.25); ?></span>
                <span>0</span>
            </div>

            <div class="wave-container" style="height: 100%; margin-top: 0;">
                <div class="grid-lines">
                    <div class="grid-line"></div>
                    <div class="grid-line"></div>
                    <div class="grid-line"></div>
                    <div class="grid-line"></div>
                </div>
                <svg class="svg-wave" viewBox="0 0 1000 100" preserveAspectRatio="none">
                    <?php 
                    $points = "";
                    $count = count($trafficData);
                    if ($count > 0) {
                        $widthPerPoint = 1000 / (max($count - 1, 1));
                        foreach ($trafficData as $i => $data) {
                            $x = $i * $widthPerPoint;
                            $y = 100 - (($data['counts'] / $maxTraffic) * 100);
                            $points .= ($i === 0 ? "M" : " L") . "$x,$y";
                        }
                    } else {
                        $points = "M0,100 L1000,100";
                    }
                    ?>
                    <path d="<?php echo $points; ?>" fill="none" stroke="var(--text-blue)" stroke-width="3" />
                </svg>
            </div>

            <!-- X-Axis Labels -->
            <div class="x-axis">
                <?php foreach ($trafficData as $data): ?>
                    <span><?php echo date('M', strtotime($data['month_date'] . '-01')); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="bottom-grid">
        <!-- Seller Ranking -->
        <div class="ranking-card">
            <div class="section-label">Seller ranking</div>
            <div class="donut-container">
                <svg class="donut-chart" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.915" fill="transparent" stroke="#111" stroke-width="4"></circle>
                    <circle cx="18" cy="18" r="15.915" fill="transparent" stroke="var(--amazon-orange)" stroke-width="4" stroke-dasharray="60 40" stroke-dashoffset="0"></circle>
                    <circle cx="18" cy="18" r="15.915" fill="transparent" stroke="var(--bestbuy-yellow)" stroke-width="4" stroke-dasharray="25 75" stroke-dashoffset="-60"></circle>
                    <circle cx="18" cy="18" r="15.915" fill="transparent" stroke="var(--walmart-blue)" stroke-width="4" stroke-dasharray="15 85" stroke-dashoffset="-85"></circle>
                </svg>
            </div>
            
            <div class="seller-list">
                <?php 
                $sellers = [
                    ['name' => 'Amazon', 'color' => 'var(--amazon-orange)', 'icon' => 'a'],
                    ['name' => 'Best Buy', 'color' => 'var(--bestbuy-yellow)', 'icon' => 'B'],
                    ['name' => 'Walmart', 'color' => 'var(--walmart-blue)', 'icon' => 'W']
                ];
                foreach ($sellers as $i => $s): ?>
                    <div class="seller-item" style="border-color: <?php echo $s['color']; ?>; color: <?php echo $s['color']; ?>;">
                        <span class="seller-rank"><?php echo $i+1; ?>.</span>
                        <div class="seller-icon" style="background: <?php echo $s['color']; ?>; color: #fff;"><?php echo $s['icon']; ?></div>
                        <span><?php echo $s['name']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Contacted Messages -->
        <div class="messages-card">
            <div class="section-label">Contacted Messages</div>
            <div class="message-list">
                <?php if (empty($messages)): ?>
                    <p style="text-align: center; color: var(--text-secondary);">No messages yet.</p>
                <?php else: ?>
                    <?php foreach($messages as $msg): ?>
                        <div class="message-item">
                            <div class="avatar-circle">
                                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            </div>
                            <div class="message-content" style="flex-grow: 1;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <span style="font-weight: bold; color: var(--text-blue);"><?php echo htmlspecialchars($msg['username'] ?? 'Guest'); ?></span>
                                    <span style="font-size: 0.8rem; color: rgba(255,255,255,0.5);"><?php echo date('M d, H:i', strtotime($msg['created_at'])); ?></span>
                                </div>
                                <div style="font-weight: 600; font-size: 0.9rem; margin-bottom: 5px;"><?php echo htmlspecialchars($msg['subject']); ?></div>
                                <div style="font-size: 0.85rem; color: rgba(255,255,255,0.8); line-height: 1.4;">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
