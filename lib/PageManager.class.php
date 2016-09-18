<?php
/**
 *
 *
 *
 */
class PageManager {

	private $loaded = false;
	
	private static $current;
	
	private $dbservice;

	public function __construct() {
		if (!isset(PageManager::$current)) {
			PageManager::$current = $this;
		}
		$this->dbservice = new DatabaseService();
		$this->configure();
	}
	
	public static function getCurrent() {
		return PageManager::$current;
	}
	
	public function configure($file = "sow.ini") {
		$config     = parse_ini_file($file, true);
		$host       = $config['database']['host'];
		$username   = $config['database']['username'];
		$password   = $config['database']['password'];
		$database   = $config['database']['database'];
		$connection = new Connection($host, $username, $password, $database);
		$this->dbservice->newConnection($connection);
		
//		$query = "select * from `sow-pages`";
//		$this->dbservice->query($query);
//		
//		$result = $this->dbservice->fetchArray();
	}
	
	public function loadPage() {
		$page = new Page();
		$page->loadConfig();
		$page->loadActions();
		$page->runActions();
		$page->load();
		return $page;
	}
}
