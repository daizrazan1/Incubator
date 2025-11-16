<?php
require_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buildId = $_POST['build_id'] ?? null;
    $buildName = $_POST['build_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $isPublic = isset($_POST['is_public']) ? 1 : 0;
    $userId = 1;
    
    if ($buildId) {
        execute("UPDATE builds SET build_name = :name, description = :desc, is_public = :public, updated_at = CURRENT_TIMESTAMP 
                WHERE build_id = :id",
               [':name' => $buildName, ':desc' => $description, ':public' => $isPublic, ':id' => $buildId]);
        header("Location: /build.php?build_id=$buildId");
    } else {
        execute("INSERT INTO builds (user_id, build_name, description, is_public) VALUES (:user_id, :name, :desc, :public)",
               [':user_id' => $userId, ':name' => $buildName, ':desc' => $description, ':public' => $isPublic]);
        $db = getDB();
        $newBuildId = $db->lastInsertRowID();
        header("Location: /build.php?build_id=$newBuildId");
    }
} else {
    header('Location: /profile.php');
}
?>
