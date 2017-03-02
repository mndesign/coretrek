<?php 

class Query {
	
	function credentials($db, $username, $password) {
			
		// Check if user exists
		$data = $db->DoQuery("SELECT * FROM users WHERE `username` = '{$username}' AND `password` = '{$password}'");	
		
		// returning ID of user
		return $data->data[0]['id'];
	}
	
	function posts($db) {
		
		// Get all posts and name of author
		$data = $db->DoQuery("SELECT articles.*, users.full_name FROM articles INNER JOIN users ON users.id=articles.author");
		
		// returning data
		return $data->data;
	}
	
	function posts_current($db, $id) {
		
		// Get info about current post
		$data = $db->DoQuery("SELECT * FROM articles WHERE `id` = '{$id}'");
		
		// returning data
		return $data->data;
	}
	
	function posts_save($db, $post) {
		
		/* Prevent SQL Injection
		*	Would like to implement code in DoQuery
		*	$stmt = $dbConnection->prepare("UPDATE articles SET `title` = '?', `content` = '?' WHERE `id` = '?'");
		*	$stmt->bind_param('sss', $title, $content, $id);
		*
		*	$stmt->execute()
		*/
		
		// Updates the current post with new values
		$data = $db->DoQuery("UPDATE articles SET `title` = '{$post['header']}', `content` = '{$post['content']}' WHERE `id` = '{$post['id']}'");
		
		// returning data
		return $data;
	}
}

?>