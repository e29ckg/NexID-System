<?php
// api/update_profile.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST'); // หรือ PUT
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require __DIR__ . '/../vendor/autoload.php';
require 'config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// 1. ตรวจสอบ Token (เหมือนเดิมทุกไฟล์ที่ต้อง Login)
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบ Token']);
    exit;
}

try {
    $secretKey = $_ENV['JWT_SECRET'];
    $decoded = JWT::decode($matches[1], new Key($secretKey, $_ENV['JWT_ALGO']));
    $userId = $decoded->uid; // ได้ ID ผู้ใช้มาแล้ว

    // 2. รับข้อมูลที่ส่งมาแก้ไข
    $data = json_decode(file_get_contents("php://input"), true);

    // กำหนดตัวแปร (ใช้ Null Coalescing Operator ?? เผื่อไม่ได้ส่งบางค่ามา)
    $firstName = $data['first_name'] ?? '';
    $lastName  = $data['last_name'] ?? '';
    $phone     = $data['phone'] ?? '';
    $address   = $data['address'] ?? '';

    // 3. บันทึกข้อมูล (Upsert: Insert ถ้าไม่มี, Update ถ้ามี)
    // เทคนิค: user_id เป็น Primary Key ดังนั้นถ้าเจอ user_id ซ้ำ มันจะไปทำส่วน UPDATE แทน
    $sql = "INSERT INTO user_profiles (user_id, first_name, last_name, phone, address) 
            VALUES (:uid, :fname, :lname, :phone, :addr)
            ON DUPLICATE KEY UPDATE 
            first_name = :fname, last_name = :lname, phone = :phone, address = :addr";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':uid'   => $userId,
        ':fname' => $firstName,
        ':lname' => $lastName,
        ':phone' => $phone,
        ':addr'  => $address
    ]);

    echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อย']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>