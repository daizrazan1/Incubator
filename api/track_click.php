<?php
header('Content-Type: application/json');
require_once '../db_config.php';

$data = json_decode(file_get_contents('php://input'), true);
$partId = $data['part_id'] ?? null;
$merchantId = $data['merchant_id'] ?? null;

if ($partId && $merchantId) {
    execute("INSERT INTO click_tracking (user_id, part_id, merchant_id) VALUES (1, :part_id, :merchant_id)",
           [':part_id' => $partId, ':merchant_id' => $merchantId]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
