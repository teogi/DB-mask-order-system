<?php
session_start();
$dbservername='localhost';
$dbname		 ='order_sys';
$dbusername	 ='examdb';
$dbpassword	 ='examdb';

////////////////////PDO db connection////////////////////////
$conn 	= new PDO("mysql:host=$dbservername;dbname=$dbname", 
				$dbusername, $dbpassword);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$uname	= $_SESSION['Username'];

try{
	if($_SERVER["REQUEST_METHOD"]=='POST'){
		if(isset($_POST['oid'])){			
		////////////cancel multiple/////////////////
			$oid_arr= $_POST['oid'];
			$query="UPDATE orders SET status=2,finish_date=NOW(), 
						   finisher=:uname 
					WHERE  1!=1";
			$param=array('uname'=>$uname);
			//////////check if status has been modified
			foreach($oid_arr as $i=>$oid){
				$stmt= $conn->prepare("SELECT status FROM orders WHERE oid=:oid");
				$stmt->execute(array('oid'=>$oid));
				$row=$stmt->fetch();
				if($stmt->rowCount()==1 && $row['status']!=0)
					throw new Exception("Some of the selected orders has been modified by another one! You cannot make any further modification!");
			}
			
			foreach($oid_arr as $i=>$oid){
				$query .=" OR oid=:oid$i";
				$param["oid$i"]=$oid;
			
				//////////update shop
				$so_stmt = $conn->prepare("SELECT amount,shop_name FROM orders WHERE oid=:oid");
				$so_stmt->execute(array('oid'=>$oid));
				$row=$so_stmt->fetch();
				$shop=$row['shop_name'];
				$amount=$row['amount'];
				
				$s_stmt = $conn->prepare("UPDATE shop SET mask_amount=mask_amount+:amt
								   	  WHERE shop_name=:shop");
				$s_stmt->execute(array('shop'=>$shop,'amt'=>$amount));
				
			}
			$stmt = $conn->prepare($query);
			$stmt->execute($param);
			
			
		}elseif(isset($_POST['oid0'])){		
		////////////cancel single/////////////////
			
			$oid0	= $_POST['oid0'];
			//////////check if status has been modified
			$stmt= $conn->prepare("SELECT status FROM orders WHERE oid=:oid");
			$stmt->execute(array('oid'=>$oid0));
			$row=$stmt->fetch();
			if($stmt->rowCount()==1 && $row['status']!=0)
				throw new Exception("This order has been modified by another one! You cannot make any further modification!");
			
			//////////prepare update orders
			$o_stmt = $conn->prepare("UPDATE orders SET status=2,finish_date=NOW(), 
										     finisher=:uname 
									  WHERE oid=:oid0");
			
			//////////prepare update shop
			$so_stmt = $conn->prepare("SELECT amount,shop_name FROM orders WHERE oid=:oid0");
			$so_stmt->execute(array('oid0'=>$oid0));
			$row=$so_stmt->fetch();
			print_r($row);
			$shop=$row['shop_name'];
			$amount=$row['amount'];
			
			$s_stmt = $conn->prepare("UPDATE shop SET mask_amount=mask_amount+:amt
								   	  WHERE shop_name=:shop");
			
			////execution
			$o_stmt->execute(array('oid0'=>$oid0,'uname'=>$uname));
			$s_stmt->execute(array('shop'=>$shop,'amt'=>$amount));
			
		}
	}
	//back to my_order.php
	echo <<<EOT
<!DOCTYPE html>
<html>
<body>
	<script>
	window.location.replace("my_order.php");
	</script>
</body>
</html>
EOT;
}
catch(PDOException $e){
	$errMsg=$e->getMessage();
	echo "Error:" . $errMsg;
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
		  window.location.replace("my_order.php");
        </script>
	  </body>
	</html>
EOT;
  
}
?>