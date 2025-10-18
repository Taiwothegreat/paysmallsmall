<?php
session_start();
include 'db_connect.php';

if (empty($_SESSION['cart'])) {
  echo "<p>No items in cart. <a href='shop.php'>Go shopping</a></p>";
  exit;
}

// Assume user is logged in — replace with actual login system later
$user_email = "demo@paysmallsmall.org";

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $id) {
  $product = $conn->query("SELECT * FROM products WHERE id='$id'")->fetch_assoc();
  $total += $product['price'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Save order
  $conn->query("INSERT INTO orders (user_email, total_amount) VALUES ('$user_email', '$total')");
  $order_id = $conn->insert_id;

  // Save each product
  foreach ($_SESSION['cart'] as $id) {
    $conn->query("INSERT INTO order_items (order_id, product_id) VALUES ('$order_id', '$id')");
  }

  // Clear cart
  unset($_SESSION['cart']);

  echo "<p style='color:green;'>Order placed successfully!</p>";
  echo "<p><a href='history.php'>View Order History</a></p>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout - PaySmallSmall</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body style="padding:20px;">
<h2>Checkout</h2>

<p><strong>Email:</strong> <?php echo $user_email; ?></p>
<p><strong>Total:</strong> ₦<?php echo number_format($total, 2); ?></p>

<form method="POST">
  <button type="submit" class="btn btn-success">Confirm Order</button>
  <a href="cart.php" class="btn btn-default">Go Back</a>
</form>

</body>
</html>
