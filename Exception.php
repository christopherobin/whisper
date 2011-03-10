<?php

namespace Whisper\Exceptions;

/* core exceptions */
class MissingExtensionException extends \Exception {
	public function __construct($extension_name) {
		parent::__construct('Missing the \'' . $extension_name . '\' extension');
	}
}
/* file based exceptions */
class FileNotFoundException extends \Exception {
	public function __construct($file) {
		parent::__construct('File \'' . $file . '\' doesn\'t exists or cannot be accessed');
	}
}
class FileIsNotWritableException extends \Exception { }

class DirectoryNotFoundException extends \Exception {
	public function __construct($dir) {
		parent::__construct('Directory \'' . $dir . '\' doesn\'t exists or cannot be accessed');
	}
}

class RouteNotFoundException extends \Exception { }
