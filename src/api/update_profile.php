<?php
// api/update_profile.php

require __DIR__ . '/../vendor/autoload.php';
require 'config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// --- 1. จัดการ CORS ให้สมบูรณ์ ---
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS'); // เพิ่ม OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// ดักจับ Preflight Request (ถ้าเป็น OPTIONS ให้จบการทำงานทันที)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- 2. ฟังก์ชันดึง Header แบบรองรับทุก Server (Apache/Nginx) ---
function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx มักมาท่านี้
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    }
    elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

// --- 3. ตรวจสอบ Token ---
$authHeader = getAuthorizationHeader();

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบ Token หรือรูปแบบไม่ถูกต้อง']);
    exit;
}

try {
    $secretKey = $_ENV['JWT_SECRET'];
    $decoded = JWT::decode($matches[1], new Key($secretKey, $_ENV['JWT_ALGO']));
    $userId = $decoded->uid;
    
    // --- 4. รับข้อมูล ---
    $data = json_decode(file_get_contents("php://input"), true);

    // ป้องกัน XSS เบื้องต้น (Optional: ถ้าต้องการเก็บ HTML ให้เอา strip_tags ออก)
    $firstName = isset($data['first_name']) ? strip_tags($data['first_name']) : '';
    $lastName  = isset($data['last_name'])  ? strip_tags($data['last_name'])  : '';
    $phone     = isset($data['phone'])      ? strip_tags($data['phone'])      : '';
    $address   = isset($data['address'])    ? strip_tags($data['address'])    : '';

    // --- 5. บันทึกข้อมูล (Upsert) ---
    // ใช้ ON DUPLICATE KEY UPDATE คือถ้ามี user_id นี้อยู่แล้วให้ Update ถ้าไม่มีให้ Insert
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
    // กรณี Token หมดอายุ หรือ Error อื่นๆ
    http_response_code(401); // หรือ 500 แล้วแต่กรณี แต่ส่วนใหญ่ JWT decode fail คือ 401
    echo json_encode([
        'status' => 'error', 
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
?>