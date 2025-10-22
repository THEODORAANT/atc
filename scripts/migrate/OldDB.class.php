<?php

class OldDB
{
    static private $instance;
	private $link = false;
    private $count = 0;

	public $errored   = false;
	public $error_msg = false;
	
	static public $queries    = 0;

    
	public static function fetch()
	{	    
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
	}
	

	function __construct()
	{

	}
    
	function __destruct() 
	{
		$this->close_link();
	}
		
	private function open_link() 
	{
		$Conf = Conf::fetch();

		$dsn_opts = array();
		$dsn_opts['host'] 	= $Conf->DB['host'];
		$dsn_opts['dbname'] = $Conf->DB['db'];

		if (isset($Conf->DB['socket']))  $dsn_opts['unix_socket'] = $Conf->DB['socket'];
		if (isset($Conf->DB['port'])) 	 $dsn_opts['port'] 	 	  = (int)$Conf->DB['port'];

		$dsn = 'mysql:';

		foreach($dsn_opts as $key=>$val) {
			$dsn .= "$key=$val;";
		}

		$opts = NULL;

		$opts = array(1002 => "SET NAMES 'latin1'");

		try {
			$this->link = new PDO($dsn, $Conf->DB['user'], $Conf->DB['pass'], $opts);
			if ($this->link) $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} 
		catch (PDOException $e) {

			Console::log("Could not create DB link!", 'error');
			Console::log($e->getMessage(), 'error');

			return false;
		}

		
	}
	
	private function close_link() 
	{
		$this->link = null;
	}
	
	private function get_link() 
	{    
		if (!$this->link) {
			$this->open_link();
		}
		
		return $this->link;
	}
	
	public function execute($sql) 
	{
		if (is_object($sql)) $sql = $sql->get_sql();

		Console::log($sql, 'db');
		$this->errored = false;

		
		$link = $this->get_link();
	    if (!$link) return false;
		
		try {
			$result = $link->exec($sql);
			self::$queries++;
		}
		catch (PDOException $e) {
			Console::log("Invalid query: " . $e->getMessage(), 'error');
			$this->errored = true;
			$this->error_msg = $e->getMessage();
			return false;
		}
		
		if ($link->errorCode() && $link->errorCode()!='0000') {
			$err = $link->errorInfo();
			Console::log("Invalid query: " . $err[2], 'error');
			$this->errored = true;
			$this->error_msg = $err[2];
			return false;
		}

		$newid	= $link->lastInsertId();

		if (!$newid) {
		    self::$queries++;
			return $result;
		}
		
		return $newid;
		
	}
	
	
	public function get_rows($sql) 
	{

		if (is_object($sql)) $sql = $sql->get_sql();

		Console::log($sql, 'db');
		$this->errored = false;
		
		$link = $this->get_link();
	    if (!$link) return false;
		
		try {
			$result = $link->query($sql);
			self::$queries++;
		}
		catch(PDOException $e) {
			Console::log("Invalid query: " . $e->getMessage(), 'error');
			$this->errored = true;
			$this->error_msg = $e->getMessage();
			return false;
		}

		if ($link->errorCode() && $link->errorCode()!='0000') {
			$err = $link->errorInfo();
			Console::log("Invalid query: " . $err[2], 'error');
			$this->errored = true;
			$this->error_msg = $err[2];
			return false;
		}

		if ($result->errorCode() && $result->errorCode()!='0000') {
			$err = $result->errorInfo();
			Console::log("Invalid query: " . $err[2], 'error');
			$this->errored = true;
			$this->error_msg = $err[2];
			return false;
		}

		
		if ($result) {
			$r = $result->fetchAll(PDO::FETCH_ASSOC);
			$result = null;
			if (Util::count($r)) {
				return $r;
			}else{
				return false;
			}
		}
		
		return false;
	}
	
