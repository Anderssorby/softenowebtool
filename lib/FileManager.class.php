<?php
/**
 * File upload manager
 */
class FileManager {
	protected $file;
	/**
	 * tells if the user has uploaded multiple files or not
	 */
	protected $isMultiple;
	protected $numFiles;
	public function __construct($key="") {
		$this -> file = $_FILES[$key];
		$this -> isMultiple = is_array($this -> file['name']);
		$this -> numFiles = count($this -> file['name']);
	}

	public function fetchUploadedFile($key = 0, $mime = "*/*", $maxSize = 8388608) {
		if ($this -> isMultiple) {
			$error = $this -> file["error"][$key];
			if ($error == UPLOAD_ERR_OK) {
				$typex = explode("/", $this -> file["type"][$key]);
				$mimex = explode("/", $mime);
				$properSize = $this -> file["type"][$key] <= $maxSize;
				$isOfMime = ($mimex[0] == "*" || $mimex[0] == $typex[0]) && ($mimex[1] == "*" || $mimex[1] == $typex[1]);
				if ($isOfMime && $properSize) {
					$tmp_name = $this -> file["tmp_name"][$key];
					$name = $this -> file["name"][$key];
					Logger::getDefaultLogger() -> app("successfully uploaded");
					return array("data" => file_get_contents($tmp_name), "name" => $name,
					 "mime" => $this -> file["type"], "size" => $this->file["size"]);
				} else {
					Logger::getDefaultLogger() -> app("is not right mime or size");
				}
			} else {
				Logger::getDefaultLogger() -> app("error whith upload");
			}
		} else {
			if ($this -> file["error"] == UPLOAD_ERR_OK) {
				$typex = explode("/", $this -> file["type"]);
				$mimex = explode("/", $mime);
				$properSize = $this -> file["size"] <= $maxSize;
				$isOfMime = ($mimex[0] == "*" || $mimex[0] == $typex[0]) && ($mimex[1] == "*" || $mimex[1] == $typex[1]);
				if ($isOfMime && $properSize) {
					$tmp_name = $this -> file["tmp_name"];
					$name = $this -> file["name"];
					Logger::getDefaultLogger() -> app("successfully uploaded");
					return array("data" => file_get_contents($tmp_name), "name" => $name,
					"mime" => $this->file["type"], "size" => $this->file["size"]);
				} else {
					Logger::getDefaultLogger() -> app("is not right mime or size");
				}
			} else {
				Logger::getDefaultLogger() -> app("error whith upload");
			}
		}
	}

	/**
	*
	*@deprecated
	*/
	public function makeFileList() {
		return new FileList();
	}

	/**
	*
	*@deprecated
	*/
	public static function deleteFile($name) {
		return unlink('arkiv/' . $name);
	}

	/**
	*
	*@deprecated
	*/
	public static function deleteDirectory($name) {
		return rmdir('arkiv/' . $name);
	}

	/**
	*
	*@deprecated
	*/
	public static function moveFile($name, $dir) {
		try {
			if (is_dir("arkiv/$dir")) {
				$split = explode("/", $name);
				$fileName = $split[count($split)-1];
				return rename('arkiv/' . $name, 'arkiv/' . $dir . '/' . $fileName);
			}
		} catch (exception $e) {
			return FALSE;
		}
	}

