<?php
session_start();
include 'db_connect.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.html"); exit; }
$user_id = $_SESSION['user_id'];
// ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  product_name VARCHAR(255),
  price DECIMAL(10,2),
  image VARCHAR(255),
  quantity INT DEFAULT 1,
  added_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Cart - Paysmallsmall</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body style="background:#f9f9f9">

<nav class="navbar navbar-default" style="background:#2b6;border:none">
  <div class="container-fluid">

    <div class="navbar-header">
      <!-- Mobile toggle button -->
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#mainNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>

      <a class="navbar-brand" href="product.html" style="color:#fff">Paysmallsmall</a>
    </div>

    <!-- Collapsible menu -->
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="nav navbar-nav navbar-right">
        <li><a href="product.html" style="color:#fff">Shop</a></li>
        <li><a href="cart.php" style="color:#fff">Cart</a></li>
        <li><a href="wishlist.php" style="color:#fff">Wishlist</a></li>
        <li><a href="history.php" style="color:#fff">History</a></li>
        <li><a href="account.php" style="color:#fff">Account</a></li>
      </ul>
    </div>

  </div>
</nav>

<div class="container" style="margin-top:20px;">
  <h2>Your Cart</h2>
  <div class="row">
<?php
$stmt = $conn->prepare("SELECT id, product_name, price, image, quantity, added_on FROM cart WHERE user_id = ? ORDER BY added_on DESC");
$stmt->bind_param("i",$user_id); $stmt->execute(); $res = $stmt->get_result();
if($res->num_rows === 0){
  echo '<div class="col-sm-12"><p>No items in your cart.</p></div>';
} else {
  while($r = $res->fetch_assoc()){
    echo '<div class="col-sm-4"><div class="panel panel-default"><div class="panel-body text-center">';
    echo '<img src="'.htmlspecialchars($r['image']).'" style="width:100%;height:180px;object-fit:cover;border-radius:6px;">';
    echo '<h4>'.htmlspecialchars($r['product_name']).'</h4>';
    echo '<p><strong>₦'.number_format($r['price'],2).'</strong></p>';
    echo '<form method="post" action="remove_cart_item.php"><input type="hidden" name="id" value="'.(int)$r['id'].'"><button class="btn btn-danger btn-sm">Remove</button></form>';
    echo '</div></div></div>';
  }
}
?>
  </div>
</div>
</body></html>
