<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shop - PaySmallSmall</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body style="padding:20px;">

<h2>Shop</h2>
<div class="row">
<?php
$result = $conn->query("SELECT * FROM products");
while ($row = $result->fetch_assoc()) {
?>
  <div class="col-sm-4" style="margin-bottom:20px;">
    <div class="panel panel-default">
      <div class="panel-heading text-center"><b><?php echo $row['name']; ?></b></div>
      <div class="panel-body text-center">
        <img src="<?php echo $row['image']; ?>" style="max-width:100%; height:200px;">
        <p>₦<?php echo number_format($row['price'], 2); ?></p>
        <a href="product.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">View Product</a>
      </div>
    </div>
  </div>
<?php } ?>
</div>

</body>
</html>
