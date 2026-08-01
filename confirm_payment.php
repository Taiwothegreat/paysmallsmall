<?php
// ---- DATABASE CONNECTION ----
$conn = new mysqli("localhost", "paysmall_user", ")1+TuLiz!uae", "paysmall_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// ---- GET VALUES FROM LINK ----
$order_id = trim($_GET['order_id'] ?? '');
$email    = trim($_GET['email'] ?? '');
$token    = trim($_GET['token'] ?? '');


if (empty($order_id) || empty($email) || empty($token)) {
    die("<h3 style='color:red;'>Invalid confirmation link. Missing parameters.</h3>");
}


// ---- FIND PAYMENT RECORD ----
$stmt = $conn->prepare("
SELECT 
product_name,
price,
installment_amount,
first_installment,
plan_type,
duration,
status,
admin_token,
order_type

FROM payments

WHERE order_id = ?
AND LOWER(email) = LOWER(?)

LIMIT 1
");


$stmt->bind_param("ss", $order_id, $email);

$stmt->execute();

$result = $stmt->get_result();



if ($result->num_rows === 0) {

    die("<h3 style='color:red;'>
    ❌ Payment not found.<br>
    Order ID: $order_id<br>
    Email: $email
    </h3>");

}



$row = $result->fetch_assoc();



// ---- PAYMENT DETAILS ----

$product = $row['product_name'];

$price = $row['price'];

$installment = $row['installment_amount'];

$first = $row['first_installment'];

$plan = $row['plan_type'];

$duration = $row['duration'];

$orderType = $row['order_type'];




// ---- CHECK TOKEN ----

if ($token !== $row['admin_token']) {

    die("<h3 style='color:red;'>
    ❌ Invalid confirmation token.
    </h3>");

}



// ---- CHECK ALREADY CONFIRMED ----

if ($row['status'] === 'confirmed') {

    die("<h3 style='color:green;'>
    This payment has already been confirmed.
    </h3>");

}



// ---- SHIPPING PERCENTAGE FOR MACHINES ONLY ----

$shippingPercent = 0;


if ($orderType !== "accessory") {


    if ($price <= 50000) {
        $rate = 0.15;
    }
    elseif ($price <= 100000) {
        $rate = 0.18;
    }
    elseif ($price <= 150000) {
        $rate = 0.15;
    }
    elseif ($price <= 250000) {
        $rate = 0.12;
    }
    elseif ($price <= 500000) {
        $rate = 0.10;
    }
    else {
        $rate = 0.05;
    }


    $shippingPercent = $rate * 100;

}




// ---- UPDATE PAYMENT STATUS ----

$update = $conn->prepare("
UPDATE payments
SET status='confirmed'

WHERE order_id=?
AND LOWER(email)=LOWER(?)
");


$update->bind_param("ss", $order_id, $email);

$update->execute();

$update->close();




// ======================================
// CUSTOMER CONFIRMATION EMAIL
// ======================================


if ($orderType === "accessory") {


    $paymentDetails = "

    <p>
    <strong>Amount Paid:</strong>
    ₦" . number_format($price,2) . "
    </p>


    <p>
    <strong>Delivery Charge:</strong>
    Included
    </p>

    ";


}
else {


    $paymentDetails = "

    <p>
    <strong>Installment Amount:</strong>
    ₦" . number_format($installment,2) . "
    </p>


    <p>
    <strong>
    First Installment (with {$shippingPercent}% shipping):
    </strong>

    ₦" . number_format($first,2) . "

    </p>

    ";

}




$subject = "Payment Confirmed - Order ID: $order_id";



$message = "

<html>

<body>


<h2 style='color:#28a745;'>
Your Payment Has Been Confirmed
</h2>


<p>
<strong>Order ID:</strong>
$order_id
</p>


<p>
<strong>Product:</strong>
$product
</p>


<p>
<strong>Total Price:</strong>
₦" . number_format($price,2) . "
</p>


$paymentDetails



<p>
<strong>Plan:</strong>
$plan
</p>


<p>
<strong>Duration:</strong>
$duration Weeks
</p>



<p>
Your payment has been successfully verified by our team.
</p>



" . ($orderType !== "accessory" ? 
"<p>You may continue with the next installment when due.</p>" 
: "") . "



</body>

</html>

";



$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type:text/html;charset=UTF-8\r\n";
$headers .= "From: Paysmallsmall <no-reply@paysmallsmall.org>\r\n";



mail($email,$subject,$message,$headers);



?>



<!DOCTYPE html>

<html>

<head>

<title>Payment Confirmed</title>


<style>

body {

font-family:Arial;

background:#f2f2f2;

text-align:center;

padding-top:40px;

}


.box {

background:white;

padding:30px;

border-radius:10px;

width:450px;

margin:auto;

box-shadow:0 0 10px rgba(0,0,0,0.2);

}


h2 {

color:#28a745;

}


p {

font-size:16px;

}

</style>


</head>


<body>


<div class="box">


<h2>
Payment Confirmed
</h2>



<p>
<strong>Order ID:</strong>

<?php echo htmlspecialchars($order_id); ?>

</p>



<p>
<strong>Product:</strong>

<?php echo htmlspecialchars($product); ?>

</p>



<p>
<strong>Total Price:</strong>

₦<?php echo number_format($price,2); ?>

</p>




<?php if($orderType === "accessory"){ ?>


<p>

<strong>
Amount Paid:
</strong>

₦<?php echo number_format($price,2); ?>

</p>



<p>

<strong>
Delivery Charge:
</strong>

Included

</p>



<?php } else { ?>



<p>

<strong>
Installment Amount:
</strong>

₦<?php echo number_format($installment,2); ?>

</p>



<p>

<strong>
First Installment
(with <?php echo $shippingPercent; ?>% shipping):
</strong>


₦<?php echo number_format($first,2); ?>

</p>



<?php } ?>




<p>

<strong>
Duration:
</strong>

<?php echo htmlspecialchars($duration); ?> Weeks

</p>



<br>


<p style="color:green;">

This payment has been successfully confirmed.

</p>



</div>


</body>


</html>