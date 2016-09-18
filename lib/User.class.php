<?php
class User {
	protected $loggedin = false;
	protected $username;
	protected $password;
	protected $data;
	protected $last;
	protected $id;
	protected $current;
	protected $log;
	protected static $categories;
	protected static $user;

	public function __construct($username, $password = "") {
		if (is_numeric($username)) {
			$this->fetchUserById($username);
		} elseif (is_string($username) && is_string($password)) {
			$this->username = $username;
			$this->password = $password;
			$this->fetchUser();
		} else {
			$this->loggedin = false;
		}
		if (!User::$user) {
			User::$user = $this;
		}
	}

	public static function getUser() {
		return User::$user;
	}

	public function update($user = "", $password = "") {
		$this->username = $user;
		$this->password = $password;
		if ($user && $password) {
			$this->fetchUser();
		} else {
			$this->loggedin = false;
		}
	}

	public function isLoggedIn() {
		return $this->loggedin;
	}

	public function getCategories() {
		return User::$categories;
	}

	public function setCategories($cates) {
		User::$categories = $cates;
	}

	public function saveUserData() {
		if (!$this->data) {
			return false;
		}
		if (!$this->data->getElementsByTagName("root")->item(0)) {
			$root = $this->data->appendChild($this->data->createElement("root"));
			$nod = $root->appendChild($this->data->createElement("user"));
			$nod->setAttribute("name", $this->username);
			$nod->setAttribute("password", $this->password);
			$nod->setAttribute("time", time());
		}
		$xml = addslashes($this->data->saveXML());
		$result = mysql_query("update `users` set `data` = '".$xml."' where `id` = '$this->id' limit 1");
		return is_resource($result);
	}

	public function saveUserLog() {
		if (!$this->log) {
			return false;
		}
		if (!$this->log->hasChildNodes()) {
			$root = $this->log->appendChild($this->log->createElement("root"));
			$nod = $root->appendChild($this->log->createElement("user"));
			$nod->setAttribute("name", $this->username);
			$nod->setAttribute("password", $this->password);
			$nod->setAttribute("time", time());
		}
		$xml = addslashes($this->log->saveXML());
		$query = "update `users` set `log` = '".$xml."' where `id` = '$this->id' limit 1";
		$result = mysql_query($query);
		return is_resource($result);
	}

	public function getUserData() {
		return $this->data;
	}

	public function loggout() {
		$this -> loggedin = false;
		return session_destroy();
	}

	public function logAction($action, $command, $args = array()) {
		if (!$this->log) {
			return false;
		}
		$alog = $this->log->getElementsByTagName("actionlog")->item(0);
		if (!$alog) {
			$alog = $this->log->appendChild($this->log->createElement("actionlog"));
		}
		$lenght = $alog->childNodes->lenght;
		if ($lenght >= 100) {
			$alog->removeChild($alog->childNodes->item(0));
		}	$logel = $alog->appendChild($this->log->createElement("action"));
		$logel->setAttribute("time", time());
		$logel->setAttribute("name", $action);
		$logel->setAttribute("command", $command);
		foreach ($args as $key => $arg) {
			if (is_string($key)) {
				$arel = $logel->appendChild($this->log->createElement("arg"));
				$arel->setAttribute("name", $key);
				if (is_string($arg)&&strlen($arg)<=100) {
					$arel->setAttribute("value", utf8_encode($arg));
				} else {
					$arel->setAttribute("value", "..");
				}
			}
		}
		return true;
	}

	public function addCategory($category) {
		if (!$this->isPermitted($category)) {
			$categories = $this->data->getElementsByTagName("categories")->item(0);
			$cate = $this->data->createElement("category");
			$cate->setAttribute("name", $category);
			$categories->appendChild($cate);
		}
	}

	public function removeCategory($category) {
		if ($this->isPermitted($category)) {
			$categories = $this->data->getElementsByTagName("categories")->item(0);
			$cates = $categories->getElementsByTagName("category");
			foreach ($cates as $cate) {
				if ($cate->getAttribute("name")==$category) {
					$categories->removeChild($cate);
				}
			}
		}
	}

	public function isPermitted($category) {
		if (is_string($category)) {
			$categories = $this->data->getElementsByTagName("categories")->item(0);
			if (!$categories) {
				$categories = $this->data->createElement("categories");
				$root = $this->data->getElementsByTagName("root")->item(0);
				if ($root) {
					$root->appendChild($categories);
				} else {
					$root = $this->data->createElement("root");
					$this->data->appendChild($root);
					$categories = $this->data->createElement("categories");
					$root->appendChild($categories);
				}
				return false;
			} else {
				if ($categories->getAttribute("super")=="true") {
					return true;
				}
				$cates = $categories->getElementsByTagName("category");
				foreach ($cates as $cate) {
					if ($cate->getAttribute("name")==$category) {
						return true;
					}
				}
				return false;
			}
		} else {
			return false;
		}
	}

	public function getActionLog() {
		$alog = $this->log->getElementsByTagName("actionlog")->item(0);
		if (!$alog) {
			$alog = $this->log->appendChild($this->log->createElement("actionlog"));
		}
		return $alog->getElementsByTagName("action");
	}

	protected function fetchUser() {
		$this->username = addslashes($this->username);
		$resource = mysql_query("SELECT * FROM `users` WHERE username='$this->username' LIMIT 1");
		$data = mysql_fetch_array($resource);
		if ($this->password === $data['password']) {
			$this->id = $data['id'];
			$this->data = new DOMDocument();
			$this->log = new DOMDocument();
			if ($data['data']) {
				$this->data->loadXML($data['data']);
			}
			if ($data['log']) {
				$this->log->loadXML($data['log']);
			}
			$this->last = $data['last'];
			$up = mysql_query("update `users` set `last` = NOW() where `id` = '{$data['id']}' limit 1");
			if (!$up) {
				echo mysql_error();
			}
			$res = mysql_query("SELECT `last` FROM `users` WHERE `id` = '{$data['id']}' LIMIT 1");
			$ass = mysql_fetch_assoc($res);
			$this->current = $ass['last'];
			$_SESSION['password'] = $this->password;
			$_SESSION['user'] = $this->username;
			$this->loggedin = true;
		} else {
			$this->loggedin = false;
		}
	}

	protected function fetchUserbyId($id) {
		$id = addslashes($id);
		$resource = mysql_query("SELECT * FROM `users` WHERE `id` = '$id' LIMIT 1");
		$data = mysql_fetch_array($resource);
		if ($data) {
			$this->username = $data['username'];
			$this->password = $data['password'];
			$this->id = $data['id'];
			$this->data = new DOMDocument();
			$this->log = new DOMDocument();
			if ($data['data']) {
				$this->data->loadXML($data['data']);
			}
			if ($data['log']) {
				$this->log->loadXML($data['log']);
			}
			$this->last = $data['last'];
		} else {
			$this->loggedin = false;
		}
	}

	public function __destruct() {
		if ($this->loggedin) {
			$this->saveUserData();
			$this->saveUserLog();
		}
	}
}