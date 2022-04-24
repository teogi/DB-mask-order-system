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
$mid=$_SESSION['UserID'];
$conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if (isset($_POST['mask_price'])){
	$stmt=$conn->prepare("UPDATE shop SET mask_price=:mask_price WHERE mid=:mid");
	$stmt->execute(array('mask_price'=>$_POST['mask_price'], 'mid'=>$mid));
	header("Location: shop.php");
}
if (isset($_POST['mask_amount'])){
	$stmt=$conn->prepare("UPDATE shop SET mask_amount=:mask_amount WHERE mid=:mid");
	$stmt->execute(array('mask_amount'=>$_POST['mask_amount'], 'mid'=>$mid));
	header("Location: shop.php");
}
if (isset($_POST['account'])){
		$stmt=$conn->prepare("SELECT username FROM work_for  WHERE username=:account and sid=:sid");
		$stmt->execute(array('account'=>$_POST['account'],'sid'=>$_SESSION['SID']));
	if ($stmt->rowCount()==1){
		$_SESSION['add_error']="{$_POST['account']} is already an employee at {$_SESSION['SName']}";
		header("Location: shop_manager.php");
		exit();
	}
	$stmt=$conn->prepare("SELECT * FROM user WHERE username=:account");
	$stmt->execute(array('account'=>$_POST['account']));
	if ($stmt->rowCount()==0){
		$_SESSION['add_error']='No such user!';
		header("Location: shop_manager.php");
		exit();
	}
	$row = $stmt->fetch();
	if($row['uid']==$mid){
		$_SESSION['add_error']='You CANNOT add yourself!';
		header("Location: shop_manager.php");
		exit();
	}
	$stmt=$conn->prepare("INSERT INTO work_for (username,sid) VALUES(:account,:sid)");
	$stmt->execute(array('account'=>$_POST['account'], 'sid'=>$_SESSION['SID']));
}
if(isset($_POST['delete'])){
	$stmt=$conn->prepare("DELETE FROM work_for WHERE username=:account AND sid=:sid");
	$stmt->execute(array('account'=>$_POST['delete'],'sid'=>$_SESSION['SID']));
}
?>

<!DOCTYPE html>
<html>
<head>
<title><?php echo $_SESSION['SName'] ?></title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
<nav class="navbar navbar-expand-sm bg-light navbar-light sticky-top">
	<ul class="navbar-nav">
		<li class="nav-item"><a class="nav-link" href="home.php" id="home">Home</a></li>
		<li class="nav-item active"><a class="nav-link" href="#" id="shop">Shop</a></li>
		<li class="nav-item"><a class="nav-link" href="my_order.php"  id="my_order">My Order</a></li>
		<li class="nav-item"><a class="nav-link" href="shop_order.php"  id="shop_order">Shop Order</a></li>
		<li class="nav-item"><a class="nav-link" href="logout.php" id="Logout">Logout</a></li>
	</ul>
</nav>

<div class="container-fluid">
<h2 class="mb-2">Shop</h2>
<table class="table">
<tr>
	<th>Shop</th>
	<td><?php echo $_SESSION['SName']; ?></td>
</tr>
<tr>
	<th>City</th>
	<td><?php echo $_SESSION['City'];  ?></td>
</tr>
<tr>
	<th>Price</th>
	<td>
		<form action="shop_manager.php" method="post">
			<input type="text" name="mask_price" value="<?php echo $_SESSION['Price']; ?>">
			<input type="submit" value="Edit">
		</form>
	</td>
</tr>
<tr>
	<th>Quantity</th>
	<td>
		<form action="shop_manager.php" method="post">
			<input type="text" name="mask_amount" value="<?php echo $_SESSION['Quantity']; ?>">
			<input type="submit" value="Edit">
		</form>
	</td>
</tr>
</table>
</div>

<div class="container-fluid mb-2">
<form class="form-inline" action="shop_manager.php" method="post">
  <div class="form-group ml-1">
	<label class="mr-2" for="account">Employee:</label>
	<input class="form-control" type="text" name="account" placeholder="Type Account"> 
  </div>
  <div class="form-group ml-1">
	<input class="btn btn-primary" type="submit" value="Add">
  </div>
  <?php 
	if(isset($_SESSION['add_error'])) echo $_SESSION['add_error'];
  ?>
</form>
</div>


<table class="table table-hover">
  <thead class="thead-light">
	<tr>
		<th class="">Account</th>
		<th>phone</th>
	</tr>
  </thead>
  <?php
	$stmt=$conn->prepare("SELECT username,phone FROM work_for NATURAL JOIN user WHERE sid=:sid");
	$stmt->execute(array(':sid'=>$_SESSION['SID']));
	while(!empty($row=$stmt->fetch())){
		echo "<tr>";
		echo "<td>".$row['username']."</td>";
		echo "<td>".$row['phone'];
		echo "<form style='display:inline-block' class='ml-2' action='shop_manager.php' method='post'>";
		echo "<input type='hidden' name='delete' value=".$row['username'].">";
		echo "<input class='btn btn-danger' type='submit' value='Delete'>";
		echo "</form></td>";
		echo "</tr>";
	}
  ?>
</table>

<?php
	unset($_SESSION["add_error"]);
	$stmt=$conn->prepare("SELECT username FROM work_for WHERE username=:account");
?>
</body>
</html>