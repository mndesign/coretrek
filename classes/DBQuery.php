<?PHP
/*	
	Disclaimer
	
	While we make every effort to ensure that this code is fit for its intended purpose, we
	make no guarantees as to its functionality. CoreTrek AS will accept no
	responsibility for the loss of data or any other damage or financial loss caused by use
	of this code.


	Copyright
	
	This programming code is copyright of CoreTrek AS. Permission to run this code is given to 
	approved users of CoreTrek's publishing system CorePublish.
	
	This source code may not be copied, modified or otherwise repurposed for use by a third
	party without the written permission of CoreTrek AS.
	
	Contact webmaster@coretrek.com for information.
	
*/

/**
*	Class DBQuery  - abstract class for dbconnection..
*   Author: 
*      Ståle Undheim
*   
*   Modified by:
*		Arve Skjørestad, 27.10.2000
*	  	Arve Skjørestad, 27.06.2001
*			-commented all functions properly	  
*
*   @package cplib 
*        
*/
class DBQuery
	{
	var $data;
	var $fieldnames;
	var $numrows;
	var $errormsg;
	var $newid;
    var $pos;

	/**
	*       The constructor retrieves the full resultset from the database
	*		and stores it in $this->data. If the result is not a select
	*		(ie. $select == false), the number of rows affected is set.
	*		If the query failed, $this->errormsg will be set. If $select
	*		is set to true when the query isn't a select statement, PHP
	*		will put out an errormessage for the lines 55, 64, 69 and 72.
	*		This function should only be used from DBConnection::doQuery()
	*
	*		@see DBConnection::doQuery()
	*		@param int $resultid (int): The result id returned by a mysql_query
	*		@param int $connection (int): The connection id returned by mysql_connect
	*		@param boolean $select (boolean): Set to true if this is a select statement	false otherwise.
	*/
	function DBQuery($resultid,$connection,$select){
		// type specific code goes here		
	}
	
	/**
	*	function that returns the result of the query as a large array.
	*	
	*	@return array $this->data, the whole result from the query as an array
	*/
	function GetResult() {
		// type specific code goes here		
	}
	
	/**
	*	function that returns either the number of rows returned from a query, or the number of rows affected in an insert/delete/update.
	*
	*	@return int either the number of rows returned from a query, or the number of rows affected in an insert/delete/update.
	*/
	function GetNumrows(){
		// type specific code goes here		
	}

	/**
	*  	returns the number of fields in the query result (select only)
	*
	*	@return int the number of fields in the query result (select only)
	*/
	function GetNumfields(){
		// type specific code goes here		
	}

	/**
	*	Returns an array of the fieldsname from the query result (select only)
	*
	*	@return array an array of the fieldsname from the query result (select only)
	*/
	function GetFieldnames(){
		// type specific code goes here		
	}

	/**
	*	Returns the error message returned if the query failed.
	*
	*	@return string the error message returned if the query failed.
	*/
	function GetError(){
		// type specific code goes here		
	}
	
	/**
	*	returns the last insert id if there is one
	*
	*	@return the insert id if there is one.
	*/
	function GetNewId(){
		// type specific code goes here		
	}

	/**
	*	function that returns the next row in the recordset. Useful when looping through a query result, like e.g.
    *    while($foo=$DBQuery->getNextRow()){
	*		echo $foo["id"];
	*	}
	*
    *   @return array one row, useful when going through rows, returns false when reached the last row
	*/
    function GetNextRow(){
		// type specific code goes here		
	}

	/**
	*	returns the row with the row number $id 
	*	   
	*	@param int $id: the number/id of the row to get
	*   @return the row with the row number $id
	*/
    function GetRowById($id){
		// type specific code goes here		
	}
    
	/**
	*	returns "upper left", first column in first row if query result
	*
	*   @return mixed "upper left", first coloumn in first row.
	*/
	function GetFirst(){
		// type specific code goes here		
	}
    
	/**
    *   function that resets the counter used in GetNextRow(), if you want to start over
	*   
	*/
    function reset(){
		// type specific code goes here		
	}
    

}?>
