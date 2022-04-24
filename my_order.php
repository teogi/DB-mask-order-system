<?php
session_start();
if(!isset($_SESSION['UserID'])){
	header("Location: index.php");
	exit();
}
$dbservername='localhost';
$dbname		 ='order_sys';
$dbusername	 ='examdb';
$dbpassword	 ='examdb';

////////////////////user information//////////////////////////
$uname	=$_SESSION['Username'];
$phone	=$_SESSION['Phone'];
$uid	=(int)$_SESSION['UserID'];

////////////////////PDO db connection////////////////////////
$conn 	= new PDO("mysql:host=$dbservername;dbname=$dbname", 
				$dbusername, $dbpassword);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function action_form($oid){
	return <<<ACTION
	<form method="post">
	<input type="hidden" value="$oid" id="oid0" name="oid0"></input>
	<input type="submit" value="Cancel" formaction="c_order.php"></input>
	</form>
ACTION;
}
?>
<!--------------html----------------->
<!DOCTYPE html>
<html>
<head>
<title>My Order</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>

</style>
</head>

<body>
<nav class="navbar navbar-expand-sm bg-light navbar-light sticky-top">
	<ul class="navbar-nav">
		<li class="nav-item">
			<a class="nav-link" href="home.php" id="home">Home</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="shop.php" id="shop">Shop</a>
		</li>
		<li class="nav-item active">
			<a class="nav-link" href="#"  id="my_order">My Order</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="shop_order.php"  id="shop_order">Shop Order</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="logout.php" id="Logout">Logout</a>
		</li>
	</ul>
</nav>

<div class="filter container-fluid">
<h2>My Order</h2>
<form class='form-inline row mb-3 mt-3' method="post">
	<input type="hidden" value="<?php echo $uname;?>" id="orderer" name="orderer"><label for="orderer"></label></input>
	<div class='input-group col-sm-5' >
		<label class="mr-2" for="status">Status:</label>
		<select class='form-control' id="status" name="status">
			<option value=3>All</option>
			<option value=0>Not Finished</option>
			<option value=1>Finished</option>
			<option value=2>Cancelled</option>
		</select>
	</div>
	<div class='form-group col-2'>
		<input class='btn btn-primary' type="submit" value="Submit"></input>
	</div>
</form>
</div>

<div class="order_table container-fluid">
<!--------------------select multiple option form------------------------------->
<form class='' id='multi' method='post' action='c_order.php' >
	<input class='btn btn-danger mb-2' type='submit' value='Cancel Selected'></input>
</form>
<table class="table">
	<thead>
		<tr>
			<th></th>
			<th>OID</th>
			<th>Status</th>
			<th>Start</th>
			<th>End</th>
			<th>Shop</th>
			<th>Total Price</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
<?php
$orderer= $uname;
$status	= 3;		//3 => All

if(isset($_POST['status'])){
	$_SESSION['status']	= $_POST['status'] ; 
}
if(isset($_SESSION['status'])){
	$status=$_SESSION['status'];
}

$ord_status=array(0=>"Not Finished",1=>"Finished",2=>"Cancelled");
$query="SELECT * 
		FROM orders
		WHERE orderer=:orderer";
$param=array("orderer"=>$orderer);
if($status!=3){
	$query.= " AND status=:status";
	$param['status']=$status;
}

$stmt=$conn->prepare($query);
$stmt->execute($param);
if($stmt->rowCount()==0){
	$info="You have no order yet!";
	switch($status){
		case 0:	$info="You have no unfinished order!";break;	
		case 1:	$info="You have no finished order yet!";break;	
		case 2:	$info="You have no cancelled order yet!";break;
		default:;
	}
	echo"<tr>";
	echo"<td colspan=5>$info</td>";
	echo"</tr>";
}
$arr_i=1;
while(!empty($row=$stmt->fetch())){
	$oid		= $row['oid'];
	$status		= $row['status'];
	echo "<tr>";
	echo "<td>".($row['status']==0 ?
		 "<input type='checkbox' name='oid[]' id= 'oid$arr_i' value=$oid form='multi'>":"")
		 ."</td>";
	echo "<td>$oid</td>";
	echo "<td>".$ord_status[$status]."</td>";
	echo "<td>".$row['start_date']."<br>".$row['orderer']."</td>";
	echo "<td>".$row['finish_date']."<br>".$row['finisher']."</td>";
	echo "<td>".$row['shop_name']."</td>";
	echo "<td>".($row['price']*$row['amount'])."<br>(".$row['amount']."*".$row['price'].")</td>";
	echo "<td>".($row['status']==0 ? action_form($oid):"")."</td>";
	echo "</tr>";
	$arr_i++;
}
?>
</tbody>
</table>
</div>

</body>
</html>