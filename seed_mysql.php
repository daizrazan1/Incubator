<?php
require_once 'db_config.php';

echo "Seeding MySQL database with sample data...\n\n";

// Insert demo user
execute("INSERT IGNORE INTO users (user_id, username, email, password_hash) VALUES (?, ?, ?, ?)", 
    [1, 'demo_user', 'demo@pcpartsniper.com', password_hash('demo123', PASSWORD_DEFAULT)]);
echo "✓ Created demo user (username: demo_user, password: demo123)\n";

// Insert merchants
execute("INSERT IGNORE INTO merchants (merchant_id, merchant_name, website_url) VALUES (?, ?, ?)", 
    [1, 'Amazon', 'https://amazon.com']);
execute("INSERT IGNORE INTO merchants (merchant_id, merchant_name, website_url) VALUES (?, ?, ?)", 
    [2, 'Newegg', 'https://newegg.com']);
execute("INSERT IGNORE INTO merchants (merchant_id, merchant_name, website_url) VALUES (?, ?, ?)", 
    [3, 'Micro Center', 'https://microcenter.com']);
echo "✓ Created merchants\n";

$parts = [
    ['AMD Ryzen 9 7950X', 'CPU', 'AMD', '7950X', 549.99, 'AM5', null, 170, null, '16 cores, 32 threads, 5.7GHz boost'],
    ['Intel Core i9-14900K', 'CPU', 'Intel', 'i9-14900K', 589.99, 'LGA1700', null, 125, null, '24 cores, 32 threads, 6.0GHz boost'],
    ['NVIDIA RTX 4090', 'GPU', 'NVIDIA', 'RTX 4090', 1599.99, null, null, 450, null, '24GB GDDR6X, 16384 CUDA cores'],
    ['AMD Radeon RX 7900 XTX', 'GPU', 'AMD', 'RX 7900 XTX', 999.99, null, null, 355, null, '24GB GDDR6, RDNA 3'],
    ['ASUS ROG STRIX X670E-E', 'Motherboard', 'ASUS', 'X670E-E', 449.99, 'AM5', 'ATX', null, null, 'PCIe 5.0, DDR5, WiFi 6E'],
    ['MSI MPG Z790 CARBON', 'Motherboard', 'MSI', 'Z790 CARBON', 399.99, 'LGA1700', 'ATX', null, null, 'PCIe 5.0, DDR5, WiFi 6E'],
    ['Corsair Vengeance DDR5 32GB', 'RAM', 'Corsair', 'Vengeance DDR5', 129.99, null, 'DDR5', null, null, '32GB (2x16GB) 6000MHz CL36'],
    ['Samsung 990 PRO 2TB', 'Storage', 'Samsung', '990 PRO', 169.99, null, 'M.2', null, null, 'PCIe 4.0 NVMe, 7450MB/s read'],
    ['Corsair RM1000x', 'PSU', 'Corsair', 'RM1000x', 189.99, null, 'ATX', null, 1000, '80+ Gold, Fully Modular, 1000W'],
    ['NZXT H7 Flow', 'Case', 'NZXT', 'H7 Flow', 129.99, null, 'ATX', null, null, 'Mid Tower, Mesh Front Panel'],
    ['Noctua NH-D15', 'Cooling', 'Noctua', 'NH-D15', 109.99, null, null, null, null, 'Dual Tower CPU Cooler'],
    ['AMD Ryzen 7 7800X3D', 'CPU', 'AMD', '7800X3D', 449.99, 'AM5', null, 120, null, '8 cores, 16 threads, 3D V-Cache'],
    ['Intel Core i5-14600K', 'CPU', 'Intel', 'i5-14600K', 319.99, 'LGA1700', null, 125, null, '14 cores, 20 threads'],
    ['NVIDIA RTX 4070 Ti', 'GPU', 'NVIDIA', 'RTX 4070 Ti', 799.99, null, null, 285, null, '12GB GDDR6X'],
    ['G.Skill Trident Z5 RGB 64GB', 'RAM', 'G.Skill', 'Trident Z5', 249.99, null, 'DDR5', null, null, '64GB (2x32GB) 6400MHz'],
];

foreach ($parts as $i => $part) {
    execute("INSERT IGNORE INTO parts (part_id, part_name, category, brand, model, price, socket, form_factor, tdp, wattage, specs) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
           [
               $i + 1,
               $part[0],
               $part[1],
               $part[2],
               $part[3],
               $part[4],
               $part[5],
               $part[6],
               $part[7],
               $part[8],
               $part[9]
           ]);
}
echo "✓ Created " . count($parts) . " parts\n";

// Create part prices
for ($i = 1; $i <= count($parts); $i++) {
    $merchantId = (($i - 1) % 3) + 1;
    $basePrice = $parts[$i - 1][4];
    $variance = rand(-50, 50);
    $price = max(1, $basePrice + $variance);
    
    execute("INSERT IGNORE INTO part_prices (part_id, merchant_id, price, url, in_stock) 
            VALUES (?, ?, ?, ?, ?)",
           [$i, $merchantId, $price, "https://example.com/part/$i", 1]);
}
echo "✓ Created part prices\n";

// Create sample build
execute("INSERT IGNORE INTO builds (build_id, user_id, build_name, description, is_public) 
        VALUES (?, ?, ?, ?, ?)",
        [1, 1, 'Ultimate Gaming Beast', 'High-end 4K gaming powerhouse', 1]);

execute("INSERT IGNORE INTO build_parts (build_id, part_id, category) VALUES (?, ?, ?)", [1, 1, 'CPU']);
execute("INSERT IGNORE INTO build_parts (build_id, part_id, category) VALUES (?, ?, ?)", [1, 3, 'GPU']);
execute("INSERT IGNORE INTO build_parts (build_id, part_id, category) VALUES (?, ?, ?)", [1, 5, 'Motherboard']);
execute("INSERT IGNORE INTO build_parts (build_id, part_id, category) VALUES (?, ?, ?)", [1, 7, 'RAM']);
execute("INSERT IGNORE INTO build_parts (build_id, part_id, category) VALUES (?, ?, ?)", [1, 8, 'Storage']);
execute("INSERT IGNORE INTO build_parts (build_id, part_id, category) VALUES (?, ?, ?)", [1, 9, 'PSU']);
execute("INSERT IGNORE INTO build_parts (build_id, part_id, category) VALUES (?, ?, ?)", [1, 10, 'Case']);
execute("INSERT IGNORE INTO build_parts (build_id, part_id, category) VALUES (?, ?, ?)", [1, 11, 'Cooling']);
echo "✓ Created sample build\n";

// Create sample reviews
execute("INSERT IGNORE INTO reviews (part_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)",
    [1, 1, 5, 'Incredible performance! Running all my games at max settings.']);
execute("INSERT IGNORE INTO reviews (part_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)",
    [3, 1, 5, 'The RTX 4090 is a beast. Worth every penny for 4K gaming.']);
echo "✓ Created sample reviews\n";

echo "\n✅ Database seeded successfully!\n";
?>
