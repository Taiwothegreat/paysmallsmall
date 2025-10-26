<?php
session_start();
include 'db_connect.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.html"); exit; }
$user_id = $_SESSION['user_id'];
$conn->query("CREATE TABLE IF NOT EXISTS wishlist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  product_name VARCHAR(255),
  price DECIMAL(10,2),
  image VARCHAR(255),
  added_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
?>
<!DOCTYPE html><html><head><meta charset="utf-8">
<title>Wishlist</title><link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body style="background:#f9f9f9">
<!--<nav class="navbar navbar-default" style="background:#2b6;border:none"><div class="container-fluid">
  <div class="navbar-header">
    <a class="navbar-brand" href="product.html" style="color:#fff">Paysmallsmall</a></div>
    <ul class="nav navbar-nav navbar-right"><li><a href="product.html" style="color:#fff">Shop</a></li>
    <li><a href="cart.php" style="color:#fff">Cart</a></li>
    <li><a href="wishlist.php" style="color:#fff">Wishlist</a></li>
    <li><a href="history.php" style="color:#fff">History</a></li>
    <li><a href="account.php" style="color:#fff">Account</a></li></ul></div></nav>-->
<div class="container" style="margin-top:20px;">
  <h2>Your Wishlist</h2><div class="row">
<?php
$stmt = $conn->prepare("SELECT id, product_name, price, image FROM wishlist WHERE user_id = ? ORDER BY added_on DESC");
$stmt->bind_param("i",$user_id); $stmt->execute(); $res = $stmt->get_result();
if($res->num_rows===0){ echo '<div class="col-sm-12"><p>Your wishlist is empty.</p></div>'; }
else {
  while($r = $res->fetch_assoc()){
    echo '<div class="col-sm-4"><div class="panel panel-default"><div class="panel-body text-center">';
    echo '<img src="'.htmlspecialchars($r['image']).'" style="width:100%;height:180px;object-fit:cover;border-radius:6px;">';
    echo '<h4>'.htmlspecialchars($r['product_name']).'</h4>';
    echo '<p><strong>₦'.number_format($r['price'],2).'</strong></p>';
    echo '<form method="post" action="move_wishlist_to_cart.php" style="display:inline-block;"><input type="hidden" name="id" value="'.(int)$r['id'].'"><button class="btn btn-success btn-sm">Move to Cart</button></form> ';
    echo '<form method="post" action="remove_wishlist_item.php" style="display:inline-block;"><input type="hidden" name="id" value="'.(int)$r['id'].'"><button class="btn btn-danger btn-sm">Remove</button></form>';
    echo '</div></div></div>';
  }
}
?>

</div></div>
<!-- Menu Section Below Carousel -->
<div class="container text-center" style="margin-top:20px;">
  <div class="row">

    <div class="col-sm-2 col-xs-4" style="margin-bottom:15px;">
      <a href="product.html" style="text-decoration:none; color:#333;">
        <i class="fa fa-shopping-bag fa-2x" style="color:#5cb85c;"></i>
        <div style="margin-top:5px;">Shop</div>
      </a>
    </div>

    <div class="col-sm-2 col-xs-4" style="margin-bottom:15px;">
      <a href="cart.php" style="text-decoration:none; color:#333;">
        <i class="fa fa-shopping-cart fa-2x" style="color:#f0ad4e;"></i>
        <div style="margin-top:5px;">Cart</div>
      </a>
    </div>

    <!--<div class="col-sm-2 col-xs-4" style="margin-bottom:15px;">
      <a href="wishlist.php" style="text-decoration:none; color:#333;">
        <i class="fa fa-heart fa-2x" style="color:#d9534f;"></i>
        <div style="margin-top:5px;">Wishlist</div>
      </a>
    </div>-->

    <div class="col-sm-2 col-xs-4" style="margin-bottom:15px;">
      <a href="history.php" style="text-decoration:none; color:#333;">
        <i class="fa fa-history fa-2x" style="color:#5bc0de;"></i>
        <div style="margin-top:5px;">History</div>
      </a>
    </div>

    <div class="col-sm-2 col-xs-4" style="margin-bottom:15px;">
      <a href="account.php" style="text-decoration:none; color:#333;">
        <i class="fa fa-user fa-2x" style="color:#428bca;"></i>
        <div style="margin-top:5px;">Account</div>
      </a>
    </div>

    <div class="col-sm-2 col-xs-4" style="margin-bottom:15px;">
      <a href="logout.php" style="text-decoration:none; color:#333;">
        <i class="fa fa-sign-out fa-2x" style="color:#777;"></i>
        <div style="margin-top:5px;">Logout</div>
      </a>
    </div>

  </div>
</div>
</body></html>
