<?php
/**
 *
 * Automatical query generating object. Gives more secure handling of values in queries.
 * @author anders
 *
 */
class Query {
	protected $connection;
	protected $query = "";
	protected $error = array();
	protected $resource;

	public function __construct($connection = false) {
		$this->connection = $connection;
	}

	public function tableExists($table) {
		$table = addslashes($table);
		$query = "SHOW TABLES LIKE '" . $table . "'";
		$resource = mysql_query($query);
		if (!is_resource($resource)) {
			$this->error[] = mysql_error();
		}
		return mysql_result($resource, 0) ? true : false;
	}

	public function select($table, $fields = array()) {
		$query = "SELECT ";
		if ($fields&&is_array($fields)) {
			foreach ($fields as $i => $field) {
				if ($field&&is_string($field)) {
					$field = addslashes($field);
					if ($i!=0) $query .= ", ";
					$query .= "`".$field."`";
				}
			}
		} else {
			$query .= "*";
		}
		if ($table&&is_string($table)) {
			$table = addslashes($table);
			$query .= " FROM `".$table."`";
			$this->query = $query;
		}
		return $this;
	}

	public function delete($table) {
		if ($table&&is_string($table)) {
			$query = "DELETE ";
			$table = addslashes($table);
			$query .= "FROM `".$table."`";
			$this->query = $query;
		}
		return $this;
	}

	/**
	 *
	 * Creates an update query. The data is fed with $data, an associative array
	 * with the corresponding fields as keys.
	 * @param string $table
	 * @param array $data
	 */
	public function update($table, $data) {
		if ($data&&is_array($data)&&$table&&is_string($table)) {
			$query = "UPDATE ";
			$table = addslashes($table);
			$query .= "`".$table."`";
			$query .= " SET ";
			$i = 0;
			foreach ($data as $field => $da) {
				if ($da&&$field&&is_string($field)) {
					$field = addslashes($field);
					if ($i!=0) $query .= ", ";
					$query .= "`".$field."`";
					$query .= " = ";
					if (is_array($da)) {
						$query .= $da['name']."(";
						if (is_array($da['args'])) {
							foreach ($da['args'] as $j => $arg) {
								if ($j!=0) $query .= ", ";
								$query .= $arg;
							}
						}
						$query .= ")";
					} else {
						$da = addslashes($da);
						$query .= "'".$da."'";
					}
					$i++;
				}
			}
			$this->query = $query;
		}
		return $this;
	}

	public function insert($table, $data) {
		if ($data&&is_array($data)&&$table&&is_string($table)) {
			$query = "INSERT INTO ";
			$table = addslashes($table);
			$query .= "`".$table."` ";
			$fields = "(";
			$values = "VALUES (";
			$i = 0;
			foreach ($data as $field => $da) {
				if ($da&&$field&&is_string($field)) {
					$field = addslashes($field);
					if ($i!=0) {
						$fields .= ", ";
						$values .= ", ";
					}
					$fields .= "`".$field."`";
					if (is_array($da)) {
						$values .= $da['name']."(";
						if (is_array($da['args'])) {
							foreach ($da['args'] as $j => $arg) {
								if ($j!=0) $values .= ", ";
								$values .= $arg;
							}
						}
						$values .= ")";
					} else {
						$da = addslashes($da);
						$values .= "'".$da."'";
					}
					$i++;
				}
			}
			$fields .= ")";
			$values .= ")";
			$query .= $fields." ".$values;
			$this->query = $query;
		}
		return $this;
	}

	public function whereString($string) {
		if ($this->query&&$string&&is_string($string)) {
			$query = " WHERE ";
			$query .= $string;
			$this->query .= $query;
		}
		return $this;
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $field
	 * @param unknown_type $value
	 * @param string $operator
	 */
	public function where($field, $value, $operator = "=") {
		if ($this->query&&$field&&$value&&$operator) {
			$query = " WHERE ";
			$field = addslashes($field);
			$value = addslashes($value);
			$operator = strtoupper($operator);
			$query .= "`".$field."`";
			$query .= " ".$operator." ";
			$query .= "'".$value."'";
			$this->query .= $query;
		}
		return $this;
	}

	/**
	 *
	 * A query to list
	 * @return Query
	 */
	public function showTables() {
		$this->query = "SHOW TABLES";
		return $this;
	}

	/**
	 * 
	 * creates a query that will give information on table fields
	 * 
	 * @param string $table
	 */
	public function showColumns($table) {
		if (is_string($table)) {
			$table = addslashes($table);
			$this->query = "SHOW COLUMNS FROM `".$table."`";
		}
		return $this;
	}

	/**
	 *
	 * Sets the query limit
	 * @param int $size
	 * @param int $start
	 */
	public function limit($size, $start = -1) {
		if ($this->query&&is_numeric($size)&&is_numeric($start)&&$start>=0) {
			$query = " LIMIT ";
			$query .= $start.", ".$size;
			$this->query .= $query;
		} elseif ($this->query&&is_numeric($size)&&$start === -1) {
			$query = " LIMIT ";
			$query .= $size;
			$this->query .= $query;
		}
		return $this;
	}

	/**
	 *
	 * Orders result by field after in ascending or descending order
	 * @param string $field
	 * @param string $rule, either 'DESC'(standard) or 'ASC'
	 * @return Query
	 */
	public function orderBy($field, $rule = "DESC") {
		if ($this->query&&$field&&is_string($field)) {
			$query = " ORDER BY ";
			$field = addslashes($field);
			$rule = strtoupper(addslashes($rule));
			$query .= "`".$field."`";
			if ($rule==="DESC"||$rule==="ASC") {
				$query .= " ".$rule;
			}
			$this->query .= $query;
		}
		return $this;
	}

	/**
	 * 
	 * Run the pending query
	 */
	public function query() {
		if ($this->query) {
			$resource = mysql_query($this->query);
			if (!$resource) {
				$this->error[] = mysql_error();
			} else {
				$this->resource = $resource;
			}
		}
		return $this;
	}

	/**
	 * 
	 * Fetches an associative array of the entire query result
	 */
	public function assoc() {
		$assoc = array();
		if ($this->resource) {
			while ($ass = mysql_fetch_assoc($this->resource)) {
				$assoc[] = $ass;
			}
		}
		return $assoc;
	}

	public function row() {
		$rows = array();
		if ($this->resource) {
			while ($row = mysql_fetch_row($this->resource)) {
				$rows[] = $row;
			}
		}
		return $rows;
	}

	/**
	 *
	 * Returns true on a succesfull query. If not false
	 */
	public function result() {
		if ($this->resource) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Fetches the last error recorded by this object or the specified index $c
	 */
	public function error($c = -1) {
		if ($c == -1) {
			$c = count($this->error)-1;
		}
		return $this->error[$c];
	}

	/**
	 *
	 * Returns the query string
	 */
	public function getQuery() {
		return $this->query;
	}
}