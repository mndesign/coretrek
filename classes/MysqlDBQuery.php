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
*   
*   @package cplib    
*/
class MysqlDBQuery extends DBQuery
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
	function MysqlDBQuery($resultid,$connection,$select)
		{
		// Added by Johnny Egeland to safely define the errormessage.
		$this->errormsg = false;

		
		//	Check that the result is true/ok and that it's a select statement, 
		//	or just set the erromsg.
		if ($resultid && $select)
			{

			//	Get the resulting fieldnames
			for ($i=0;$i<mysql_num_fields($resultid);$i++)
				{
				$tmp = mysql_fetch_field($resultid,$i);
				$this->fieldnames[$i] = $tmp->name;
				}

			//	Get the number of rows returned
			$this->numrows = mysql_num_rows($resultid);
						
			//	Get the result set and store it in the data array
			//while ($this->data[] =  mysql_fetch_array($resultid));			
			while ($this->data[] =  mysql_fetch_array($resultid,MYSQL_ASSOC));			

			array_pop($this->data);
			mysql_free_result($resultid);	
			}
		
		//	Set numrows to number of affected rows if it's not a select	query
		else if ($resultid && !$select)
			{
			$this->numrows = mysql_affected_rows($connection);	
			$this->newid = mysql_insert_id($connection);
			}
		//	Set the error if something has failed
		else
			$this->errormsg = mysql_error($connection);

		//	Free the memory used by the result
        $this->pos = 0;

		}
	
	/**
	*	function that returns the result of the query as a large array.
	*	
	*	@return array $this->data, the whole result from the query as an array
	*/
	function GetResult()
		{
		if ($this->errormsg)
			return false;
		else
			return $this->data;
		}

	/**
	*	function that returns either the number of rows returned from a query, or the number of rows affected in an insert/delete/update.
	*
	*	@return int either the number of rows returned from a query, or the number of rows affected in an insert/delete/update.
	*/
	function GetNumrows()
		{
		if ($this->errormsg)
			return false;
		else
			return $this->numrows;
		}

	/**
	*  	returns the number of fields in the query result (select only)
	*
	*	@return int the number of fields in the query result (select only)
	*/
	function GetNumfields()
		{
		if ($this->errormsg)
			return false;
		else
			return count($this->fieldnames);
		}

	/**
	*	Returns an array of the fieldsname from the query result (select only)
	*
	*	@return array an array of the fieldsname from the query result (select only)
	*/
	function GetFieldnames()
		{
		if ($this->errormsg)
			return false;
		else
			return $this->fieldnames;
		}		

	/**
	*	Returns the error message returned if the query failed.
	*
	*	@return string the error message returned if the query failed.
	*/
	function GetError()
		{
		if ($this->errormsg)
			return $this->errormsg;
		else
			return false;
		}
	
	/**
	*	returns the last insert id if there is one
	*
	*	@return the insert id if there is one.
	*/
	function GetNewId()
		{
		if ($this->newid)
			return $this->newid;
		else
			return $this->errormsg;		
		}

	/**
	*	function that returns the next row in the recordset. Useful when looping through a query result, like e.g.
    *    while($foo=$DBQuery->getNextRow()){
	*		echo $foo["id"];
	*	}
	*
    *   @return array one row, useful when going through rows, returns false when reached the last row
	*/
    function GetNextRow()
        {
        if (isset($this->data[$this->pos]))
            return $this->data[$this->pos++];
        else
            return false;
        }

	/**
	*	returns the row with the row number $id 
	*	   
	*	@param int $id: the number/id of the row to get
	*   @return the row with the row number $id
	*/
    function GetRowById($id){
        if (isset($this->data[$id]))
            return $this->data[$id];
        else
            return false;
    }
    
	/**
	*	returns "upper left", first column in first row if query result
	*
	*   @return mixed "upper left", first coloumn in first row.
	*/
	function GetFirst() {
      return $this->data[0][$this->fieldnames[0]];
      // return $this->data[0][0];
    }
    
	/**
    *   function that resets the counter used in GetNextRow(), if you want to start over
	*   
	*/
    function reset(){
        $this->pos = 0;
    }
	
	/**
	* 	function that returns the word for the 'trigger' field name. this is a hack, basically
	*	to make a spesific table work in both mysql and ms sql
	*
	*	@access private
	*	@return string the trigger word as a string
	*/
    function getTriggerFieldName() {
		return "trigger";
    }

    
}?>
