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

// Clear User session and logout
unset($_SESSION['User']);
unset($_SESSION['Token']);

// redirect back to login form
header("Location: index.php");			
die();


?>