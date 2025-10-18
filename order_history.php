<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

$user_id = $_SESSION['user_id'];

// ---- Simulate a Successful Payment ----
if (isset($_POST['simulate_payment'])) {
  $product_name = "PaySmallSmall Sewing Machine";
  $price = 125000;
  $plan_type = "3-Month Plan";
  $duration = "3 Months";
  $date = date("Y-m-d H:i:s");

  $sql_insert = "INSERT INTO order_history (user_id, product_name, price, plan_type, duration, date) 
                 VALUES (?, ?, ?, ?, ?, ?)";
  $stmt_insert = $conn->prepare($sql_insert);
  $stmt_insert->bind_param("isdsss", $user_id, $product_name, $price, $plan_type, $duration, $date);

  if ($stmt_insert->execute()) {
    $success_message = "✅ Payment simulated successfully! Order added to your history.";
  } else {
    $error_message = "❌ Error simulating payment: " . $stmt_insert->error;
  }
  $stmt_insert->close();
}

// ---- Remove Individual Order ----
if (isset($_POST['remove_order']) && isset($_POST['order_id'])) {
  $order_id = intval($_POST['order_id']);
  $sql_delete = "DELETE FROM order_history WHERE id = ? AND user_id = ?";
  $stmt_delete = $conn->prepare($sql_delete);
  $stmt_delete->bind_param("ii", $order_id, $user_id);

  if ($stmt_delete->execute()) {
    $success_message = "🗑️ Order removed successfully.";
  } else {
    $error_message = "❌ Failed to remove order: " . $stmt_delete->error;
  }
  $stmt_delete->close();
}

// ---- Fetch Order History ----
$sql = "SELECT id, product_name, price, plan_type, duration, date 
        FROM order_history 
        WHERE user_id = ? 
        ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order History - PaySmallSmall</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 3 -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

  <style>
    body {
      background-color: #f7f7f7;
      padding-top: 60px;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }
    .history-container {
      background: #fff;
      border-radius: 6px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      padding: 25px;
      max-width: 950px;
      margin: 0 auto;
    }
    h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 30px;
    }
    .table > thead > tr > th {
      background-color: #2c3e50;
      color: white;
      text-align: center;
    }
    .table > tbody > tr:hover {
      background-color: #f1f1f1;
    }
    .btn-simulate {
      background-color: #2c3e50;
      color: #fff;
      border-radius: 5px;
      padding: 10px 15px;
      border: none;
    }
    .btn-simulate:hover {
      background-color: #1a242f;
      color: #fff;
    }
    .btn-remove {
      background-color: #c9302c;
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: 5px 10px;
    }
    .btn-remove:hover {
      background-color: #ac2925;
      color: #fff;
    }
    .alert {
      text-align: center;
      font-size: 15px;
    }
    .navbar-brand {
      font-weight: bold;
      color: #fff !important;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <a class="navbar-brand" href="product.html">PaySmallSmall</a>
      </div>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="account.php"><span class="glyphicon glyphicon-user"></span> Account</a></li>
        <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container history-container">
    <h2>Your Order History</h2>

    <?php
    if (isset($success_message)) {
      echo '<div class="alert alert-success">' . $success_message . '</div>';
    } elseif (isset($error_message)) {
      echo '<div class="alert alert-danger">' . $error_message . '</div>';
    }
    ?>

    <form method="POST" style="text-align:center; margin-bottom: 25px;">
      <button type="submit" name="simulate_payment" class="btn btn-simulate">
        <span class="glyphicon glyphicon-shopping-cart"></span> Simulate Payment
      </button>
    </form>

    <?php
    if ($result->num_rows > 0) {
      echo '<div class="table-responsive">';
      echo '<table class="table table-striped table-bordered">';
      echo '<thead><tr>
              <th>Product Name</th>
              <th>Price (₦)</th>
              <th>Plan Type</th>
              <th>Duration</th>
              <th>Date</th>
              <th>Action</th>
            </tr></thead><tbody>';

      while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['product_name']}</td>
                <td>{$row['price']}</td>
                <td>{$row['plan_type']}</td>
                <td>{$row['duration']}</td>
                <td>{$row['date']}</td>
                <td>
                  <form method='POST' style='display:inline;'>
                    <input type='hidden' name='order_id' value='{$row['id']}'>
                    <button type='submit' name='remove_order' class='btn btn-remove btn-xs'>
                      <span class='glyphicon glyphicon-trash'></span> Remove
                    </button>
                  </form>
                </td>
              </tr>";
      }

      echo '</tbody></table></div>';
    } else {
      echo '<p class="text-center text-muted">No order history found.</p>';
    }

    $stmt->close();
    $conn->close();
    ?>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
