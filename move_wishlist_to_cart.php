<?php
session_start();
include 'db_connect.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.html"); exit; }
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['id'])){
  $id=(int)$_POST['id'];
  // fetch item
  $stmt=$conn->prepare("SELECT product_name, price, image FROM wishlist WHERE id = ? AND user_id = ? LIMIT 1");
  $stmt->bind_param("ii",$id,$_SESSION['user_id']); $stmt->execute(); $res=$stmt->get_result();
  if($res->num_rows){
    $r=$res->fetch_assoc();
    $ins=$conn->prepare("INSERT INTO cart (user_id, product_name, price, image) VALUES (?, ?, ?, ?)");
    $ins->bind_param("isds", $_SESSION['user_id'], $r['product_name'], $r['price'], $r['image']); $ins->execute();
    $del=$conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ? LIMIT 1"); $del->bind_param("ii",$id,$_SESSION['user_id']); $del->execute();
  }
}
header("Location: cart.php");
