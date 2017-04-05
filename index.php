<?php
// Todos las inclusiones de librerias y archivos requeridos
session_cache_limiter(false);   // PHP’s session cache limiter
session_start();    // PHP session

require 'vendor/autoload.php';

spl_autoload_register(function ($classname) {
    require ("classes/" . $classname . ".php");
});

// Datos de configuracion global
$config = [
	/**
     * Configuracion de Slim
     *  - Datos de configuracion del framework
     */
    'settings' => [
        'displayErrorDetails' 	 => true,
        'addContentLengthHeader' => false,
        /**
	     * Configuracion de Logger
	     *  - Datos de configuracion el log de la aplicacion.
	     */
        'logger' => [
            'name'  => 'logger',
            'level' => Monolog\Logger::DEBUG,
            'path' 	=> __DIR__ . '/logs/app.log',
        ],
        /**
	     * Configuracion de PDO
	     *  - Datos de configuracion para la conexion con la base de datos.
	     */
        'db' => [
        	'host' 	 => '127.0.0.1',
        	'user' 	 => 'root',
        	'pass' 	 => '',
        	'dbname' => 'asscic',
        ],
        /**
	     * Configuracion de Twig
	     *  - Datos de configuracion del motor de plantillas.
	     */
        'view' => [
        	'path'  => __DIR__ . '/views',
        	'cache' => false,
        ],
    ],
];

// Inicializacion de Slim Framework
$app = new \Slim\App($config);

// Inicializacion el contenedor de depencias
$container = $app->getContainer();

// Inicializando del Logger de la aplicacion
$container['logger'] = function($container) {
    $logger = $container['settings']['logger'];
    $log = new \Monolog\Logger($logger['name']);
    $file_handler = new \Monolog\Handler\StreamHandler($logger['path']);
    $log->pushHandler($file_handler);
    return $log;
};

// Inicializando conexion a la base de datos con driver PDO
$container['db'] = function ($container) {
    $db = $container['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'], $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db = new NotORM($pdo);
    $db->debug = true;
    return $db;
};

// Inicializacion del Motor de Plantillas Twig
$container['view'] = function ($container) {
    $view = $container['settings']['view'];
    $templates = new \Slim\Views\Twig($view['path'], ['cache' => $view['cache']]);
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $templates->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    return $templates;
};

// Inicializacion de las rutas
require 'routes.php';

// Ejecutar la aplicación
$app->run();
