<?php
header('Content-Type: application/json');
require 'config.php';

// รับข้อมูล JSON จาก Axios
$data = json_decode(file_get_contents("php://input"), true);

if(isset($data['username']) && isset($data['password'])) {
    $username = $data['username'];
    // Hash Password เสมอ!
    $password = password_hash($data['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        
        echo json_encode(['status' => 'success', 'message' => 'สมัครสมาชิกสำเร็จ']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Username นี้ถูกใช้ไปแล้ว']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
}
?>