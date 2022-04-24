<?php
session_start();
if(!isset($_SESSION['UserID'])){
  header("Location: index.php");
  exit();
}
$dbservername='localhost';
$dbname  ='order_sys';
$dbusername ='examdb';
$dbpassword ='examdb';
$conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
 
//input error handling
if (isset($_POST['s_name']) && isset($_POST['city']) && 
	isset($_POST['price'])  && isset($_POST['amount'])) {
	if(empty($_POST['s_name'])) $_SESSION['shop_error']  ="Required!";
	if(empty($_POST['price']) ) $_SESSION['price_error'] ="Required!";
	if(empty($_POST['amount'])&&$_POST['amount']!=0) $_SESSION['amount_error'] ="Required!";
	
	$stmt=$conn->prepare("SELECT shop_name FROM shop WHERE shop_name=:sname");
	$stmt->execute(array(':sname' => $_POST['s_name']));
	if($stmt->rowCount()>0)
	$_SESSION['shop_error']="Shop name has been used";
	
	$cities = array('Taipei','New Taipei','Keelung',
				'Taoyuan','Hsinchu','Yilan','Miaoli',
				'Taichung','Changhua','Nantou','Hualien',
				'Yunlin','Chiayi','Tainan','Kaohsiung',
				'Taitung','Pingtung','Penghu','Lienchiang');
	$modified=true;
	foreach($cities as $ct){
	if($_POST['city']==$ct)$modified=false;
	}
	if($modified)$_SESSION['city_error']="Not in original options. Did you modify the options?";
	
	if( !isset($_SESSION['price_error']) &&
	!preg_match('/^[0-9]*$/', $_POST['price']))
	$_SESSION['price_error']="Numbers only";
	if( !isset($_SESSION['amount_error']) &&
	!preg_match('/^[0-9]*$/', $_POST['amount']))
	$_SESSION['amount_error']="Numbers only";
	
	//add shop
	if(!isset( $_SESSION['shop_error']) && !isset($_SESSION['city_error']) && 
	!isset($_SESSION['price_error']) && !isset( $_SESSION['amount_error'])){
	$stmt=$conn->prepare("INSERT INTO shop (shop_name, mid, city, mask_amount, mask_price)
		VALUES(:sname,:mid,:city,:quantity,:price)");
	$stmt->execute(array('mid'=>$_SESSION['UserID'], 
						'sname'=>$_POST['s_name'],
						'quantity'=>$_POST['amount'], 
						'price'=>$_POST['price'], 
						'city'=>$_POST['city']));
	header("Location: shop.php");
	exit();
	}
}
?>

<!DOCTYPE html>
<html>

<head>
<title>Shop Register</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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

<h2>Register a shop:</h2>
<form action="shop_worker.php" method="post">
  <div class="form-group col-md-8">
	<input type="text" class="form-control" name="s_name" placeholder="shop name">
	<?php
		if(isset($_SESSION['shop_error'])) echo $_SESSION['shop_error'];
	?>
  </div>
  <div class="form-group col-md-8">
	<select id="city" class="form-control" name="city">
	</select>
  </div>
  <div class="form-group col-md-8">
	<input type="text" class="form-control" name="price" placeholder="price"><?php
	if(isset($_SESSION['price_error'])) echo $_SESSION['price_error'];
	?>
  </div>
  <div class="form-group col-md-8">
	<input type="text" class="form-control" name="amount" placeholder="amount"><?php
	if(isset($_SESSION['amount_error'])) echo $_SESSION['amount_error'];
	?>
  </div>
	<input class="btn btn-primary" type="submit" value="Register">
	</input>
</form>


</body>
</html>

<script>
  var txt="";
  var option= ['Taipei','New Taipei','Keelung',
    'Taoyuan','Hsinchu','Yilan','Miaoli',
    'Taichung','Changhua','Nantou','Hualien',
    'Yunlin','Chiayi','Tainan','Kaohsiung',
    'Taitung','Pingtung','Penghu','Lienchiang'];
  option.forEach(addOption);
  document.getElementById("city").innerHTML=txt;
  
  function addOption(value){
 txt+="<option value=\""+value+"\">"+value+"</option>";
  }
 
 </script>
  

<?php
 unset($_SESSION["shop_error"]);
 unset($_SESSION["city_error"]);
 unset($_SESSION["price_error"]);
 unset($_SESSION["amount_error"]);
?>