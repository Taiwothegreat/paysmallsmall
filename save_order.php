<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if(!$input){ echo json_encode(['ok'=>false,'msg'=>'no input']); exit; }

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){ echo json_encode(['ok'=>false,'msg'=>'not logged in']); exit; }

// create table if missing
$conn->query("CREATE TABLE IF NOT EXISTS order_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  product_name VARCHAR(255),
  price DECIMAL(10,2),
  plan_type VARCHAR(50),
  duration INT,
  amount_paid DECIMAL(10,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$product = $input['product'] ?? '';
$price = floatval($input['price'] ?? 0);
$plan = $input['plan'] ?? '';
$duration = intval($input['duration'] ?? 0);
$amount_paid = floatval($input['amount_paid'] ?? 0);

$stmt = $conn->prepare("INSERT INTO order_history (user_id, product_name, price, plan_type, duration, amount_paid) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isdsi d", $user_id, $product, $price, $plan, $duration, $amount_paid);
// fix bind type (PHP won't accept 'isdsi d'), so use correct types:
$stmt = $conn->prepare("INSERT INTO order_history (user_id, product_name, price, plan_type, duration, amount_paid) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isdsid", $user_id, $product, $price, $plan, $duration, $amount_paid);

if($stmt->execute()){
  echo json_encode(['ok'=>true]);
} else {
  echo json_encode(['ok'=>false,'msg'=>$stmt->error]);
}
