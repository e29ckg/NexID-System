<?php
// api/update_profile.php

require __DIR__ . '/../vendor/autoload.php';
require 'config.php'; // โหลด Database และ .env

// ✅ เรียกพี่ยามมาตรวจบัตร (ถ้าไม่ผ่าน มันจะ exit ในไฟล์นี้เลย)
require 'auth.php'; 

// --- จุดนี้มั่นใจได้แล้วว่า User Login แล้วแน่นอน ---
// ตัวแปร $userId จะถูกส่งมาจาก auth.php ให้ใช้ได้เลย

header('Content-Type: application/json');

// ... (ส่วนรับข้อมูล JSON เหมือนเดิม) ...
$data = json_decode(file_get_contents("php://input"), true);

$firstName = isset($data['first_name']) ? strip_tags($data['first_name']) : '';
$lastName  = isset($data['last_name'])  ? strip_tags($data['last_name'])  : '';
$phone     = isset($data['phone'])      ? strip_tags($data['phone'])      : '';
$address   = isset($data['address'])    ? strip_tags($data['address'])    : '';

try {
    // ... (SQL Update เหมือนเดิม) ...
    $sql = "INSERT INTO user_profiles (user_id, first_name, last_name, phone, address) 
            VALUES (:uid, :fname, :lname, :phone, :addr)
            ON DUPLICATE KEY UPDATE 
            first_name = :fname, last_name = :lname, phone = :phone, address = :addr";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':uid'   => $userId, // ใช้ตัวแปรจาก auth.php ได้เลย
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