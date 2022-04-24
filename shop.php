<?php
  session_start();
  if(!isset($_SESSION['UserID'])){
    header("Location: index.php");
    exit();
  }
  $dbservername	='localhost';
  $dbname		='order_sys';
  $dbusername	='examdb';
  $dbpassword	='examdb';
  $uid=$_SESSION['UserID'];
  $conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
  $stmt=$conn->prepare("select * from shop where mid=:uid");
  $stmt->execute(array('uid' => $uid));
  if ($stmt->rowCount()==1)
  {
	// manager code here
	$row = $stmt->fetch();
	$_SESSION['SID']=$row['sid'];
	$_SESSION['SName']=$row['shop_name'];
	$_SESSION['Quantity']=$row['mask_amount'];
	$_SESSION['Price']=$row['mask_price'];
	$_SESSION['City']=$row['city'];
    echo "you are a manager!";
	header("Location: shop_manager.php");
  }
  else{
	  // slave code here
	  echo "you are a slave!";
	  header("Location: shop_worker.php");
  }
?>
