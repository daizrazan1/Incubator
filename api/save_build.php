<?php
require_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buildId = $_POST['build_id'] ?? null;
    $buildName = $_POST['build_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $isPublic = isset($_POST['is_public']) ? 1 : 0;
    $userId = 1;
    
    if ($buildId) {
        execute("UPDATE builds SET build_name = ?, description = ?, is_public = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE build_id = ?",
               [$buildName, $description, $isPublic, $buildId]);
        header("Location: /build.php?build_id=$buildId");
    } else {
        execute("INSERT INTO builds (user_id, build_name, description, is_public) VALUES (?, ?, ?, ?)",
               [$userId, $buildName, $description, $isPublic]);
        $newBuildId = lastInsertId();
        header("Location: /build.php?build_id=$newBuildId");
    }
} else {
    header('Location: /profile.php');
}
?>
