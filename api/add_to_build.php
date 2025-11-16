<?php
header('Content-Type: application/json');
require_once '../db_config.php';
require_once '../compatibility.php';

$data = json_decode(file_get_contents('php://input'), true);
$buildId = $data['build_id'] ?? null;
$partId = $data['part_id'] ?? null;

if (!$partId) {
    echo json_encode(['success' => false, 'message' => 'Part ID required']);
    exit;
}

if ($buildId === 'new' || !$buildId) {
    execute("INSERT INTO builds (user_id, build_name) VALUES (?, ?)", [1, 'My Build']);
    $buildId = lastInsertId();
}

$part = fetchOne("SELECT * FROM parts WHERE part_id = ?", [$partId]);

if (!$part) {
    echo json_encode(['success' => false, 'message' => 'Part not found']);
    exit;
}

$existing = fetchOne("SELECT * FROM build_parts WHERE build_id = ? AND category = ?", 
                     [$buildId, $part['category']]);

if ($existing) {
    execute("UPDATE build_parts SET part_id = ? WHERE build_part_id = ?", 
           [$partId, $existing['build_part_id']]);
} else {
    execute("INSERT INTO build_parts (build_id, part_id, category) VALUES (?, ?, ?)",
           [$buildId, $partId, $part['category']]);
}

$buildParts = fetchAll("SELECT bp.*, p.* 
    FROM build_parts bp 
    JOIN parts p ON bp.part_id = p.part_id 
    WHERE bp.build_id = ?", [$buildId]);

$compatibility = checkCompatibility($buildParts);

echo json_encode([
    'success' => true, 
    'build_id' => $buildId,
    'compatibility' => $compatibility
]);
?>
