<?php
/**
 *
 *
 *
 */
class DatabaseService {

	private $connections;
	
	private $current = -1;
	
	private $resource;
	
	public function __construct() {
		
	}
	
	public function newConnection($connection) {
		$this->connections[] = $connection;
		$current++;
	}
	
	public function query($query) {
		if ($this->current === -1) {
			$this->resource = mysql_query($query);
		} else {
			if (!$this->isConnected())
				$this->reconnect();
			$this->resource = mysql_query($query, $this->connections[$this->current]);
		}
		if (!is_resource($this->resource)) {
			throw new Exception(mysql_error());
		}
	}
	
	public function fetchArray() {
		if (!is_resource($this->resource)) {
			ErrorService::logError(mysql_error());
		}
		return mysql_fetch_array($this->resource);
	}
	
	public function disconnect() {
		$this->connections[$this->current]->close();
	}
	
	public function reconnect() {
		$this->connections[$this->current]->connect();
	}
	
	public function fetchAssoc() {
		return mysql_fetch_assoc($this->resource);
	}
	
}
