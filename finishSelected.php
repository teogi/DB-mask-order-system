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

	if(isset($_POST['slected'])){			
		////////////finish multiple/////////////////
		
		$oid_arr= $_POST['slected'];
		//////////check if status has been modified
		foreach($oid_arr as $i=>$oid){
			$stmt= $conn->prepare("SELECT status FROM orders WHERE oid=:oid");
			$stmt->execute(array('oid'=>$oid));
			$row=$stmt->fetch();
			if($stmt->rowCount()==1 && $row['status']!=0)
				throw new Exception("Some of the selected orders has been modified by another one! You cannot make any further modification!");
		}
		
		//////////finished order
		$query="UPDATE orders SET status=1,finish_date=NOW(), 
					   finisher=:uname 
				WHERE  1!=1";
		$param=array('uname'=>$uname);
		foreach($oid_arr as $i=>$oid){
			$query .=" OR oid=:oid$i";
			$param["oid$i"]=$oid;
		}
		$stmt = $conn->prepare($query);
		$stmt->execute($param);
	}
 
	//back to shop_order.php
	echo <<<EOT
<!DOCTYPE html>
<html>
<body>
	<script>
	window.location.replace("shop_order.php");
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
		  window.location.replace("shop_order.php");
        </script>
      </body>
	</html>
EOT;
}
?>