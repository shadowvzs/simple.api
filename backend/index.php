<?php
session_start();

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

define('DS', '/');
define('PHP_EXT', '.php');

define('BASE_PATH', __DIR__ . DS);
define('DEBUG', true);
define('LINUX', true);
define('LOG', false);
define('METHOD', strtoupper($_SERVER['REQUEST_METHOD']));
define('DATASOURCE_LIST', [
	'mysql' => ['mysql datasource'],	
	'api' => ['api datasource, in our case now session'],
]);

define('PATTERNS', 
	[
		'EMAIL' => '/^([a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$)$/',
		'NAME' => '/^([a-zA-Z \-]+)$/',
		'NAME_EX' => '/^([a-zA-Z0-9_ \-]+)$/',
		'INTEGER' => '/^([0-9]+)$/',
		'SLUG' => '/^[a-zA-Z0-9-_]+$/',
		'SORT' => '/^(ASC|DESC)$/',
		'ALPHA_NUM' => '/^([a-zA-Z0-9]+)$/',
		'STR_AND_NUM' => '/^([0-9]+[a-zA-Z]+|[a-zA-Z]+[0-9]+|[a-zA-Z]+[0-9]+[a-zA-Z]+)$/',
		'LOWER_UPPER_NUM' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/',
		'URL_QUERY' => '/^([a-zA-Z0-9\-\/_%]+)$/',
	]
);

register_shutdown_function( "fatalErrorHandler" );


spl_autoload_register(function ($className) {
	
	$file = BASE_PATH . $className.PHP_EXT;
	if (LINUX) {
		$file = str_replace("\\", "/", $file);
	}

	if (file_exists($file)) {
		include_once $file;
	}

});
	
if (DEBUG) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

function fatalErrorHandler()
{
	$errfile = 'unknown file';
    $errstr  = 'shutdown';
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if( $error !== NULL) {
    	$file = explode('/', $error["file"]);
        $errno   = $error["type"];
        $errfile = end($file);
        $errline = $error["line"];
        $errstr  = $error["message"];
		$message = $errfile."[".$errno."]: ".$errstr." - line: ".$errline;
		\Controller\App::response($message);	
	}
}

$Router = new \Router\Router;
$urlData = $Router->dispatchedUrl;
$controllerName = "\\Controller\\". $urlData['controller'];
$repositoryName = "\\Model\\". $urlData['controller'] . ucfirst($Router->datasource);
$Controller = new $controllerName($urlData, new $repositoryName);

$action = $urlData['action'];
if (!is_callable(array($Controller, $action))) {
	\Controller\App::error('Action not exist');
}
$Controller->$action();


?>