<?php
session_start();

$_SESSION['Authenticated']=false;
$dbservername='localhost';
$dbname		 ='order_sys';
$dbusername	 ='examdb';
$dbpassword	 ='examdb';

try
{
  if (!isset($_POST['uname']) || !isset($_POST['pwd']))
  {
    header("Location: index.php");
    exit();
  }
  if (empty($_POST['uname']) || empty($_POST['pwd']))
    throw new Exception('Please input username and password.');

  $uname=$_POST['uname'];
  $pwd=$_POST['pwd'];
  $conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbpassword);
  # set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
  $stmt=$conn->prepare("SELECT * from user where username=:username");
  $stmt->execute(array('username' => $uname));
  if ($stmt->rowCount()==1)
  {
    $row = $stmt->fetch();
    if ($row['password']==hash('sha256',$row['salt'].$_POST['pwd']))
    {
      $_SESSION['Authenticated']=true;
	  $_SESSION['UserID']	=$row['uid'];
      $_SESSION['Username']	=$row['username'];
      $_SESSION['Phone']	=$row['phone'];
	  header("Location: home.php");
      exit();
	}
	else
	  throw new Exception('Login failed.');
  }
  else
	throw new Exception('Login failed.');
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
?>
