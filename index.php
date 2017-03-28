<?php
// Todos las inclusiones de librerias y archivos requeridos
session_cache_limiter(false);   // PHP’s session cache limiter
session_start();    // PHP session

require 'vendor/autoload.php';

spl_autoload_register(function ($classname) {
    require ("classes/" . $classname . ".php");
});

$config['displayErrorDetails']    = true;
$config['addContentLengthHeader'] = false;

$config['db']['driver']     = "mysql";
$config['db']['host']       = "localhost";
$config['db']['username']   = "root";
$config['db']['password']   = "root";
$config['db']['database']   = "tyrant";
$config['db']['charset']    = "utf8";
$config['db']['collation']  = "utf8_general_ci";
$config['db']['prefix']     = "";

$config['logger']['name']  = 'slim-app';
$config['logger']['level'] = Monolog\Logger::DEBUG;
$config['logger']['path']  = __DIR__ . 'logs/app.log';

$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($c['settings']['db']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    return $capsule;
};

$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig(__DIR__ . '/views', [
        'cache' => false,
    ]);
    $view->addExtension(new Slim\Views\TwigExtension( $c['router'], $c['request']->getUri()));
    return $view;
};

// Inicializacion de las rutas
require 'routes.php';

// Ejecutar la aplicación
$app->run();
