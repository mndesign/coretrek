<?php
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
*	Class DBConnection
*   Author:
*      Ståle Undheim
*
*	Modified by:
*		Arve Skjørestad, 13.10.2000
*		Arve Skjørestad, 20.04.2001
*		Arve Skjørestad, 10.07.2001
*			- added notifyError() method, notifies drift@coretrek.no if a connection failure occurs.
*		Arve Skjørestad, 07.08.2001
*			- fixed host name finding on lines 57-61
*			- fixed line 68
*		Arve Skjørestad, 08.08.2001
*			- Finally found a better way of finding host name and setting "localhost" where we have to have this.
*	06.02.2002	Arve Skjørestad
*        - updated with configurable email adress and bette format on the mail beeing sent.
*		- now possible to turn on and off logging
*		- time measuring on queries.
*	25.02.2002	Arve Skjørestad
*		- added mysql error message in error mail..
*		- changed the replacing of "coretrek.no" with "coretrek.com" with reverse order..
*	12.09.2002	Arve Skjoerestad
*		- fixed bug caused by staale undheim(the bugmaker) causing people not to be able to write
*		  the word "select" in the strings passed to the database
*
*	NOTE:
*	This is a spesial version for CPLIB
*
*
*   @package cplib
*/
class DBConnection {
	var $username;
	var $database;
	var $password;
	var $hostname;
	var $databasetype;
	var $connection;
    var $querystring;
	var $start_tstamp;
	var $document_start_time ;
	var $num_queries;
	var $currentExecTime;
	var $spaceChar;
	var $breakChar;

	/**
	*	constructor that sets up a database connection, and prepare for queries. You need to spesify database username, password
	*	name and host. This versjon of DBConnection is a special version for CPLIB	and cannot be used
	*	other places. The function checks whether the database host is the same as the host the script is
	*	executed, and automatically sets it to "localhost" if host is the same. It handles both "coretrek.no"
	*	and "coretrek.com" as well as other domains.
	*	If database connection fails, it will send a mail to the adress specified
	* 	in cplib.config, and then redirect user to "<domainpath>dbfeil.php" (using header)
	*
	*	@param string $_username: the database username
	*	@param string $_password: the database password(in clear text)
	*	@param string $_database: the database name
	*	@param string $_hostname: the database host, like "whitestar.coretrek.com".
	*	@param string $_databasetype: the database type, like "mysql" or "mssqlserver".
	*	@return DBConnection a DBConnection object.
	*/
	function DBConnection($_username,$_password,$_database,$_hostname,$_databasetype="mysql"){
		global $DOMAIN, $CP_HOST;

		$this->spaceChar = '&nbsp;';
		$this->breakChar = '<br>';
		$this->boldChar = '<b>';

		// Reset query counter
		$this->num_queries=0;

		if(is_object($DOMAIN))
			$htmlroot =$DOMAIN->getDomainName();

		// NOTE that $CP_HOST must be set manually in the Apache config file like this:
		// SetEnv CP_HOST servername.domain.com
		$servername = $CP_HOST;

		// Fix hostname: replace ".coretrek.no" with ".coretrek.com"
        //$servername = str_replace(".coretrek.com",".coretrek.no",$servername);
        //$_hostname = str_replace(".coretrek.com",".coretrek.no",$_hostname);

        // Make sure that we don't get any conflicts on db_host beeing the same host
		if($_hostname== $servername)
			$_hostname = "localhost";

		$this->username = $_username ;
		$this->password = $_password;
		$this->database = $_database;
		$this->hostname = $_hostname;
		$this->databasetype = $_databasetype;
        $this->querystring="<pre>";

        // Database type dependent connection.
		switch ($this->databasetype) {
			case "mssqlserver":
				//@requireClass("MsSqlServerDBQuery");
				$this->connection = mssql_pconnect($this->hostname,$this->username,$this->password);
                mssql_query("set textsize 1048576;", $this->connection);
                $selectDbRes = mssql_select_db($this->database,$this->connection);
				break;
			case "mysql":
			default:
				//@requireClass("MysqlDBQuery");
				$this->connection = mysql_pconnect($this->hostname,$this->username,$this->password);
				$selectDbRes =mysql_select_db($this->database,$this->connection);
				break;
		}

		// Assure the connection was established, send to dbfeil.php if not
		if (!$this->connection){
			$this->notifyError($DOMAIN);
			header("Location: ".$htmlroot."/dbfeil.php?connection");
			die();
		}

		if (!$selectDbRes){
			$this->notifyError($DOMAIN);
			header("Location: ".$htmlroot."/dbfeil.php?select");
			die();
		}
	} // DBConnection()

