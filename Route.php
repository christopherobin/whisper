<?php

namespace Whisper;

/**
 * The route object
 * 
 * @author Christophe Robin <crobin@php.net>
 */
class Route {
	protected $app;
	protected $routes;
	protected $callback;
	protected $middlewares;

	/**
	 * Constructor
	 * 
	 * @param Whisper\Kernel 	$app			The Kernel that owns this Route
	 * @param string		$route			The route to resolve
	 * @param callback		$callback		A callback to call when dispatching this route
	 * @param array			$middlewares	A set of middlewares to wrap around the callback (TODO)
	 */
	public function __construct(Kernel $app, $route, $callback, $middlewares = array()) {
		$this->app = $app;
		$this->routes = (is_array($route) ? $route : array($route));
		$this->callback = $callback;
		$this->middlewares = $middlewares;
	}
	
	/**
	 * Try to dispatch the current route
	 *
	 * @param Whisper\Request $req	The request to dispatch
	 *
	 * @return mixed A scalar, a Whisper\Response object if the query was dispatched or false 
	 *
	 * @todo Implement middlewares
	 */
	public function dispatch(Request $req) {
		foreach ($this->routes as $route) {
			if (strpos($route, ':') === false) {
				if ($req->route() == $route) {
					return call_user_func_array($this->callback, array($this->app, $req));
				}
				continue;
			}
	
			// otherwise parse route
			$preg = '#^' . preg_replace('#:([^/]+)#', '(?P<\1>[^/]+)', $route) . '#';
	
			if (preg_match($preg, $req->route(), $matches)) {
				$data = array();
				/* remove numeric keys */
				array_walk($matches, function($item, $key, $data) {
					if (!is_numeric($key)) {
						$data[$key] = $item;
					}
				}, &$data);
				/* and register data in the request */
				$req->setData($data);
				/* callback time */
				return call_user_func_array($this->callback, array($this->app, $req));
			}
		}

		return false;
	}
}
