<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action'])) { echo json_encode(['ok'=>false]); exit; }
$action = $input['action'];

$conn->query("CREATE TABLE IF NOT EXISTS wishlist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  product_name VARCHAR(255),
  price DECIMAL(10,2),
  image VARCHAR(255),
  added_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if ($action === 'add') {
    $product = $input['product'] ?? null; if (!$product) { echo json_encode(['ok'=>false]); exit; }
    $user_id = $_SESSION['user_id'] ?? null;
    // avoid duplicates on server: check
    if ($user_id) {
      $chk = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_name = ? LIMIT 1");
      $chk->bind_param("is", $user_id, $product['name']); $chk->execute(); $r = $chk->get_result();
      if ($r->num_rows > 0) { echo json_encode(['ok'=>true,'note'=>'exists']); exit; }
    }
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_name, price, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $user_id, $product['name'], $product['price'], $product['image']);
    $stmt->execute();
    echo json_encode(['ok'=>true]); exit;
} elseif ($action === 'remove') {
    $product = $input['product'] ?? null; if (!$product) { echo json_encode(['ok'=>false]); exit; }
    $user_id = $_SESSION['user_id'] ?? null;
    if ($user_id) {
      $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_name = ? LIMIT 1");
      $stmt->bind_param("is", $user_id, $product['name']); $stmt->execute();
    }
    echo json_encode(['ok'=>true]); exit;
}
echo json_encode(['ok'=>false]);