	/**
	*
	*@deprecated
	*/
	public static function makeNewDirectory($name) {
		if ($name)
			return mkdir('arkiv/' . $name);
	}
	/**
	 * 
	 *@deprecated 
	 */
	public function moveAllToArkiv($mime = "*/*", $maxSize = 104857600) {
		$uploads_dir = 'arkiv';
		if ($this -> isMultiple) {
			foreach ($this->file["error"] as $key => $error) {
				if ($error == UPLOAD_ERR_OK) {
					$typex = explode("/", $this -> file["type"][$key]);
					$mimex = explode("/", $mime);
					$properSize = $this -> file["type"][$key] <= $maxSize;
					$isOfMime = ($mimex[0] == "*" || $mimex[0] == $typex[0]) && ($mimex[1] == "*" || $mimex[1] == $typex[1]);
					if ($isOfMime && $properSize) {
						$tmp_name = $this -> file["tmp_name"][$key];
						$name = $this -> file["name"][$key];
						if (move_uploaded_file($tmp_name, "$uploads_dir/$name"))
							Logger::getDefaultLogger() -> app("successfully uploaded ".$key);
						else
							Logger::getDefaultLogger() -> app("couldn't move uploaded file $uploads_dir/$name");
					} else {
						Logger::getDefaultLogger() -> app("is not right mime or size");
					}
				}
			}
		} else {
			if ($this -> file["error"] == UPLOAD_ERR_OK) {
				$typex = explode("/", $this -> file["type"]);
				$mimex = explode("/", $mime);
				$properSize = $this -> file["size"] <= $maxSize;
				$isOfMime = ($mimex[0] == "*" || $mimex[0] == $typex[0]) && ($mimex[1] == "*" || $mimex[1] == $typex[1]);
				if ($isOfMime && $properSize) {
					$tmp_name = $this -> file["tmp_name"];
					$name = $this -> file["name"];
					if (move_uploaded_file($tmp_name, "$uploads_dir/$name"))
						Logger::getDefaultLogger() -> app("successfully uploaded single file");
					else
						Logger::getDefaultLogger() -> app("couldn't move uploaded file $uploads_dir/$name");
				} else {
					Logger::getDefaultLogger() -> app("is not right mime or size");
				}
			}
		}
	}
	
	/**
	*
	*@deprecated
	*/
	public function moveAllToDirectory($uploads_dir, $mime = "*/*", $maxSize = 104857600) {
		if ($this -> isMultiple) {
			foreach ($this->file["error"] as $key => $error) {
				if ($error == UPLOAD_ERR_OK) {
					$typex = explode("/", $this -> file["type"][$key]);
					$mimex = explode("/", $mime);
					$properSize = $this -> file["type"][$key] <= $maxSize;
					$isOfMime = ($mimex[0] == "*" || $mimex[0] == $typex[0]) && ($mimex[1] == "*" || $mimex[1] == $typex[1]);
					if ($isOfMime && $properSize) {
						$tmp_name = $this -> file["tmp_name"][$key];
						$name = $this -> file["name"][$key];
						if (move_uploaded_file($tmp_name, "$uploads_dir/$name"))
						Logger::getDefaultLogger() -> app("successfully uploaded ".$key);
						else
						Logger::getDefaultLogger() -> app("couldn't move uploaded file $uploads_dir/$name");
					} else {
						Logger::getDefaultLogger() -> app("is not right mime or size");
					}
				}
			}
		} else {
			if ($this -> file["error"] == UPLOAD_ERR_OK) {
				$typex = explode("/", $this -> file["type"]);
				$mimex = explode("/", $mime);
				$properSize = $this -> file["size"] <= $maxSize;
				$isOfMime = ($mimex[0] == "*" || $mimex[0] == $typex[0]) && ($mimex[1] == "*" || $mimex[1] == $typex[1]);
				if ($isOfMime && $properSize) {
					$tmp_name = $this -> file["tmp_name"];
					$name = $this -> file["name"];
					if (move_uploaded_file($tmp_name, "$uploads_dir/$name"))
					Logger::getDefaultLogger() -> app("successfully uploaded single file");
					else
					Logger::getDefaultLogger() -> app("couldn't move uploaded file $uploads_dir/$name");
				} else {
					Logger::getDefaultLogger() -> app("is not right mime or size");
				}
			}
		}
	}

	public function isMultiple() {
		return $this -> isMultiple;
	}

	public function getNumFiles() {
		return $this -> numFiles;
	}

}
