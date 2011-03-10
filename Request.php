<?php

namespace Whisper;

/**
 * The request object provides a proxy between the controller and the user/environment variables
 * 
 * @author Christophe Robin <crobin@php.net> 
 */
class Request {
	protected $route = '/';
	protected $data = null;

	/**
	 * Constructor, precompute the current route
	 */
	public function __construct() {
		/* cache the current route */
		$this->route = preg_replace('/^' . preg_quote($_SERVER['SCRIPT_NAME'], '/') . '/', '', $_SERVER['REQUEST_URI']);
	}
	
	/**
	 * Return the computed route
	 * 
	 * @return string The route
	 */
	public function route() {
		return $this->route;
	}
	
	/**
	 * Resolve a variable from the route
	 * 
	 * @return string The variable value
	 */
	public function resolve($var, $filter = NULL, $options = NULL) {
		if (!$this->data) {
			return null;
		}
		return $this->getValue($this->data, $var, $filter, $options);
	}
	
	/**
	 * Return all the variables resolved by the current route
	 * 
	 * @return array The resolved values
	 */
	public function vars() {
		return $this->data;
	}
	
	/**
	 * Retrieve a variable from $_GET
	 */
	public function get($key, $filter = NULL, $options = NULL) {
		return $this->getValue($_GET, $key, $filter, $options);
	}

	/**
	 * Retrieve a variable from $_POST
	 */
	public function post($key, $filter = NULL, $options = NULL) {
		return $this->getValue($_POST, $key, $filter, $options);
	}

	/**
	 * Retrieve a variable from $_REQUEST
	 */
	public function request($key, $filter = NULL, $options = NULL) {
		return $this->getValue($_REQUEST, $key, $filter, $options);
	}
	
	/**
	 * Retrieve a variable from $_COOKIE
	 */
	public function cookie($key, $filter = NULL, $options = NULL) {
		return $this->getValue($_COOKIE, $key, $filter, $options);
	}
	
	/**
	 * Retrieve a variable from $_ENV
	 */
	public function env($key, $filter = NULL, $options = NULL) {
		return $this->getValue($_ENV, $key, $filter, $options);
	}
	
	/* processed data from the route */
	public function setData($data) {
		$this->data = $data;
	}
	
	/**
	 * Retrieve a value from an array and apply filter_var if needed
	 * 
	 * @param array		$from 		The array we're reading the data from
	 * @param string 	$key		The key we're trying to access
	 * @param integer	$filter 	The filter we want to apply ( or 0 )
	 * @param array		$options	An array of options provided to the filter ( or NULL )
	 * 
	 * @return mixed The filtered value from the array, or NULL
	 */
	protected function getValue(array $from, $key, $filter, $options) {
		if (!isset($from[$key])) {
			return null;
		}
		$value = $from[$key];
		if ($filter) {
			/* apply the filter using array_walk if the input is an array */
			if (is_array($value)) {
				$value = array_walk($value, function(&$item, $key, $filter_options) {
					$item = filter_var($item, $filter_options['filter'], $filter_options['options']);
				}, array('filter' => $filter, 'options' => $options));
			} else {
				$value = filter_var($value, $filter, $options);
			}
		}
		return $value;
	}
}
