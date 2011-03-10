<?php

namespace Whisper\Config;

/**
 * Small wrapper around the native YAML implementation
 *
 * @package Whisper
 * @author  
 */
abstract class ConfigAbstract implements \ArrayAccess, \SplSubject {
	protected $config = array();

	abstract public function __construct($file);
	abstract public function commit();

	/* ArrayAccess implementation */
	public function offsetExists($offset) {
		return isset($this->config[$offset]);
	}
	
	public function offsetGet($offset) {
		return isset($this->config[$offset]) ? $this->config[$offset] : null;
	}
	
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			throw new Exception('Root of configuration cannot house keyless items');
		} else {
			$this->config[$offset] = $value;
		}
	}
	
	public function offsetUnset($offset) {
		unset($this->config[$offset]);
	}

	/* SplSubject implementation */
	protected $observers;

	public function attach(SplObserver $observer) {
		$this->observers[spl_object_hash($observer)] = $observer;
	}

	public function detach(SplObserver $observer) {
		unset($this->observers[spl_object_hash($observer)]);
	}

	public function notify() {
		foreach ($observers as $observer) {
			$observer->update($this);
		}
	}
}
