<?PHP
/**
*   Index file for login
*
*   @author John Michaelsen 2004
*/

// Don't display message about eregi beeing deprecated.
error_reporting(E_ALL ^ E_DEPRECATED);

// Start session
session_start();

?>
<html>
<link rel="stylesheet" href="css/style.css"/>
<body>

<div class="block">
	<form method="post" action="login.php">
		<p class="login_msg">Login</p>
		<?php if(isset($_SESSION['error'])) echo $_SESSION['error'] ?>
		<input class="username" type="text" name="username" placeholder="Username" required><br />
		<input class="password" type="password" name="password" placeholder="password" required><br />
		<input class="submit" type="submit" name="b" value="Login">
	</form>
</div>

</body>
</html>