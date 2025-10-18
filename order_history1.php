<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}






$user_id = $_SESSION['user_id'];

// ✅ Adjust this query to match your actual order_history columns
$sql = "SELECT product_name, price, status, order_date 
        FROM order_history 
        WHERE user_id = ? 
        ORDER BY order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Order History</h2>";

if ($result->num_rows > 0) {
  echo "<table border='1' cellpadding='10'>
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Status</th>
            <th>Order Date</th>
          </tr>";
  while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['product_name']}</td>
            <td>{$row['price']}</td>
            <td>{$row['status']}</td>
            <td>{$row['order_date']}</td>
          </tr>";
  }
  echo "</table>";
} else {
  echo "<p>No order history found.</p>";
}

$stmt->close();
$conn->close();
?>
