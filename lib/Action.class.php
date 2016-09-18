<?php
/**
 *Action is an interface for handling userspecified actions
 *through the GET or POST method 
 */
interface Action extends KeyTrigger {
	const perform = "perform";
	const reset = "reset";
		
    public function performAction($command, $args);
}