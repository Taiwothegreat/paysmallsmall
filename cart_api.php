<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');


$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action'])) {
    echo json_encode(['ok'=>false,'msg'=>'bad request']); exit;
}
$action = $input['action'];
// try ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  product_name VARCHAR(255),
  price DECIMAL(10,2),
  image VARCHAR(255),
  quantity INT DEFAULT 1,
  added_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if ($action === 'add') {
    $product = $input['product'] ?? null;
    if (!$product) { echo json_encode(['ok'=>false]); exit; }
    $user_id = $_SESSION['user_id'] ?? null;
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_name, price, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $user_id, $product['name'], $product['price'], $product['image']);
    $stmt->execute();
    echo json_encode(['ok'=>true]);
    exit;
} elseif ($action === 'remove') {
    // optional: implement remove by product name
    $product = $input['product'] ?? null;
    if (!$product) { echo json_encode(['ok'=>false]); exit; }
    $user_id = $_SESSION['user_id'] ?? null;
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_name = ? LIMIT 1");
    $stmt->bind_param("is", $user_id, $product['name']);
    $stmt->execute();
    echo json_encode(['ok'=>true]);
    exit;
}

echo json_encode(['ok'=>false]);
