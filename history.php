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
      padding: 50px;
      max-width: 950px;
      margin: 0 auto;
    }
    h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 30px;
      font-weight: 600;
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

    /* Navbar Adjustments */
    .navbar-brand img {
      max-width: 120px;
      height: auto;
      margin-top: -10px;
    }
    @media (max-width: 767px) {
      .navbar-brand img {
        width: 100px;
        margin-top: -5px;
      }
      .navbar-brand {
        margin-top: 0;
      }
      .navbar p {
        font-size: 11px;
        text-align: center !important;
      }
    }

    /* Responsive History Container */
    @media (max-width: 768px) {
      .history-container {
        padding: 20px;
        margin-top: 20px;
      }
      h2 {
        font-size: 20px;
      }
      .table th, .table td {
        font-size: 12px;
      }
    }

    /* Footer Responsive Fixes */
    #pure-footer {
      background: #111;
      color: #fff;
      padding: 40px 15px 20px 15px;
      box-sizing: border-box;
    }
    #pure-footer .footer-col {
      margin-bottom: 30px;
    }
    @media (max-width: 768px) {
      #pure-footer {
        text-align: center;
      }
      #pure-footer .footer-col {
        flex: 1 1 100%;
        min-width: unset;
      }
      #pure-footer img {
        margin: 0 auto 15px;
        display: block;
      }
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand" href="#" style="font-weight:bold; color:#0b63b8;">
          <img src="./assets/paysmallsmall_logo-removebg-preview.png" alt="logo">
        </a>
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#mainNav">
          <span class="sr-only">Toggle nav</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
      </div>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="nav navbar-nav navbar-right">
          <li><a href="index.html">Home</a></li>
          <li><a href="#">Sewing Machine</a></li>
          <li><a href="contactus.html">Contact Us</a></li>
          <li><a href="aboutus.html">About Us</a></li>
          <li><a href="pickup.html">Enquiry/Pickup Center</a></li>
        </ul>
      </div>
    </div>
    <p style="font-size:13px; text-align:right; margin-right:20px; color:white;">
      <i>feel free to call us</i> &nbsp;&nbsp;
      <u>Toll free call:</u> <strong>+234 801 234 5678</strong> &nbsp;&nbsp;
      <u>info@paysmallsmall.org</u>
    </p>
  </nav>

  <div class="container history-container">
    <h2>Your Order History</h2>

    <?php
    if (isset($success_message)) {
      echo '<div class="alert alert-success">' . $success_message . '</div>';
    } elseif (isset($error_message)) {
      echo '<div class="alert alert-danger">' . $error_message . '</div>';
    }

    if ($result->num_rows > 0) {
      echo '<div class="table-responsive">';
      echo '<table class="table table-striped table-bordered">';
      echo '<thead><tr>
              <th>Product Name</th>
              <th>Price (₦)</th>
              <th>Plan Type</th>
              <th>Duration</th>
              <th>Date</th>
            </tr></thead><tbody>';

      while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['product_name']}</td>
                <td>{$row['price']}</td>
                <td>{$row['plan_type']}</td>
                <td>{$row['duration']}</td>
                <td>{$row['date']}</td>
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

  <!-- Responsive Footer -->
  <div id="pure-footer">
    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; margin-bottom:30px;">
      <div class="footer-col" style="flex:1 1 300px;">
        <img src="./assets/paysmallsmall_logo-removebg-preview.png" alt="Logo" style="width:120px; margin-bottom:15px;">
        <p style="font-size:14px; color:#ccc;">At <strong>Pure Finest Inc.</strong>, we empower communities through innovation and partnership.</p>
      </div>

      <div class="footer-col" style="flex:1 1 200px;">
        <h4 style="margin-bottom:15px;">Quick Links</h4>
        <ul style="list-style:none; padding:0;">
          <li><a href="#" style="color:#ccc; text-decoration:none;">Home</a></li>
          <li><a href="#" style="color:#ccc; text-decoration:none;">About Us</a></li>
          <li><a href="#" style="color:#ccc; text-decoration:none;">Products</a></li>
          <li><a href="#" style="color:#ccc; text-decoration:none;">Contact</a></li>
          <li><a href="#" style="color:#ccc; text-decoration:none;">FAQs</a></li>
        </ul>
      </div>

      <div class="footer-col" style="flex:1 1 250px;">
        <h4 style="margin-bottom:15px;">Contact Us</h4>
        <p style="color:#ccc;">📍 24 Unity Avenue, Lagos, Nigeria</p>
        <p style="color:#ccc;">📞 +234 801 234 5678</p>
        <p style="color:#ccc;">✉️ support@purefinest.com</p>
      </div>
    </div>

    <div style="border-top:1px solid #333; text-align:center; padding-top:15px;">
      <p style="font-size:13px; color:#999;">&copy; 2025 Pure Finest Inc. All Rights Reserved.</p>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
