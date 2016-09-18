<?php


class ErrorService {

	private $echoOn = false;
	
	public static $currentService;

	public function __construct() {
		
	}
	
	public static function handleError($type, $message, $file = "(not included)", $line = "(not included)", $context = "(not included)") {
		$msg = "error ($type) $message in $file:$line";
		Logger::getDefaultLogger()->app($msg);
		if ($echoOn) echo $msg;
	}
	
	public static function handleException($e) {
		Logger::getDefaultLogger()->app($e);
		if ($echoOn) echo $e;
	}


	public static function logError($string) {
		Logger::getDefaultLogger()->app($string);
	}
}
set_exception_handler(array("ErrorService", "handleException"));
set_error_handler(array("ErrorService", "handleError"));