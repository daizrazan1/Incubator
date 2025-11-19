<?php
require_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $buildId = $_POST['build_id'] ?? null;
    
    if ($buildId) {
        // Verify ownership
        $build = fetchOne("SELECT user_id, is_public FROM builds WHERE build_id = ?", [$buildId]);
        $currentUser = getCurrentUser();
        if ($build && $currentUser && $build['user_id'] == $currentUser['user_id']) {
            $newStatus = $build['is_public'] ? 0 : 1;
            execute("UPDATE builds SET is_public = ? WHERE build_id = ?", [$newStatus, $buildId]);
        }
    }
}

header('Location: /profile.php');
exit;
?>
