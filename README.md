# Whisper - A PHP MicroFramework #

Whisper is a micro framework written in PHP 5.3 inspired from frameworks like [Flask][1] or [Express.js][2]
It uses the PHP 5.3 closures and namespaces to provide an easy to use framework for building working sites very fast.

## Documentation ##

A detailed documentation can be found [here][3]

## Usage ##

This is a basic usage redirecting a user to a route upon landing on the index and displaying him a nice 'safe' hello <name> screen.

    require_once 'Whisper/Kernel.php';

    use Whisper\Kernel, Whisper\Request;

    /* create a default kernel */
    $app = new Kernel();

    /* register a route for index */
    $app->route('/', function(Kernel $app) {
        return $app->redirect('/hello');
    });

    /* register a route hello */
    $hello_routes = array('/hello', '/hello/:name');
    $app->route($hello_routes, function(Kernel $app, Request $req) {
        /* try to retrieve variable 'name' from route */
        $name = $req->resolve('name', FILTER_SANITIZE_SPECIAL_CHARS);
        if ($name == null) {
            $name = 'world';
        }
        return 'Hello ' . $name . '!';
    });

    /* dispatch call */
    $app->dispatch();

## Status ##

Current version is very alpha and doesn't support half of the final framework, here are the current features:
* Serve basic and more dynamic routes supporting variables
* Provide a safe wrappers around every user variables ($_GET, $_POST, etc... ) from a query
* Redirection helpers
* Support for Twig
* Configuration system based on YAML

The features to be implemented:
* Middlewares: Encapsulation around controllers to add loosely coupled functionnality very easily
* Events: An easy to use event system using 

[1]: http://flask.pocoo.org/
[2]: http://expressjs.com/
[3]: http://whisper.nekoo.com/
