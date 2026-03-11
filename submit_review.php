<?php
// Database connection
include 'db_config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $part_id = $_POST['part_id'] ?? null;
    $user_id = $_POST['user_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $review_text = $_POST['review_text'] ?? '';
    
    if ($part_id && $user_id && $rating) {
        // Insert review into the database
        $stmt = $pdo->prepare("INSERT INTO reviews (part_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)");
        $stmt->execute([$part_id, $user_id, $rating, $review_text]);
        
        // Redirect back to the parts page
        header('Location: parts.php');
        exit;
    } else {
        echo 'Error: Missing required fields.';
    }
}

// Close database connection
$pdo = null;
?>