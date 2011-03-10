<?php

namespace Whisper;

require_once('Autoload.php');
require_once('Exception.php');

/**
 * The microkernel register routes and dispatch our calls
 *
 * @author Christophe Robin <crobin@php.net>
 * @package Whisper
 */
class Kernel implements \SplSubject {
	protected $app_dir = NULL;
	protected $whisper_dir = NULL;
	protected $autoloader = NULL;
	protected $config = NULL;
	/* view related */
	protected $view = NULL;
	protected $templates = array();

	/**
	 * Constructor
	 * 
	 * @param array $options An array containing kernel options, for now, only the config option is used
	 */
	public function __construct(array $options = null) {
		$this->app_dir = dirname(realpath($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME']));
		$this->whisper_dir = __DIR__;

		$this->autoloader = new Autoload();
		spl_autoload_register(array($this->autoloader, 'load'), true);
		
		if (isset($options['config'])) {
			$this->config = ConfigFactory::factory($options['config']);
		}
	}
	
	/**
	 * Return the current configuration object
	 * 
	 * @return ConfigAbstract The config object or null if no config is loaded
	 */
	public function config() {
		return $this->config;
	}

	/* route management */
	protected $routes;

	/**
	 * Register a route
	 * 
	 * @param mixed 	$route		Either a string containing the route or an array of routes
	 * @param callback 	$callback	The callback called for this route
	 */
	public function route() {
		$args = func_get_args();
		$route = array_shift($args);
		$callback = array_pop($args);
		$middlewares = $args;
		$this->routes[] = new Route($this, $route, $callback, $middlewares);
	}

	/**
	 * Dispatch the current request to the valid route
	 * 
	 * @throws \Whisper\Exceptions\RouteNotFoundException if no route was found
	 */
	public function dispatch() {
		try {
			foreach ($this->routes as $route) {
				$request = new Request();
				if (($response = $route->dispatch($request)) !== false) {
					// transform scalars to Response objects
					if (is_scalar($response)) {
						$response = new Response($response);
					}
					if (!($response instanceof Response)) {
						throw new \Exception('Invalid response');
					}
					return $response->render();
				}
			} 

			throw new Exceptions\RouteNotFoundException();
		} catch (\Exception $e) {
			/* take care of 404 */
			if ($e instanceof Exceptions\RouteNotFoundException) {
				header('HTTP/1.0 404 Not Found');
				exit;
			}
			
			/* if debug is enabled, display a nicely formatted page */
			if (isset($this->config['debug']) && ($this->config['debug'] == true)) {
				$debug_view = $this->setupView(implode(DIRECTORY_SEPARATOR, array(__DIR__, 'Debugger', 'views')));
				/* add source code to traces */
				$backtrace = array();
				foreach ($e->getTrace() as $trace) {
					$tmp = $trace;
					$lines = file($trace['file']);
					$code = trim($lines[$trace['line'] - 1]);
					$tmp['code'] = $code;
					unset($lines);
					$backtrace[] = $tmp;
				}
				/* then display debug template */
				$debug_view->loadTemplate('debug.html')->display(array('type' => get_class($e), 'exception' => $e, 'traces' => $backtrace));
				exit;
			} else {
				throw $e;
			}
		}
	}

	/**
	 * Create a redirection response
	 * 
	 * @param string $to Where to redirect the user
	 * 
	 * @return \Whisper\Response A Response with a preset Location header
	 */
	public function redirect($to) {
		$response = new Response();
		/* by default we use the good old pathinfo */
		if (!$this->config || isset($this->config['rewrite']) || $this->config['rewrite'] != true) {
			$to = $_SERVER['SCRIPT_NAME'] . $to;
		}
		$response->setHeader('Location', $to);
		return $response; 
	}

	/* view management */
	protected $twig_autoload_registered = false;
	/**
	 * Setup the Twig template engine
	 * 
	 * @param string 	$tpl_folder The folder from where Twig load it's templates
	 * @param array		$config		A set of options to provide to twig
	 * 
	 * @return \Twig_Environment	A twig environment ready to use
	 * 
	 * @throws \Whisper\Exceptions\DirectoryNotFoundException if the template directory doesn't exists
	 */
	protected function setupView($tpl_folder, $config = null) {
		if (!$this->twig_autoload_registered) {
			$twig_autoloader_file = $this->whisper_dir . DIRECTORY_SEPARATOR . 'Vendor/Twig/lib/Twig/Autoloader.php';
			if (!file_exists($twig_autoloader_file)) {
				throw new Exceptions\FileNotFoundException($twig_autoloader_file);
			}
			/* 3rd party dependencies */
			require_once($twig_autoloader_file);

			\Twig_Autoloader::register();
			$this->twig_autoload_registered = true;
		}
		
		if (!is_dir($tpl_folder)) {
			throw new Exceptions\DirectoryNotFoundException($tpl_folder);
		}

		/* create the twig loader */
		$loader = new \Twig_Loader_Filesystem($tpl_folder);
		
		$twig_options = array();
		/* setup the twig options */
		if (($cache = $this->getCacheFolder('view')) !== false) {
			$twig_options['cache'] = $cache;
		}
		if (isset($config['debug'])) {
			$twig_options['debug'] = $config['debug'];
		}
		if (isset($config['charset'])) {
			$twig_options['debug'] = $config['charset'];
		}
		return new \Twig_Environment($loader, $twig_options);
	}

	/**
	 * Interact with the view layer
	 * 
	 * @param string $template	The template file to load/use
	 * 
	 * @return \Twig_Template	The instancied template
	 */
	public function view($template) {
		$config = array();
		if (isset($this->config['view'])) {
			$config = $this->config['view'];
		}
		/* for now we only support Twig */
		if (!$this->view) {
			/* views are expected to be stored in app_dir/views by default */
			$view_directory = $this->app_dir . DIRECTORY_SEPARATOR . 'views';
			if ($this->config && isset($config['path'])) {
				$view_directory = realpath($config['path']);
			}

			$this->view = $this->setupView($view_directory, $config);
		}

		if (!isset($this->templates[$template])) {
			$this->templates[$template] = $this->view->loadTemplate($template);
		}

		return $this->templates[$template];
	}

	/**
	 * Shortcut for view($template)->render($values)
	 * 
	 * @param string	$template	The template file to load
	 * @param array		$values		The values to pass to render()
	 * 
	 * @return string The rendered template
	 */
	public function renderTemplate($template, array $values = null) {
		if ($values === null) {
			$values = array();
		}
		return $this->view($template)->render($values);
	}
	
	/* utilities */
	
	/**
	 * Return the supposed cache folder for a component and create it if it doesn't exists
	 * 
	 * @param string $component The component name
	 */
	public function getCacheFolder($component) {
		$cache_directory = $this->app_dir . DIRECTORY_SEPARATOR . 'cache';
		/* search for the cache folder */
		if (!is_dir($cache_directory)) {
			return false;
		}
		
		$component_cache_directory = $cache_directory . DIRECTORY_SEPARATOR . $component;
		/* try to create the cache folder */
		if (!is_dir($component_cache_directory)) {
			mkdir($component_cache_directory);
		}

		/* if the folder exists */
		if (is_dir($component_cache_directory)) {
			return $component_cache_directory;
		}

		return false;
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
} // END
