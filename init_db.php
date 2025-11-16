<?php
$db = new SQLite3('pcpartsniper.db');

$db->exec('CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

$db->exec('CREATE TABLE IF NOT EXISTS merchants (
    merchant_id INTEGER PRIMARY KEY AUTOINCREMENT,
    merchant_name TEXT NOT NULL,
    website_url TEXT,
    affiliate_enabled INTEGER DEFAULT 1
)');

$db->exec('CREATE TABLE IF NOT EXISTS parts (
    part_id INTEGER PRIMARY KEY AUTOINCREMENT,
    part_name TEXT NOT NULL,
    category TEXT NOT NULL,
    brand TEXT,
    model TEXT,
    price REAL,
    socket TEXT,
    form_factor TEXT,
    tdp INTEGER,
    wattage INTEGER,
    specs TEXT,
    image_url TEXT,
    is_used INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

$db->exec('CREATE TABLE IF NOT EXISTS part_prices (
    price_id INTEGER PRIMARY KEY AUTOINCREMENT,
    part_id INTEGER,
    merchant_id INTEGER,
    price REAL NOT NULL,
    url TEXT,
    in_stock INTEGER DEFAULT 1,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (part_id) REFERENCES parts(part_id),
    FOREIGN KEY (merchant_id) REFERENCES merchants(merchant_id)
)');

$db->exec('CREATE TABLE IF NOT EXISTS price_history (
    history_id INTEGER PRIMARY KEY AUTOINCREMENT,
    part_id INTEGER,
    merchant_id INTEGER,
    price REAL NOT NULL,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (part_id) REFERENCES parts(part_id),
    FOREIGN KEY (merchant_id) REFERENCES merchants(merchant_id)
)');

$db->exec('CREATE TABLE IF NOT EXISTS builds (
    build_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    build_name TEXT NOT NULL,
    description TEXT,
    is_public INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)');

$db->exec('CREATE TABLE IF NOT EXISTS build_parts (
    build_part_id INTEGER PRIMARY KEY AUTOINCREMENT,
    build_id INTEGER,
    part_id INTEGER,
    category TEXT,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (build_id) REFERENCES builds(build_id),
    FOREIGN KEY (part_id) REFERENCES parts(part_id)
)');

$db->exec('CREATE TABLE IF NOT EXISTS reviews (
    review_id INTEGER PRIMARY KEY AUTOINCREMENT,
    part_id INTEGER,
    user_id INTEGER,
    rating INTEGER CHECK(rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (part_id) REFERENCES parts(part_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)');

$db->exec('CREATE TABLE IF NOT EXISTS click_tracking (
    click_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    part_id INTEGER,
    merchant_id INTEGER,
    clicked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (part_id) REFERENCES parts(part_id),
    FOREIGN KEY (merchant_id) REFERENCES merchants(merchant_id)
)');

$db->exec('CREATE TABLE IF NOT EXISTS support_tickets (
    ticket_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    subject TEXT NOT NULL,
    message TEXT NOT NULL,
    status TEXT DEFAULT "open",
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)');

echo "Database initialized successfully!\n";

$db->close();
?>