	/**
	*	function to perform an query. return a new DBQuery object, which has several methods available.
	*	Note that you have to make sure that your sql is correct, and that all "dangerous" characters
	*	are escaped. Its also possible to limit number of returned rows and offset by using the 2. and
	*	3. parameter.
	*
	*	@param string $SQLStatement: the sql string
	*	@param int $numrows: set this if you want to limit the result to a number of rows.
	*   @param int $startpoint: the offset to start, e.g. if you want to start at row 20.
	*	@return DBQuery a DBQuery object.
	*/
	function DoQuery($SQLStatement,$numrows=0,$startpoint=0){
		global $CPLIB_ENABLE_DBLOG,$LOG;

		if ($CPLIB_ENABLE_DBLOG==1) {
			$this->resetTime();
		}

        // Switch on which type...
		switch ($this->databasetype) {
			case "mssqlserver":
				// Check if the query is limited
                if($numrows>0 && eregi("SELECT", $SQLStatement)) {
					eregi_replace("SELECT ", "SELECT TOP " . $numrows+$startpoint . " ", $SQLStatement);
				}

                				// do query
                $SQLStatement = str_replace("0000-00-00", "1970-01-01", $SQLStatement);

                //echo "<pre>Executing: $SQLStatement</pre>\n";
                mssql_query("BEGIN TRANSACTION");
                $queryResult = mssql_query($SQLStatement,$this->connection);

                // $isSelectStatement = eregi("select",$SQLStatement);
				// IMPORTANT. The substr() needs to be there, else update statements with
				// the string 'select' in the string be taken as select statements..
				$tmp =  new MsSqlServerDBQuery(
                    $queryResult, $this->connection, (eregi("select",substr($SQLStatement,0,10))), $startpoint
                );
                mssql_query("COMMIT");
				break;
			case "mysql":
			default:
				// Check if the query is limited
				if($numrows>0)
					$SQLStatement .= " LIMIT $startpoint,$numrows";

				// Do query
				// IMPORTANT. The substr() needs to be there, else update statements with
				// the string 'select' in the string be taken as select statements..
				$tmp =  new MysqlDBQuery(mysql_query($SQLStatement,$this->connection),
				$this->connection,((eregi("select",substr($SQLStatement,0,10)))));
				//$this->connection,((preg_match("select/i",substr($SQLStatement,0,10))))); // /i = case insensitive. Must check to see if this works :o)
				break;
		}


		if ($CPLIB_ENABLE_DBLOG==1) {
			// Add query string to history
			$this->querystring .= $SQLStatement ."<br>" ;
		}


		if ($CPLIB_ENABLE_DBLOG==1) {
		$this->getTime();
		}

        if ($CPLIB_ENABLE_DBLOG==1 && is_object($LOG) && $LOG->isDebugEnabled()) {
			$LOG->debug(__FILE__,__LINE__, $SQLStatement . " " .$this->currentExecTime);
        }

		$this->num_queries++;

		return $tmp;
	} // doQuery()

	/**
	*	function that returns all queries done by this object. its returned as a
	*	HTML string complete with <br> tags and spacing.
	*	Will only work if logging is enabled in cplib.config
	*
	*	@return string all queries done as a HTML string.
	*/
    function getLog() {
		global $CPLIB_ENABLE_DBLOG;
		if ($CPLIB_ENABLE_DBLOG==1) {
			$temp = str_replace("FROM",$this->breakChar."FROM ".$this->spaceChar.$this->spaceChar.$this->spaceChar.$this->spaceChar.$this->spaceChar.$this->spaceChar.$this->spaceChar,$this->querystring);
			$temp = str_replace("WHERE",$this->breakChar."WHERE". $this->spaceChar.$this->spaceChar.$this->spaceChar,$temp);
			$temp = str_replace("SELECT","SELECT". $this->spaceChar.$this->spaceChar,$temp);
			$temp = str_replace("AND",$this->breakChar."AND". $this->spaceChar.$this->spaceChar.$this->spaceChar.$this->spaceChar.$this->spaceChar.$this->spaceChar.$this->spaceChar.$this->spaceChar.$this->spaceChar.$this->spaceChar,$temp);
			$temp = str_replace("ORDER BY",$this->breakChar."ORDER BY ",$temp);
			$temp = str_replace("LIKE",$this->breakChar."LIKE ",$temp);
			$temp = str_replace("LEFT",$this->breakChar."LEFT ",$temp);
			//$temp = str_replace("JOIN","<b>JOIN</b> ",$temp);
			//$temp = str_replace("NATURAL","<b>NATURAL</b> ",$temp);
			//$temp = str_replace("DESC","<b>DESC</b> ",$temp);
			//$temp = str_replace("ASC","<b>ASC</b> ",$temp);
			return $temp ;
		} else {
			return "No logging is enabled. Se cplib.config, or set \$CPLIB_ENABLE_DBLOG to 1 for doing this";
		}
   } // getLog()

