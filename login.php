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

// This code needs to be here on every page to connect to the database                                       
include("classes/Csrf.php");
include("classes/Query.php");
include("classes/DBQuery.php");
include("classes/DBConnection.php");
include("classes/MysqlDBQuery.php");
//$db = new DBConnection("atest" ,"test123" , "custom_atest","mysql50.intra.coretrek.com"); // localhost
$db = new DBConnection("root" ,"" , "custom_atest","localhost"); // localhost
$data = new Query();
$csrf = new Csrf();

// Remove any error message (if any)
unset($_SESSION['error']);

if (is_object($db) && is_resource($db->connection)) {

	// Would like to crypt $_POST['password'] before checking with DB, but would need to crypt password before its inserted into DB (registration)

	// If user exists
	if($id = $data->credentials($db, $_POST['username'], $_POST['password'])) {
		
		//generate new CSRF key
		$csrf->generate();

		// Set SESSION to user id, and redirect
		$_SESSION['User']['Id'] = $id;	
		
		header("Location: loggedin.php");		
		die();		
		
	} else {
		
		// Redirect user back to index with error msg
		$_SESSION['error'] = 'Feil brukernavn eller passord';
		
		header("Location: index.php");			
		die();
		
	}
}



?>