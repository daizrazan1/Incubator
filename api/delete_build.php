<?php
require_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $buildId = $_POST['build_id'] ?? null;
    
    if ($buildId) {
        // Verify ownership
        $build = fetchOne("SELECT user_id FROM builds WHERE build_id = ?", [$buildId]);
        $currentUser = getCurrentUser();
        if ($build && $currentUser && $build['user_id'] == $currentUser['user_id']) {
            // Delete build parts first
            execute("DELETE FROM build_parts WHERE build_id = ?", [$buildId]);
            // Delete build
            execute("DELETE FROM builds WHERE build_id = ?", [$buildId]);
        }
    }
}

header('Location: /profile.php');
exit;
?>
