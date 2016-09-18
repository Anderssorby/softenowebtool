<?php 
class Logger {
	protected $logs;
	protected static $defaultLogger;
	private $isDefaultLogger = false;
	function __construct() {		
	}

	public static function getDefaultLogger() {
		if (!isset(Logger::$defaultLogger)) {
			Logger::$defaultLogger = new Logger();
			Logger::$defaultLogger->isDefaultLogger = TRUE;
			
		}
		return Logger::$defaultLogger;
	}
	public static function handleError($type, $message, $file = "(not included)", $line = "(not included)", $context = "(not included)") {
		$msg = "error ($type) $message in $file:$line";
		if ($type<8) {
			Logger::$defaultLogger->app($msg);
			echo $msg;
		}
	}

	function append_message($string, $function = "default", $class = "default") {
		$n = count($this -> logs);
		$this -> logs[$n]['message'] = $string;
		$this -> logs[$n]['function'] = $function;
		$this -> logs[$n]['class'] = $class;
	}

	function app($string, $function = "default", $class = "default") {
		$n = count($this -> logs);
		$this -> logs[$n]['message'] = $string;
		$this -> logs[$n]['function'] = $function;
		$this -> logs[$n]['class'] = $class;
	}

	function flush() {		if (isset($this->logs)) {			$logs = "";			foreach ($this->logs as $l) {				$logs .= $l['message'];				$logs .= " function: " . $l['function'];				$logs .= " class: ". $l['class'];				$logs .= "\n";			}			$time = time()%24*3600;			$res = fopen('./log'.$time.'.txt', 'w');
			fwrite($res, $logs);			fclose($res);		}	}

	function __toString() {
		$json = new JSON();
		foreach ($this->logs as $l) {
			$lo = new JSON();
			$lo -> add("message", $l['message']);
			$lo -> add("function", $l['function']);
			$lo -> add("class", $l['class']);
			$json -> add("log", $lo);
		}
		return $json -> __toString();
	}

	public function __invoke($string, $function = "default", $class = "default") {
		$n = count($this -> logs);
		$this -> logs[$n]['message'] = $string;
		$this -> logs[$n]['function'] = $function;
		$this -> logs[$n]['class'] = $class;
	}
	public function __destruct() {
		$this->flush();
	}
}