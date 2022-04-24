<?php
session_start();
$_SESSION['Authenticated']=false;

$dbservername='localhost';
$dbname		 ='order_sys';
$dbusername	 ='examdb';
$dbpassword	 ='examdb';

try {
  if (!isset($_POST['uname']) || !isset($_POST['pwd']) ||
	  !isset($_POST['cfm'])   || !isset($_POST['phone']))
  {
    header("Location: index.php");
    exit();
  }
  
  //input error handling
  if(empty($_POST['uname'])	) 	$_SESSION['name_error']="Required!";
  if(empty($_POST['pwd'])	)	$_SESSION['pwd_error'] ="Required!";
  if(empty($_POST['cfm'])	) 	$_SESSION['cfm_error'] ="Required!";
  if(empty($_POST['phone'])	) 	$_SESSION['pho_error'] ="Required!";
  if(!isset($_SESSION['name_error']) && 
	 !preg_match('/^[a-zA-Z0-9]+$/', $_POST['uname']))
		$_SESSION['name_error']="Invalid username";
  if(!isset($_SESSION['cfm_error']) && $_POST['pwd'] != $_POST['cfm'])
		$_SESSION['cfm_error']="Password mismatched";
  if(!isset($_SESSION['pho_error']) &&
	 !preg_match('/^[0-9]*$/', $_POST['phone']))
		$_SESSION['pho_error']="Numbers only";
  
  //exception handling
  if(isset($_SESSION['name_error']) || isset($_SESSION['name_error']) ||
	 isset($_SESSION['cfm_error'])  || isset($_SESSION['pho_error']))
	throw new Exception("Register failed.");
  
  //register process
  $uname	= $_POST['uname'];
  $pwd		= $_POST['pwd'];
  $comfirm	= $POST['cfm'];
  $phone	= $_POST['phone'];
  $conn 	= new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt=$conn->prepare("SELECT username FROM user WHERE username=:username");
  $stmt->execute(array('username' => $uname));

  if ($stmt->rowCount()==0)
  {
	$salt=strval(rand(1000,9999));
	
    $hashvalue=hash('sha256', $salt.$pwd);
    $stmt=$conn->prepare("insert into user (username,password, salt, phone) values (:username, :password, :salt, :phone)");
    $stmt->execute(array(':username' => $uname, ':password' => $hashvalue, ':salt' => $salt, ':phone' => $phone));
	$_SESSION['reg_suc']=true;
	header("Location: index.php");
	exit();
  }
  else{
	$_SESSION['name_error']="name has already been used!";
	throw new Exception("Register failed.");  
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
		  window.location.replace("index.php");
        </script>
      </body>
	</html>
EOT;
}
?>
</body>
</html>
