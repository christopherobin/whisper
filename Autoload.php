<?php

namespace Whisper;

/**
 * Autoload
 * 
 * @author Christophe Robin <crobin@php.net>
 */
class Autoload {
	/**
	 * Automatically load any class from the Whisper framework
	 * 
	 * @param string $classname The class to load, including the namespace
	 */
	public function load($classname) {
		$namespace = explode('\\', $classname);
		$class = array_pop($namespace);
		/* only manage autoload for Whisper classes */
		if (array_shift($namespace) != 'Whisper') {
			return;
		}
		$include_file = '';
		if ($namespace) {
			$include_file .= dirname(__FILE__) .DIRECTORY_SEPARATOR;
			$include_file .= implode(DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$include_file .= $class . '.php';

		include_once($include_file);
	}
}
