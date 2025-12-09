<?php
// api/get_profile.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // เปิดให้เรียกข้าม Domain ได้ (สำหรับ Dev)
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require __DIR__ . '/../vendor/autoload.php';
require 'config.php'; // ไฟล์นี้โหลด .env และเชื่อม Database แล้ว

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// 1. ดึง Token จาก Header ที่ชื่อ Authorization
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// ตรวจสอบรูปแบบ "Bearer <token>"
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบ Token ยืนยันตัวตน']);
    exit;
}

$jwt = $matches[1]; // ได้ตัว Token เพียวๆ มาแล้ว

try {
    // 2. ถอดรหัส Token (Decode)
    // ใช้ Secret Key จาก .env เพื่อตรวจสอบลายเซ็น
    $secretKey = $_ENV['JWT_SECRET']; 
    $decoded = JWT::decode($jwt, new Key($secretKey, $_ENV['JWT_ALGO']));

    // ดึง User ID ที่ซ่อนอยู่ใน Token ออกมา
    $userId = $decoded->uid;

    // 3. Query ฐานข้อมูล (JOIN ตาราง)
    // ดึงข้อมูลพื้นฐานจาก users และข้อมูลส่วนตัวจาก user_profiles
    $sql = "SELECT 
                u.id, u.username, u.role, u.created_at,
                p.first_name, p.last_name, p.phone, p.address
            FROM users u
            LEFT JOIN user_profiles p ON u.id = p.user_id
            WHERE u.id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user) {
        // ส่งข้อมูลกลับเป็น JSON
        echo json_encode([
            'status' => 'success',
            'data' => $user
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลผู้ใช้']);
    }

} catch (Exception $e) {
    // ถ้า Token หมดอายุ หรือ ถูกแก้ไข จะเข้าเงื่อนไขนี้
    http_response_code(401);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Token ไม่ถูกต้องหรือหมดอายุ',
        'debug' => $e->getMessage()
    ]);
}
?>