<?php
require __DIR__ . '/../vendor/autoload.php'; // โหลด Composer Autoload

// โหลดค่าจาก .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../'); 
// ชี้ path ไปที่ folder root ที่เก็บไฟล์ .env
$dotenv->load();

// การเรียกใช้
$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// ... (code PDO ส่วนที่เหลือเหมือนเดิม)
?>