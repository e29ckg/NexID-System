<?php
// api/login.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require __DIR__ . '/../vendor/autoload.php';
require 'config.php'; // โหลด Database และ .env

use Firebase\JWT\JWT;

// รับข้อมูล JSON จาก Frontend
$data = json_decode(file_get_contents("php://input"), true);

// ตรวจสอบว่าส่ง username และ password มาครบไหม
if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอก Username และ Password']);
    exit;
}

$username = $data['username'];
$password = $data['password'];

try {
    // 1. ค้นหา User ในฐานข้อมูล
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // 2. ตรวจสอบรหัสผ่าน (Verify Hash)
    // ถ้ารหัสที่กรอกมา ตรงกับ Hash ในฐานข้อมูล
    if ($user && password_verify($password, $user['password_hash'])) {
        
        // 3. เตรียมข้อมูลสำหรับสร้าง Token (Payload)
        $issuedAt = time();
        $expirationTime = $issuedAt + (int)$_ENV['JWT_EXPIRATION']; // หมดอายุตามที่ตั้งใน .env (เช่น 3600 วิ)
        
        $payload = [
            'iss' => $_ENV['BASE_URL'], // ผู้ออก Token (Issuer)
            'iat' => $issuedAt,         // เวลาที่ออก (Issued At)
            'exp' => $expirationTime,   // เวลาหมดอายุ (Expiration Time)
            'uid' => $user['id'],       // User ID (สำคัญ! เอาไว้ใช้ดึงข้อมูลส่วนตัว)
            'role' => $user['role']     // Role (เอาไว้เช็คสิทธิ์แอดมิน)
        ];

        // 4. สร้าง JWT Token (Encode)
        $jwt = JWT::encode($payload, $_ENV['JWT_SECRET'], $_ENV['JWT_ALGO']);

        echo json_encode([
            'status' => 'success',
            'message' => 'เข้าสู่ระบบสำเร็จ',
            'token' => $jwt,
            'user' => [ // ส่งข้อมูลเบื้องต้นกลับไป (เผื่อ Frontend อยากใช้เลย)
                'username' => $user['username'],
                'role' => $user['role']
            ]
        ]);

    } else {
        // รหัสผิด หรือ ไม่พบ Username
        http_response_code(401); // Unauthorized
        echo json_encode(['status' => 'error', 'message' => 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>