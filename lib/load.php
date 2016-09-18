<?php 
// Load classes as they are needed
spl_autoload_register(function($className) {
	$file_path = 'lib/'.$className.'.class.php';
	if (is_file($file_path)) {
		set_include_path('lib/');
		include_once($file_path);
	}
});
