<?php
  session_start();
  if(!isset($_SESSION['UserID'])){
    header("Location: index.php");
    exit();
 }
  $dbservername ='localhost';
  $dbname  ='order_sys';
  $dbusername ='examdb';
  $dbpassword ='examdb';
  $conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $id=$_SESSION['UserID'];
  $name=$_SESSION['Username'];
  $select_shop="";
try{
	if (isset($_POST['shop'])){
		if($_POST['shop']!='All') $select_shop .="and shop_name='".$_POST['shop']."'";
		}
		if(isset($_POST['status'])){
		if($_POST['status']!='All'){
			$ord_status=array("Ordered"=>0,"Finished"=>1,"Cancelled"=>2);
			$select_shop .="and status=".$ord_status[$_POST['status']];
		}
	}
	//echo $select_shop;
	if(isset($_POST['done_id'])){
		//////////check if status has been modified
		$stmt= $conn->prepare("SELECT status FROM orders WHERE oid=:oid");
		$stmt->execute(array('oid'=>$_POST['done_id']));
		$row=$stmt->fetch();
		if($stmt->rowCount()==1 && $row['status']!=0)
			throw new Exception("This order has been modified by another one! You cannot make any further modification!");
					
		$query="update orders set status=:status where oid=:oid";
		$stmt=$conn->prepare($query);
		$stmt->execute(array(':status'=>1, ':oid'=>$_POST['done_id']));
		
		$query="update orders set finisher=:name where oid=:oid";
		$stmt=$conn->prepare($query);
		$stmt->execute(array(':name'=>$name, ':oid'=>$_POST['done_id']));
		
		$query="update orders set finish_date=NOW() where oid=:oid";
		$stmt=$conn->prepare($query);
		$stmt->execute(array(':oid'=>$_POST['done_id']));
		//echo "you clicked done!!!<br>";
	}
	if(isset($_POST['cancel_id']) && isset($_POST['cancel_amount'])){
		//////////check if status has been modified
		$stmt= $conn->prepare("SELECT status FROM orders WHERE oid=:oid");
		$stmt->execute(array('oid'=>$_POST['cancel_id']));
		$row=$stmt->fetch();
		if($stmt->rowCount()==1 && $row['status']!=0)
			throw new Exception("This order has been modified by another one! You cannot make any further modification!");
		
		//set_status to cancelled
		$query="update orders set status=:status where oid=:oid";
		$stmt=$conn->prepare($query);
		
		$stmt->execute(array(':status'=>2, ':oid'=>$_POST['cancel_id']));
		$query="update orders set finisher=:name where oid=:oid";
		$stmt=$conn->prepare($query);
		$stmt->execute(array(':name'=>$name, ':oid'=>$_POST['cancel_id']));
		
		$query="update orders set finish_date=NOW() where oid=:oid";
		$stmt=$conn->prepare($query);
		$stmt->execute(array(':oid'=>$_POST['cancel_id']));
		
		//ajsust mask amount
		$query="select sid from shop NATURAL JOIN orders where oid=:oid";
		$stmt=$conn->prepare($query);
		$stmt->execute(array(':oid'=>$_POST['cancel_id']));
		$row = $stmt->fetch();
		
		$query="update shop set mask_amount= mask_amount+:amt where sid=:sid";
		$stmt=$conn->prepare($query);
		$stmt->execute(array(':amt'=>$_POST['cancel_amount'], ':sid'=>$row['sid']));
	}
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

  
<!DOCTYPE html>
<html>
<head>
<title>Shop Order</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

<nav class="navbar navbar-expand-sm bg-light navbar-light sticky-top">
 <ul class="navbar-nav">
  <li class="nav-item"><a class="nav-link" href="home.php" id="home">Home </a></li>
  <li class="nav-item"><a class="nav-link" href="shop.php" id="shop">Shop </a></li>
  <li class="nav-item"><a class="nav-link" href="my_order.php"  id="my_order">My Order </a></li>
  <li class="nav-item active"><a class="nav-link" href="#"  id="shop_order">Shop Order</a></li>
  <li class="nav-item"><a class="nav-link" href="logout.php" id="Logout">Logout   </a></li>
 </ul>
</nav>

<div class="container-fluid mb-3">
 <h2>Shop Order</h2>
 <form class="form-default" action="shop_order.php" method="post">
  <label for='shop'>Shop</label>
  <select class="form-control col-sm-4" name="shop">
   <option value="All">All</option>
   <?php
      $stmt=$conn->prepare("select distinct shop_name from (select shop_name,mid,sid from shop inner join user on uid=mid) as A
            left join work_for as W on A.sid=W.sid 
            where username=:username or mid=:mid");
      $stmt->execute(array(':username'=>$name,':mid'=>$id));
       while(!empty($row=$stmt->fetch())){
        echo "<option value='".$row['shop_name']."'>".$row['shop_name']."</option>";
       }
     ?>
  </select>
  <label for='status'>Status</label>
  <select class="form-control col-sm-4" name="status">
   <option value="All">All</option> 
   <option value="Ordered">Ordered</option>
   <option value="Finished">Finished</option>
   <option value="Cancelled">Cancelled</option>
  </select>
  <input class="btn btn-primary mt-2" type="submit" value="Search">
 </form>
</div>

 <?php
  $status=array(0=>"Ordered", 1=>"Finished", 2=>"Cancelled");
  $query="SELECT distinct oid,status,start_date,orderer ,finish_date, finisher, shop_name, price, mid,amount FROM
  (select * from orders natural join shop) as S left join work_for as W on S.sid=W.sid 
  where ( mid=:mid or username=:username ) ".$select_shop;
  //echo $query;
  $stmt=$conn->prepare($query);
  $stmt->execute(array(':mid'=>$id, ':username'=>$name));
 ?>

<div class="container-fluid">
 <form class='inline-form' id='multi' method='post'>
  <div class="form-group">
   <input class="btn btn-danger" type='submit' value='Cancel Selected' formaction='cancelSelected.php'></input>
   <input class="btn btn-success" type='submit' value='Finish Selected' formaction='finishSelected.php'></input>
  </div>
 </form>
</div>
<div class="container-fluid">
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
  <th colspan=2>Action</th>
 </tr>
 </thead>
 <?php
  $arr_i=1;
  while(!empty($row=$stmt->fetch())){
    $oid=$row['oid'];
    echo "<td>".($status[$row['status']]=="Ordered" ?
    "<input type='checkbox' name='slected[]' id= 'oid$arr_i' value=$oid form='multi'>":
  "")."</td>";
    echo "<td>".$row['oid']."</td>";
    echo "<td>".$status[$row['status']]."</td>";
    echo "<td>".$row['start_date']."<br>".$row['orderer']."</td>";
    echo "<td>".$row['finish_date']."<br>".$row['finisher']."</td>";
    echo "<td>".$row['shop_name']."</td>";
    echo "<td>".$row['price']*$row['amount']."<br>(". $row['price']. "*". $row['amount']. ")</td>";
    if($status[$row['status']]=="Ordered"){
		echo "<td><form action='shop_order.php' method='post'>";
		echo "<input type='text' name='done_id' value=".$row['oid']." style='display:none;'>";
		echo "<input type='submit' value='Done'></form></td>";
		
		echo "<td><form action='shop_order.php' method='post'>";
		echo "<input type='hidden' name='cancel_id' value=".$oid." style='display:none;'>";
		echo "<input type='hidden' name='cancel_amount' value=".$row['amount']." style='display:none;'>";
		echo "<input type='submit' value='Cancel'></form></td>";
	}else 
		echo "<td></td><td></td>";
    echo "</tr>";
    $arr_i++;
  }
  ?>
  
 <?php
  unset($_POST['done_id']);
  unset($_POST['cancel_id']);
  unset($_POST['cancel_amount']);
 ?>
 </table>
</div>

</body>
</html>