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

// Check if user is authorized
if(!isset($_SESSION['User'])) {
	header("Location: index.php");			
	die();
}

// This code needs to be here on every page to connect to the database   
include("classes/Query.php");  
include("classes/DBQuery.php");
include("classes/DBConnection.php");
include("classes/MysqlDBQuery.php");
//$db = new DBConnection("atest" ,"test123" , "custom_atest","mysql50.intra.coretrek.com"); // localhost
$db = new DBConnection("root" ,"" , "custom_atest","localhost"); // localhost
$data = new Query();

?>

<html>
<link rel="stylesheet" href="css/style.css"/>
<body>

<?PHP
if (is_object($db) && is_resource($db->connection)) {
		
	// Get all posts and authors name
	if($posts = $data->posts($db)) {
		
		// Print all posts
		foreach($posts as $post) {
			
			require('template/posts.template');
		}
	} else {
		
		// If no posts
		echo 'Du har ikke opprettet noen artikler';
	}
}
?>
<form method="post" action="loggedout.php" class="center">
	<input class="submit" type="submit" name="button" value="Logg Ut">
</form>

</body>
</html>