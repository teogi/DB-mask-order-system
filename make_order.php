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

function test_input($data) {
  $data = htmlspecialchars($data);
  return $data;
}

try
{
	
	if (empty($_POST['ord_amt']))
		throw new Exception('Please input an amount.');
	if (intval($_POST['ord_amt'])<0 || !preg_match("/^[0-9]*$/",$_POST['ord_amt']))
		throw new Exception('Please input a valid amount.');
	
	////////////information provided/////////////
	$uname	=$_SESSION['Username'];
	$shop	=test_input($_POST['shop']);
	$price	=test_input($_POST['price']);
	$ord_amt=test_input($_POST['ord_amt']);
	$ord_status=array("Ordered"=>0,"Finished"=>1,"Cancelled"=>2);
	/////////////////////////////////////////////
	
	/////////////fetching data from database
	$conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
	# set the PDO error mode to exception
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
	
	////////////Exception conditions checking
	$stmt=$conn->prepare("SELECT shop_name,mask_amount,mask_price FROM shop WHERE shop_name=:shop");
	$stmt->execute(array('shop' => $shop));
	if($stmt->rowCount()!=1){
		throw new Exception("Error Occured!\nPlease try again.");
	}
	$row=$stmt->fetch();
	$mask_amt=$row['mask_amount'];
	if($ord_amt>$mask_amt)
		throw new Exception('The ordered mask amount is greater than the shop can provide.\nPlease input a smaller amount.');
	
	/////////////updating `shop`'s information
	$cur_amt=$row['mask_amount']-$ord_amt;
	$query="UPDATE shop SET mask_amount = :cur_amt WHERE shop_name=:shop";
	$param_s=array('cur_amt'=>$cur_amt,'shop'=>$shop);
	$stmt=$conn->prepare($query);
	$stmt->execute($param_s);
	
	/////////////insert a data to `orders`
	$query="INSERT INTO `orders`(status,start_date,orderer,shop_name,price,amount) 
			VALUES(:status,NOW(),:orderer,:shop_name,:price,:amount)";
	$param_o=array('status'=>$ord_status['Ordered'],'orderer'=>$uname,
				 'shop_name'=>$shop,'price'=>$price,'amount'=>$ord_amt);
	$stmt=$conn->prepare($query);
	$stmt->execute($param_o);
	
	$suc_msg="Masks order successful!";
	echo <<<EOT
<!DOCTYPE html>
<html>
<body>
	<script>
	alert("$suc_msg");
	window.location.replace("home.php");
	</script>
</body>
</html>
EOT;
	
}
catch(Exception $e)
{
	$msg=$e->getMessage();
	echo <<<EOT
		<!DOCTYPE html>
		<html>
		<body>
			<script>
			alert("$msg");
			window.location.replace("home.php");
			</script>
		</body>
		</html>
EOT;
}

?>
<!--
<!DOCTYPE html>
<html>
<body>
	<div>
	</div>
</body>
</html>
-->