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
include("classes/Csrf.php");    
include("classes/Query.php");                         
include("classes/DBQuery.php");
include("classes/DBConnection.php");
include("classes/MysqlDBQuery.php");
//$db = new DBConnection("atest" ,"test123" , "custom_atest","mysql50.intra.coretrek.com"); // localhost
$db = new DBConnection("root" ,"" , "custom_atest","localhost"); // localhost
$data = new Query();
$csrf = new Csrf();

?>

<html>
<link rel="stylesheet" href="css/style.css"/>
<body>

<?PHP
if (is_object($db) && is_resource($db->connection)) {
	
	// If request for save, update DB with new values
	if(isset($_POST['save'])) {
		if( $csrf->validate( $_POST['csrf_key'] )) {
			if( $data->posts_save( $db, $_POST ) ) {
				// Saved to db and redirect in 2 sec
				echo '<div class="wrap">Artikkelen er oppdatert!</div>';
				header('Refresh: 2; URL=loggedin.php');
				die();
			}				
		}
	}
	
	// If user has posts
	if( $post = $data->posts_current( $db, $_GET['id'] ) ) {
		
		// Print all posts from active user
		foreach($post as $posts) {

			require('template/posts_edit.template');
		}
	} else {
		echo 'Artikkelen finnes ikke!';
		header('Refresh: 2; URL=loggedin.php');
		die();
	}
}
?>

</body>
</html>