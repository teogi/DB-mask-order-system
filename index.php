<?php
  session_start();
  $_SESSION['Authenticated']=false;
?>

<!DOCTYPE html>
<html>
<head>
<title>Index</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
<div class='container'>
	<h1 class='mx-auto mb-4 mt-2'>Mask Ordering System</h1>
	<h2>Login</h2>
	<form action="login.php" method="post">
		<div class="form-group">
			<label for="uname">Username:</label>
			<input class="form-control col-sm-6" type="text" name="uname" placeholder="Enter Username"/>
		</div>
		<div class="form-group">
			<label for="pwd">Password:</label>
			<input class="form-control col-sm-6" type="password" name="pwd" placeholder="Enter password" />
		</div>
		<div class="form-group">
			<input class="btn btn-primary" type="submit" value="Login">
		</div>
	</form>
	<div id="register">
	<h2>Create Account</h2>
	<form class="form-group" action="register.php" method="post">
		<div class="form-group">
			<label for="uname">Username:</label>
			<div class='container row'>
				<input class="form-control col-sm-6" type="text" name="uname" placeholder="Enter Username" />
				<span class="col-sm-4" style="color:red;"><?php if(isset($_SESSION['name_error'])) echo $_SESSION['name_error'];?></span>
			</div>
		</div>
		<div class="form-group">
			<label for="pwd">Password:</label>
			<div class='container row'>
				<input class="form-control col-sm-6" type="password" name="pwd"  placeholder="Enter password"/>
				<span class="col-sm-4" style="color:red;"><?php if(isset($_SESSION['pwd_error'])) echo $_SESSION['pwd_error'];?></span>
			</div>
		</div>
		<div class="form-group">
			<label for="cfm">Comfirm Password:</label>
			<div class='container row'>
				<input class="form-control col-sm-6" type="password" name="cfm"  placeholder="Comfirming password"/>
				<span class="col-sm-4" style="color:red;"><?php if(isset($_SESSION['cfm_error'])) echo $_SESSION['cfm_error'];?></span>
		</div>
		<div class="form-group">
			<label for="phone">Phone Number:</label>
			<div class='container row'>
				<input class="form-control col-sm-6" type="text" name="phone"  placeholder="Enter phone number"/>
				<span class="col-sm-4" style="color:red;"><?php if(isset($_SESSION['pho_error'])) echo $_SESSION['pho_error'];?></span>
			</div>
		</div>
		<div class="form-group mt-2">
			<input class="btn btn-primary" type="submit" value="Create Account">
			<?php if(isset($_SESSION['reg_suc']) && $_SESSION['reg_suc']==true){
						echo "Register success!";
						$_SESSION['reg_suc']=false;
				}
			?>
		</div>
	</div>
	</form>
</div>
</body>
</html>
<?php
	session_unset();
	session_destroy();
?> 