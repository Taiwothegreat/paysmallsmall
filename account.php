<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<p style='font-family:Arial;padding:20px;'>User not found. Please log in again.</p>";
    exit();
}

function pick_field($arr, $candidates, $default = '') {
    foreach ($candidates as $c) {
        if (array_key_exists($c, $arr) && $arr[$c] !== null && $arr[$c] !== '') {
            return $arr[$c];
        }
    }
    return $default;
}

$profile_pic    = pick_field($user, ['profile_pic', 'avatar', 'photo', 'picture'], null);
$name           = pick_field($user, ['name', 'fullname', 'fname', 'first_name'], 'Customer Name');
$email          = pick_field($user, ['email'], '');
$address        = pick_field($user, ['address', 'delivery_address'], '');
$verified       = (int) pick_field($user, ['verified'], 0);
$referral_code  = pick_field($user, ['referral_code'], '');
$sewing_machine = pick_field($user, ['sewing_machine'], '');
$referral_link  = $referral_code ? "https://paysmallsmall.org/ref/" . rawurlencode($referral_code) : '';
$company_address = "Paysmallsmall Headquarters, Suite 12, Abioye Plaza, Customs Bus Stop, Lagos, Nigeria.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Account - Paysmallsmall</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <style>
    body { background:#f8f9fa; font-family: Arial, sans-serif; padding-top:20px; }
    .profile-pic { 
      width:100px; height:100px; border-radius:50%; overflow:hidden; background:#eee; 
      display:inline-block; vertical-align:middle; cursor:pointer; position:relative;
    }
    .profile-pic img { width:100%; height:100%; object-fit:cover; }
    .profile-pic:hover::after {
      content:'📷'; position:absolute; top:0; left:0; width:100%; height:100%;
      display:flex; align-items:center; justify-content:center; color:#555; font-size:24px;
      background:rgba(255,255,255,0.6);
    }
    .btn-block-custom { width:100%; margin-bottom:12px; text-align:left; padding:12px 15px; border-radius:6px; font-weight:600; border:none; box-shadow:0 2px 5px rgba(0,0,0,0.1); transition:background 0.3s; }
    .btn-block-custom:hover { background:#2b6; color:#fff; }
    /* Keep color stable for referral and company address */
    .btn-block-custom.no-hover:hover { background:#fff; color:#333; }
    .section-title { font-size:16px; font-weight:bold; margin:18px 0 8px; color:#333; }
  </style>
</head>
<body>
<div class="container">

  <!-- Profile -->
  <div class="text-center" style="margin-bottom:20px;">
    <form id="uploadForm" action="upload_profile.php" method="POST" enctype="multipart/form-data">
      <label for="profileInput" class="profile-pic">
        <?php if ($profile_pic): ?>
          <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
        <?php else: ?>
          <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#888;font-size:28px;">+</div>
        <?php endif; ?>
      </label>
      <input type="file" id="profileInput" name="profile_pic" accept="image/*" style="display:none;" onchange="document.getElementById('uploadForm').submit();">
    </form>
    <h3 style="margin-top:10px;"><?php echo htmlspecialchars($name); ?></h3>
    <p style="color:#666;"><?php echo htmlspecialchars($email); ?></p>
    <p>
      Verification:
      <?php if ($verified): ?>
        <span class="label label-success">Verified</span>
      <?php else: ?>
        <span class="label label-warning">Pending</span>
      <?php endif; ?>
    </p>
  </div>

  <!-- Action Buttons -->
  <h4 class="section-title">Account Options</h4>

<!--  <a href="address_list.php" class="btn btn-default btn-block-custom">
    🏠 Delivery Address <br><small><?php #echo $address ? htmlspecialchars($address) : 'No address added yet'; ?></small>
  </a>-->

 <!-- <a href="sewing_machine.php" class="btn btn-default btn-block-custom">
    🧵 Paysmallsmall Sewing Machine <br><small><?php #echo $sewing_machine ? htmlspecialchars($sewing_machine) : 'Not added yet'; ?></small>
  </a>-->

<!--  <a href="order_history.php" class="btn btn-default btn-block-custom">-->
    <a href="history.php" class="btn btn-default btn-block-custom">
    📦 Order History <br><small>View your recent orders.</small>
  </a>

 <!-- <a href="payment_cards.php" class="btn btn-default btn-block-custom">
    💳 Payment Cards <br><small>Manage your saved cards.</small>
  </a>

  <a href="bank_accounts.php" class="btn btn-default btn-block-custom">
    🏦 Bank Accounts <br><small>Manage linked bank accounts.</small>
  </a>-->

  <a href="terms.html" class="btn btn-default btn-block-custom">
    📜 Terms & Conditions <br><small>Read our service policy.</small>
  </a>

  <a href="edit_profile.php" class="btn btn-default btn-block-custom">
    🔒 Edit Profile <br><small>Learn how we protect your data.</small>
  </a>

  <a href="faq.html" class="btn btn-default btn-block-custom">
    ❓ FAQs <br><small>Find answers to common questions.</small>
  </a>

  <?php if ($referral_link): ?>
  <button class="btn btn-default btn-block-custom no-hover" onclick="copyReferral()">
    🔗 Referral Link <br><small id="refText"><?php echo htmlspecialchars($referral_link); ?></small>
  </button>
  <?php else: ?>
  <button class="btn btn-default btn-block-custom no-hover" disabled>
    🔗 Referral Link <br><small>No referral code yet.</small>
  </button>
  <?php endif; ?>

  <div class="section-title">Company Info</div>
  <button class="btn btn-default btn-block-custom no-hover" disabled>
    🏢 Company Address <br><small><?php echo htmlspecialchars($company_address); ?></small>
  </button>

  <div style="margin-top:25px;text-align:center;">
    <a href="delete_account.php" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete your account permanently?')">Delete Profile</a>
    <a href="logout.php" class="btn btn-default">Sign Out</a>
  </div>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>
function copyReferral(){
  var text = document.getElementById('refText').innerText;
  navigator.clipboard.writeText(text);
  alert('Referral link copied!');
}
</script>
<script>
$(document).ready(function() {
  // Detect when the Edit Profile button is clicked
  $('a[href="edit_profile.php"]').on('click', function(e) {
    e.preventDefault(); // prevent any other conflicting default
    window.location.href = 'edit_profile.php'; // open the edit profile page
  });
});
</script>

</body>
</html>
