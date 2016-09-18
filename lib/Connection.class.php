<?php
class Connection {
	protected $link;
	protected $error = false;
	private $server, $username, $password, $db;
	public function __construct($server, $username, $password, $db) {
		$this->server = $server;
		$this->username = $username;
		$this->password = $password;
		$this->db = $db;
		$this->connect();
	}
	
	/**
	 * 
	 * Connecting to the MYSQL server 
	 */
	public function connect() {
		if ($this->link)
			$this->close();
		$this->link = mysql_connect($this->server, $this->username, $this->password);
		if (!$this->link) {
			$this->error = 'connect';
		} else {
			$success = mysql_select_db($this->db, $this->link);
			if (!$success) {
				$this->error = 'database';
			}
		}
	}
	
	public function close() {
		mysql_close($this->link);
		unset($this->link);
	}
	
	public function getError() {
		return $this->error;
	}
	
	public function getLink() {
		return $this->link;
	}
	
	public function isConnected() {
		return isset($this->link);
	}
	
	public function __sleep() {
		return array('server', 'username', 'password', 'db');
	}

	public function __wakeup() {
		$this->connect();
	}
}
