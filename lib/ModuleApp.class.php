<?php 
/**
 * 
 * Interface for moduleapps any class assigned to a module must implement this interface
 * @author anders
 *
 */
interface ModuleApp extends KeyTrigger {
	public function createInstance($args);
}