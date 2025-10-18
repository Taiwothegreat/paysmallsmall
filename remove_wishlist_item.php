<?php
session_start();
include 'db_connect.php';
if(!isset($_SESSION['user_id'])){ header("Location: login.html"); exit; }
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['id'])){
  $id=(int)$_POST['id'];
  $stmt=$conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ? LIMIT 1");
  $stmt->bind_param("ii",$id,$_SESSION['user_id']); $stmt->execute();
}
header("Location: wishlist.php");
