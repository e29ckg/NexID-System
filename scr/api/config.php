<?php
// api/config.php

// 1. โหลด Library ทั้งหมดผ่าน Composer (รวมถึงตัวอ่าน .env)
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// 2. โหลดค่าจากไฟล์ .env (ที่อยู่ Folder ก่อนหน้า 1 ขั้น)
// ใช้ try-catch เผื่อกรณีลืมสร้างไฟล์ .env
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (Exception $e) {
    // ถ้าไม่เจอไฟล์ .env ให้หยุดทำงานและแจ้งเตือน
    die(json_encode([
        'status' => 'error', 
        'message' => 'Configuration file (.env) not found!'
    ]));
}

// 3. เตรียมตัวแปรสำหรับเชื่อมต่อฐานข้อมูล
$host = $_ENV['DB_HOST'];     // ใน Docker ค่านี้จะเป็นชื่อ Service คือ "db"
$db   = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$port = $_ENV['DB_PORT'] ?? 3306;
$charset = 'utf8mb4';

// Data Source Name (DSN) สตริงระบุพิกัดฐานข้อมูล
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

// 4. ตั้งค่า Option ของ PDO (สำคัญมาก)
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // ถ้า error ให้โยน Exception ออกมาทันที
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // ดึงข้อมูลออกมาเป็น Array Key (ชื่อคอลัมน์)
    PDO::ATTR_EMULATE_PREPARES   => false,                  // ป้องกัน SQL Injection ได้ดีกว่า
];

try {
    // 5. สร้างการเชื่อมต่อ (Connection)
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // (Optional) เปิดบรรทัดนี้เพื่อเช็คว่าเชื่อมติดไหม เวลาเทสเสร็จให้ปิดไว้
    // echo "Connected successfully"; 

} catch (\PDOException $e) {
    // 6. ถ้าเชื่อมไม่ติด (เช่น รหัสผิด, ลืมเปิด Docker)
    // ส่ง JSON error กลับไป Frontend แทนการโชว์ Error ดิบๆ
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}
?>