	public function get_rows_flat($sql) 
	{
		if (is_object($sql)) $sql = $sql->get_sql();

		Console::log($sql, 'db');
		$this->errored = false;
		
		$link = $this->get_link();
	    if (!$link) return false;
		
		try {
			$result = $link->query($sql);
			self::$queries++;
		}
		catch(PDOException $e) {
			Console::log("Invalid query: " . $e->getMessage(), 'error');
			$this->errored = true;
			$this->error_msg = $e->getMessage();
			return false;
		}

		if ($link->errorCode() && $link->errorCode()!='0000') {
			$err = $link->errorInfo();
			Console::log("Invalid query: " . $err[2], 'error');
			$this->errored = true;
			$this->error_msg = $err[2];
			return false;
		}

		if ($result->errorCode() && $result->errorCode()!='0000') {
			$err = $result->errorInfo();
			Console::log("Invalid query: " . $err[2], 'error');
			$this->errored = true;
			$this->error_msg = $err[2];
			return false;
		}
		
		if ($result) {
			$r = $result->fetchAll(PDO::FETCH_COLUMN, 0);
			$result = null;
			if (Util::count($r)) {
				return $r;
			}else{
				return false;
			}
		}
		
		return false;
	}


	
	public function get_row($sql) 
	{
		if (is_object($sql)) $sql = $sql->get_sql();

		Console::log($sql, 'db');
		$this->errored = false;
		
		$link = $this->get_link();
	    if (!$link) return false;
		
		try {
			$result = $link->query($sql);
			self::$queries++;
		}
		catch(PDOException $e) {
			Console::log("Invalid query: " . $e->getMessage(), 'error');
			$this->errored = true;
			$this->error_msg = $e->getMessage();
			return false;
		}
		
		if ($result) {
			$r = $result->fetch(PDO::FETCH_ASSOC);
			$result = null;

			if (Util::count($r)) {
				return $r;
			}else{
				return false;
			}
			
		}
		
		return false;
		
		
	}
	
	public function get_value($sql) 
	{
		if (is_object($sql)) $sql = $sql->get_sql();

		$result = $this->get_row($sql);
		
		if (is_array($result)) {
			foreach($result as $val) {
				return $val;
			}
		}
		
		return false;
		
	}
	
	public function get_count($sql)
	{
		if (is_object($sql)) $sql = $sql->get_sql();

	    $result = $this->get_value($sql);
	    return intval($result);
	}
	
	public function insert($table, $data, $ignore=false) 
	{
		
		$cols	= array();
		$vals	= array();
		
		foreach($data as $key => $value) {
			$cols[] = $key;
			$vals[] = $this->pdb($value);
		}
		
		$sql = 'INSERT'.($ignore?' IGNORE':'').' INTO ' . $table . '(' . implode(',', $cols) . ') VALUES(' . implode(',', $vals) . ')';
		
		return $this->execute($sql);
		
	}
	
	public function update($table, $data, $id_column, $id) 
	{
		
		$sql = 'UPDATE ' . $table . ' SET ';
		
		$items = array();
		
		foreach($data as $key => $value) {
			$items[] =  $key . '=' . $this->pdb($value);
		}
		
		$sql .= implode(', ', $items);
		
		$sql .= ' WHERE ' . $id_column . '=' . $this->pdb($id);
		
		return $this->execute($sql);
		
		
	}
	
	public function delete($table, $id_column, $id, $limit=false) 
	{
		
		$sql = 'DELETE FROM ' . $table . ' WHERE ' . $id_column . '=' . $this->pdb($id);
		
		if ($limit) {
			$sql .= ' LIMIT ' . $limit;
		}
		
		
		return $this->execute($sql);
		
	}
	
	
	public function pdb($value)
	{
		// Stripslashes
		if (get_magic_quotes_runtime()) {
			$value = stripslashes($value);
		}
		
		$link = $this->get_link();
	    if (!$link) return false;

		// Quote
		switch(gettype($value)) {
			case 'integer':
			case 'double':
				$escape = $value;
				break;
			case 'string':
				$escape = $link->quote($value);
				break;
			case 'NULL':
				$escape = 'NULL';
				break;
			default:
				$escape = $link->quote($value);
		}

		return $escape;
	}
	
	public function get_table_meta($table)
	{
		$sql	= 'SELECT * FROM ' . $table . ' LIMIT 1';
		
		$link = $this->get_link();

		$result = $link->query($sql);
		self::$queries++;
		
		if ($result) {			
			$r	= array();
			$i 	= 0;
			while ($i < $result->columnCount()) {
			    $r[] = $result->fetchColumn($i);
				$i++;
			}
			$result = NULL;
			return $r;
		}else{
			
			Console::log("Invalid query: " . $link->error, 'error');
			return false;
		}
		
	}
	
	public function implode_for_sql_in($rows)
    {
        foreach($rows as &$item) {
            $item = $this->pdb($item);
        }
        
        return implode(', ', $rows);
    }
	

	public function get_client_info()
	{
		$link = $this->get_link();
		return $link->getAttribute(PDO::ATTR_CLIENT_VERSION);
	}

	public function get_server_info()
	{
		$link = $this->get_link();
		return $link->getAttribute(PDO::ATTR_SERVER_VERSION);
	}

	public function get_query_count()
	{
		return self::$queries;
	}
	
}
?>