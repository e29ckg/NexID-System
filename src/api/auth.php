<?php
// api/auth.php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// 1. จัดการ CORS และ Preflight Request (OPTIONS)
// (ใส่ตรงนี้ทีเดียว ทุกไฟล์ที่เรียกใช้จะได้ CORS ด้วย)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(200);
    exit;
}

// 2. ฟังก์ชันดึง Header (รองรับ Nginx/Apache)
function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
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

// 3. เริ่มกระบวนการตรวจสอบ
$authHeader = getAuthorizationHeader();
$userId = null;
$userRole = null;

// ถ้าไม่มี Header ส่งมา
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Token not found or invalid format']);
    exit; // ⛔ หยุดทำงานทันที ห้ามไปต่อ
}

$jwt = $matches[1];

try {
    // 4. ถอดรหัส Token
    // ต้องแน่ใจว่า $_ENV ถูกโหลดมาจาก config.php แล้วก่อนเรียกไฟล์นี้
    $secretKey = $_ENV['JWT_SECRET'];
    $decoded = JWT::decode($jwt, new Key($secretKey, $_ENV['JWT_ALGO']));

    // 5. ส่งค่ากลับไปให้ไฟล์หลักใช้งาน (Global Variables)
    $userId = $decoded->uid;
    $userRole = $decoded->role;

} catch (Exception $e) {
    // 6. ถ้า Token หมดอายุ หรือ ปลอม
    http_response_code(401);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Unauthorized: ' . $e->getMessage() // Token expired etc.
    ]);
    exit; // ⛔ หยุดทำงานทันที
}

// ถ้ามาถึงตรงนี้ได้ แปลว่า Token ถูกต้องครับ!
?>