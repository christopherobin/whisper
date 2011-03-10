<?php

namespace Whisper\Config;

/**
 * Small wrapper around var export. NOT RECOMMENDED
 *
 * @package Whisper
 * @author  
 */
class NativePHP extends ConfigAbstract {
	protected $file = null;

	public function __construct($file) {
		if (!file_exists($file) || !is_readable($file)) {
			throw new \Whisper\Exceptions\FileNotFoundException();
		}

		$this->file = $file;
		$this->config = (include($file));
	}
	
	public function commit() {
		if (!is_writable($this->file)) {
			throw new \Whisper\Exceptions\FileIsNotWritableException();
		}
		file_put_contents($this->file, 'return ' . var_export($this->config, true));
	}

} // END
