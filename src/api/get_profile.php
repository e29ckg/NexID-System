<?php
// api/get_profile.php

require __DIR__ . '/../vendor/autoload.php';
require 'config.php';
require 'auth.php'; // 👈 บรรทัดเดียวจบ เรื่อง Auth

header('Content-Type: application/json');

try {
    // ดึงข้อมูลโดยใช้ $userId จาก auth.php
    $sql = "SELECT u.username, u.role, p.* FROM users u
            LEFT JOIN user_profiles p ON u.id = p.user_id
            WHERE u.id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user) {
        echo json_encode(['status' => 'success', 'data' => $user]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูล']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>