<?php

namespace Whisper\Config;

/**
 * Small wrapper around the native YAML implementation
 *
 * @package Whisper
 * @author  
 */
class NativeYAML extends ConfigAbstract {
	protected $file = null;

	public function __construct($file) {
		if (!extension_loaded('yaml')) {
			throw new \Whisper\Exceptions\MissingExtensionException('yaml');
		}

		if (!file_exists($file) || !is_readable($file)) {
			throw new \Whisper\Exceptions\FileNotFoundException();
		}

		$this->file = $file;
		$this->config = yaml_parse_file($file);
	}
	
	public function commit() {
		if (!is_writable($this->file)) {
			throw new \Whisper\Exceptions\FileIsNotWritableException();
		}
		yaml_emit_file($this->file, $this->config);
	}

} // END
