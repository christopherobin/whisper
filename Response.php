<?php

namespace Whisper;

/**
 * The response object
 * 
 * @author Christophe Robin <crobin@php.net>
 */
class Response {
	protected $headers = array();
	protected $body;

	/**
	 * Constructor
	 * 
	 * @param string $body	The body of our response
	 */
	public function __construct($body = null) {
		$this->body = $body;
	}

	/**
	 * Wrapper around the cookie object
	 */
	public function setCookie($name, $value, $expire = 0, $path = null,
								$domain = null, $secure = false, $httponly = false) {
		setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	/**
	 * Set an header to be outputed
	 * 
	 * @param string 	$name	The name of the header (Location, Content-Type, etc...)
	 * @param string	$value	The value to use for this header
	 */
	public function setHeader($name, $value) {
		$this->headers[$name] = $value;
	}
	
	/**
	 * Set the response body
	 * 
	 * @param string $body	The response body
	 */
	public function setBody($body) {
		$this->body = $body;
	}
	
	/**
	 * Render the current response
	 * 
	 * @throw /Exception if trying to send headers when they've already been sent
	 */
	public function render() {
		if ($this->headers) {
			if (headers_sent()) {
				throw new \Exception('Headers already sent.');
			}
			foreach ($this->headers as $header => $value) {
				header($header . ': ' . $value);
			}
		}
		
		echo $this->body;
	}
}
