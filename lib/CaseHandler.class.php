<?php
/**
 * 
 * CaseHandler interface for extentions to dump data, ex. images, files...
 * @author anders
 *
 */
interface CaseHandler extends KeyTrigger {
	/**
	 * 
	 * Is called when a registerd case occurs.
	 * The return value should be a string or stringlike object or variable. It will be
	 * printed out at the end of the method the method should never print on it's own.
	 */
	public function actOnCase($args);
}