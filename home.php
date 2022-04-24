<?php
session_start();
if(!isset($_SESSION['UserID'])){
    header("Location: index.php");
    exit();
 }
if($_SESSION['Authenticated']==false){
	session_unset();
	session_destroy();
	header("Location: index.php");
	exit();
}
$dbservername='localhost';
$dbname		 ='order_sys';
$dbusername	 ='examdb';
$dbpassword	 ='examdb';

try
{
	/*
	if(!isset($_SESSION['Username'])){
		header("Location: index.php");
		exit();
	}
	*/
	
	//getting user profile information
	$uname	=$_SESSION['Username'];
	$phone	=$_SESSION['Phone'];
	$uid	=(int)$_SESSION['UserID'];
	$conn 	= new PDO("mysql:host=$dbservername;dbname=$dbname", 
					$dbusername, $dbpassword);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//shop searching list
	$op_stmt=$conn->prepare("SELECT DISTINCT city 
							FROM `shop`");
	$op_stmt->execute();
	
	
}
catch(Exception $e)
{
	$msg=$e->getMessage();
	session_unset();
	session_destroy();
	echo <<<EOT
		<!DOCTYPE html>
		<html>
			<body>
				<script>
					alert("$msg");
					window.location.replace("index.php");
				</script>
			</body>
		</html>
EOT;
}

//form error handling
$price_err="";
if(	$_SERVER["REQUEST_METHOD"]=="GET"&&	isset($_GET['m_price_low']) &&
	isset($_GET['m_price_upp'])	&&	isset($_GET['m_price_low'])		){
		if($_GET['m_price_low']>$_GET['m_price_upp'])
			$price_err="lower boundary should not be higher than upper boundary";
		elseif(!(preg_match("/^[0-9]*$/",$_GET['m_price_low']) &&
				 preg_match("/^[0-9]*$/",$_GET['m_price_upp'])	))
			$price_err="input should not contain any character than 0-9";
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function unset_filter(){
	unset($_SESSION['shop']);
	unset($_SESSION['city']);
	unset($_SESSION['m_amt']);
	unset($_SESSION['m_price_low']);
	unset($_SESSION['m_price_upp']);
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Home</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<style>

</style>
</head>

<body>
<nav class="navbar navbar-expand-sm bg-light navbar-light sticky-top">
	<ul class="navbar-nav">
		<li class="nav-item active"><a class="nav-link" href="#" id="home">Home						</a></li>
		<li class="nav-item"><a class="nav-link" href="shop.php" id="shop">Shop				</a></li>
		<li class="nav-item"><a class="nav-link" href="my_order.php"  id="my_order">My Order	</a></li>
		<li class="nav-item"><a class="nav-link" href="shop_order.php"  id="shop_order">Shop Order</a></li>
		<li class="nav-item"><a class="nav-link" href="logout.php" id="Logout">Logout			</a></li>
	</ul>
</nav>

<div class="container-fluid profile">
	<h3>Profile</h3>
	<table class='table table-hover'>
		<tr class='row'>
			<td class='col-sm-1'>Account</td>
			<td class='col-sm'><?php echo $uname; ?></td>
		</tr>
		<tr class='row'>
			<td class='col-sm-1'>Phone</td>
			<td class='col-sm'><?php echo $phone; ?></td>
		</tr>
	</table>
</div>

<div class="container-fluid shop_list">
	<h6>Find a shop to order mask:</h6>
	<form method="get">
		<div class="form-row mb-2 shop">
			<label class="col-sm-1" for="shop">Shop:</label>
			<input class="form-control col-sm" type="text" id="shop" name="shop">
		</div>
		<div class="form-row mb-2 city">
			<label class='col-sm-1 ' for="city">City:</label>
			<select class="form-control col-sm" id="city" name="city">
				<option value="All">All</option>
				<?php
					$row=$op_stmt->fetch();
					if(!empty($row)){
						do {
							echo "<option value=\"". $row['city'] ."\">" .
								$row['city'] . '</option><br>';
						}while($row=$op_stmt->fetch());
					}
					
				?>
			</select>
		</div>
		<div class="form-row input-group mb-2 mask_price">
			<label class="col-sm-1" for="m_price_low m_price_upp">Mask Price:</label>
			<input class="form-control col-sm" type="text" id="m_price_low" name="m_price_low">
			<div class='input-group-append'>
			<div class='input-group-prepend'>
				<span class="input-group-text">~</span>
			</div>
			</div>
			<input class="form-control col-sm" type="text" id="m_price_upp" name="m_price_upp">
		</div>
<?php 
if($price_err!=""){ 
	echo <<<ALERT
	<div class="alert alert-warning alert-dismissible" id="PriceAlert">
	<button type="button" class="close" data-dismiss="alert">×</button>
    <strong>Warning!</strong> $price_err
	</div> 
ALERT;
}
?>
		<div class="form-row mb-2 mask_amount">
			<label class="col-sm-1" for="m_amt">Mask Amount:</label>
			<select class="form-control col-sm" id="m_amt" name="m_amt"><br>
				<option value="All">All</option>
				<option value="0">0 (sold)</option>
				<option value="1~50">1~50 (few)</option>
				<option value="50~~">50 and above (many)</option>
			</select>
		</div>
		<div class="form-row mb-2">
			<div class="checkbox">
				<label for="working">
				<input type="checkbox" id="working" name="working">
				 Only show the shop I work at</label>
			</div>
		</div>
		<input class="btn btn-outline-secondary" type="submit" value="Search">
	</form>
</div>

<div class="container-fluid search_table">
<?php
function isset_GET(){
	return (isset($_GET['shop'])	 	&&	isset($_GET['city'] ) 		&& 
			isset($_GET['m_amt'])		&&	isset($_GET['m_price_low']) &&	
			isset($_GET['m_price_upp']));
}
function isset_SESSION(){
	return (isset($_SESSION['shop'])	 	&&	isset($_SESSION['city'] ) 		&& 
			isset($_SESSION['m_amt'])		&&	isset($_SESSION['m_price_low']) &&	
			isset($_SESSION['m_price_upp'])	);
}

if(!isset_SESSION()){
	$_SESSION['shop']		="";
	$_SESSION['city']		='All';
	$_SESSION['m_amt']		="";
	$_SESSION['m_price_low']="";
	$_SESSION['m_price_upp']="";
}
if(isset_GET()){
	$_SESSION['shop']		= test_input($_GET['shop'] );
	$_SESSION['city']		= test_input($_GET['city'] ); 
	$_SESSION['m_amt']		= test_input($_GET['m_amt']	);
	$_SESSION['m_price_low']= test_input($_GET['m_price_low'] );
	$_SESSION['m_price_upp']= test_input($_GET['m_price_upp'] );
	if(isset($_GET['working']))
		$_SESSION['working']= $_GET['working'];
}

try{
	//default w/ shop
	$query ="SELECT shop_name,city, mask_price,mask_amount "; 
	$param_arr= array(':shop'=>"%".$_SESSION['shop']."%");
	if(isset($_GET['working'])){
		$query.="FROM	`shop` NATURAL JOIN work_for ";
		$query.="WHERE 	shop_name LIKE :shop ";	
		$query.="AND username=:uname ";
		$param_arr[':uname']=$uname;
	}else{
		$query.="FROM	`shop`";
		$query.="WHERE 	shop_name LIKE :shop ";	
	}
	//city
	if($_SESSION['city']!='All'){
		$query.=' AND city = :city';
		$param_arr[':city']=$_SESSION['city'];
	}
	
	//mask_price
	if(!empty($_SESSION['m_price_low'])&&!empty($_SESSION['m_price_upp'])){
		$query .= " AND mask_price BETWEEN :m_price_low AND :m_price_upp";
		$param_arr[':m_price_low']=$_SESSION['m_price_low'];
		$param_arr[':m_price_upp']=$_SESSION['m_price_upp'];
	}elseif(!empty($_SESSION['m_price_low'])){
		$query.=" AND mask_price >= :m_price_low";
		$param_arr[':m_price_low']=$_SESSION['m_price_low'];
	}elseif(!empty($_SESSION['m_price_upp'])){
		$query.=" AND mask_price <= :m_price_upp";
		$param_arr[':m_price_upp']=$_SESSION['m_price_upp'];
	}
	
	//mask_amount
	if($_SESSION['m_amt'] == "0"){
		$query .= " AND mask_amount = 0";
	}
	elseif($_SESSION['m_amt'] == '1~50'){
		$query .= " AND mask_amount BETWEEN 1 AND 50";
	}
	elseif($_SESSION['m_amt'] == '50~~'){
		$query .= " AND mask_amount > 50";
	}
	$s_stmt=$conn->prepare($query);
	$s_stmt->execute($param_arr);
	
	echo "Total result shown: ".$s_stmt->rowCount()."<br>";
	echo "<table class='table table-hover'>";
	echo "<thead class='thead-light'>";
	echo "<tr scope='row'>
		<th scope='col-sm-4'>Shop</th>
		<th scope='col-sm-4'>City</th>
		<th scope='col-sm-4'>Mask Price</th>
		<th scope='col-sm-2' colspan=2>Mask Amount</th></tr>
		</thead>";

	$row = $s_stmt->setFetchMode(PDO::FETCH_ASSOC);
	while(!empty($row=$s_stmt->fetch())){
		echo "<tr>";
		echo "<td scope='row'>".$row['shop_name']	."</td>";
		echo "<td>".$row['city']		."</td>";
		echo "<td>".$row['mask_price']	."</td>";
		echo "<td>".$row['mask_amount']	."</td>";
		echo "<td><form class='form-inline' action='make_order.php' method='post'>
					<input type='hidden' id='shop' name='shop' value='".$row['shop_name']."'></input>
					<input type='hidden' id='price' name='price' value=".$row['mask_price']."></input>
					<input class='form-control' type='text' id='ord_amt' name='ord_amt' placeholder='0'></input>
					<div class='input-group-append'>
						<input class='form-control' type='submit' value='Order'></input>
					</div>
			</form></td>";
		echo "</tr>";
/*				<div class='form-group col-sm-7'>
				</div>
				<div class='form-group col-sm-5'>
				</div>
*/
	}
	echo "</table>";
}
catch(PDOException $e){
	$errMsg=$e->getMessage();
	echo "Error:" . $errMsg;
}

$conn = null;
?>

</div>

<!--
		<script>
			var option= ['All','Taipei','New Taipei','Keelung',
						'Taoyuan','Hsinchu','Yilan','Miaoli',
						'Taichung','Changhua','Nantou','Hualien',
						'Yunlin','Chiayi','Tainan','Kaohsiung',
						'Taitung','Pingtung','Penghu','Lienchiang'];
			
		</script>
-->
</body>
</html>