	/**
	*	function that notifies CorePubilsh administrators by email that a web has trouble with
	*	its database connection. Notify happens by mail.
	*
	*	@param Domain $DOMAIN: The domain object
	*/
    function notifyError($DOMAIN){
		// Make all variables global..
		global $CPLIB_MAIL_DBERROR,$CP_HOST, $cgiControl;
		$cgiControl->setErrorHandling("return false;",false);
        $HOSTNAME =$cgiControl->getVar("HOSTNAME");
        $HOST =$cgiControl->getVar("HOST");
		$SERVER_NAME = $cgiControl->getVar("SERVER_NAME");
		$HTTP_HOST = $cgiControl->getVar("HTTP_HOST");
		$REQUEST_URI = $cgiControl->getVar("REQUEST_URI");
		$REQUEST_METHOD = $cgiControl->getVar("REQUEST_METHOD");
		$QUERY_STRING = $cgiControl->getVar("QUERY_STRING");
		$HTTP_REFERER = $cgiControl->getVar("HTTP_REFERER");
		$HTTP_USER_AGENT = $cgiControl->getVar("HTTP_USER_AGENT");
		$REMOTE_ADDR = $cgiControl->getVar("REMOTE_ADDR");

		if(!$HOSTNAME)
			   $HOSTNAME =$HOST;

		// don't mail on enterprise
		if($CP_HOST=="enterprise.intra.coretrek.com")
			return;

		// Define email adresses
		$to =$CPLIB_MAIL_DBERROR;

		// Build message with all available information
		$message= "";

		if(is_object($DOMAIN)){
			$message.= " **CorePublish tilkoblingsinformasjon: ";
			$message.= "\n db_username: ".$DOMAIN->getDbUsername();
			//$message.= "\n\t\t db_password: ".$DOMAIN->getDbPassword();
			$message.= "\n db_name: " .   $DOMAIN->getDbName();
			$message.= "\n db_host: " .  $DOMAIN->getDbHost();
		}

		$message .="\n\n ** Mysql info: ".  mysql_errno().": ".mysql_error()."\n";


$message.= "
** Env. variabler:
 CP_HOST: $CP_HOST
 HOSTNAME: $HOST
 SERVER_NAME: $SERVER_NAME
 HTTP_HOST: $HTTP_HOST
 REQUEST_URI: $REQUEST_URI
 REQUEST_METHOD: $REQUEST_METHOD
 QUERY_STRING: $QUERY_STRING
 HTTP_REFERER: $HTTP_REFERER
 HTTP_USER_AGENT: $HTTP_USER_AGENT
 REMOTE_ADDR: $REMOTE_ADDR
 dato: ".date("Y-m-d  H.i:s")."
";

		// Send the mail
		mail($to,"Feil i databasekobling på $HOSTNAME",$message,"From:$CPLIB_MAIL_DBERROR");
	}  // Notifyerror()

	/**
	*  	function that resets the time between each query, used for debug purposes.
	*/
	function resetTime() {
		$this->start_tstamp = microtime();
		$this->document_start_time = gettimeofday();

	}

	/**
	*  	function that gets the time between each query, used for debug purposes.
	*/
	function getTime() {
		$now_time = gettimeofday();
		$tottime = ($now_time["sec"]*1000000 + $now_time["usec"]) - ($this->document_start_time["sec"]*1000000 + $this->document_start_time["usec"]);
		$str = "(time to process: ". number_format($tottime)." micro seconds)" ;
        $this->querystring .= $str .$this->breakChar.$this->breakChar ;
        $this->currentExecTime = $str;
	}

	/**
	*	function that returns how many queries that have been executed.
	*
	*	@return int Number of queries that have been executed.
	*/
	function getNumQueries() {
		return $this->num_queries;
	}

    /**
    *   This is a horrible hack to handle the fact that trigger is a reserved word in
    *   MS SQL, but not in MySQL. It will return the correct fieldName for the trigger
    *   field based on which db the connection is connected to.
    *   @return String name for the trigger field
    */
    function getTriggerFieldName() {
        switch ($this->databasetype) {
			case "mysql":
                return "trigger";
			case "mssqlserver":
			default:
				return "triggerword";
        }
    }

    /**
    *   function to set which chars to use in the log.
    *   types are 'break' and 'space'
    *
    *   @param string $type break or space
    *   @param string $char which char(s) to use
    */

	function setLogChars($type,$char) {
		switch ($type) {
        case "break":
			$this->breakChar = $char;
			break;
        case "space":
			$this->spaceChar = $char;
			break;
        }

    }

}// end class
?>
