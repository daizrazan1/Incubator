<?php
header('Content-Type: application/json');
require_once '../db_config.php';

$data = json_decode(file_get_contents('php://input'), true);
$buildPartId = $data['build_part_id'] ?? null;

if (!$buildPartId) {
    echo json_encode(['success' => false, 'message' => 'Build part ID required']);
    exit;
}

execute("DELETE FROM build_parts WHERE build_part_id = ?", [$buildPartId]);

echo json_encode(['success' => true]);
?>
