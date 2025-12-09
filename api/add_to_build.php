<?php
header('Content-Type: application/json');
require_once '../db_config.php';

$data = json_decode(file_get_contents('php://input'), true);
$buildId = $data['build_id'] ?? null;
$partId = $data['part_id'] ?? null;

if (!$partId) {
    echo json_encode(['success' => false, 'message' => 'Part ID required']);
    exit;
}

if ($buildId === 'new' || !$buildId || $buildId === 0) {
    $currentUser = getCurrentUser();
    $db = getDB();
    $buildName = 'My Build';
    $buildDesc = '';
    $isPublic = 0;
    
    if ($currentUser && isset($currentUser['user_id']) && $currentUser['user_id'] > 0) {
        // Logged-in user: create build with their user_id
        $userId = $currentUser['user_id'];
        $stmt = $db->prepare("INSERT INTO builds (user_id, build_name, description, is_public) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $userId, $buildName, $buildDesc, $isPublic);
    } else {
        // Non-logged-in user: create temporary build with NULL user_id
        $stmt = $db->prepare("INSERT INTO builds (build_name, description, is_public) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $buildName, $buildDesc, $isPublic);
    }
    
    if ($stmt->execute()) {
        $buildId = $db->insert_id;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create build: ' . $db->error]);
        exit;
    }
}

$part = fetchOne("SELECT category FROM parts WHERE part_id = ?", [$partId]);

if (!$part) {
    echo json_encode(['success' => false, 'message' => 'Part not found']);
    exit;
}

$existing = fetchOne("SELECT build_part_id FROM build_parts WHERE build_id = ? AND category = ?", 
                     [$buildId, $part['category']]);

if ($existing) {
    execute("UPDATE build_parts SET part_id = ? WHERE build_part_id = ?", 
           [$partId, $existing['build_part_id']]);
} else {
    execute("INSERT INTO build_parts (build_id, part_id, category) VALUES (?, ?, ?)",
           [$buildId, $partId, $part['category']]);
}

echo json_encode([
    'success' => true, 
    'build_id' => $buildId
]);
?>
