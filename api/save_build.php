<?php
require_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    $currentUser = getCurrentUser();
    if (!$currentUser || !isset($currentUser['user_id']) || $currentUser['user_id'] === null) {
        header('Location: /login.php');
        exit;
    }
    
    $buildId = $_POST['build_id'] ?? null;
    $buildName = $_POST['build_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $isPublic = isset($_POST['is_public']) ? 1 : 0;
    $userId = $currentUser['user_id'];
    
    if ($buildId) {
        execute("UPDATE builds SET user_id = ?, build_name = ?, description = ?, is_public = ? WHERE build_id = ?",
               [$userId, $buildName, $description, $isPublic, $buildId]);
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
