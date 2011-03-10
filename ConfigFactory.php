<?php

namespace Whisper;

class ConfigFactory {
	static public function factory($file) {
		/* get file extension */
		$extension = substr($file, strrpos($file, '.') + 1);

		/* add possible support for xml or ini files later */
		switch ($extension) {
			case 'yaml':
			case 'yml':
			default:
				return new Config\NativeYAML($file);
			case 'php':
				return new Config\NativePHP($file);
		}

		return null;
	}
}
