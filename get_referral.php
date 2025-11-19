<?php
require_once 'config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid ID']));
}

$referral_id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM referrals WHERE id = ?");
    $stmt->execute([$referral_id]);
    $referral = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($referral) {
        echo json_encode(['success' => true, 'referral' => $referral]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Not found']);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>