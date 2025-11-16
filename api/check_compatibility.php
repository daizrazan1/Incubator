<?php
header('Content-Type: application/json');
require_once '../db_config.php';
require_once '../compatibility.php';

$buildId = $_GET['build_id'] ?? null;

if (!$buildId) {
    echo json_encode(['success' => false, 'message' => 'Build ID required']);
    exit;
}

$buildParts = fetchAll("SELECT bp.*, p.* 
    FROM build_parts bp 
    JOIN parts p ON bp.part_id = p.part_id 
    WHERE bp.build_id = :id", [':id' => $buildId]);

if (empty($buildParts)) {
    echo json_encode([
        'success' => true,
        'compatibility' => [
            'overall_compatible' => true,
            'issues' => [],
            'part_compatibility' => []
        ]
    ]);
    exit;
}

$compatibility = checkCompatibility($buildParts);

echo json_encode([
    'success' => true,
    'compatibility' => $compatibility
]);
?>
