<?php
// Todos las inclusiones de librerias y archivos requeridos
session_cache_limiter(false);   // PHP’s session cache limiter
session_start();    // PHP session
require 'vendor/autoload.php';  // Composer required
use \Slim\Middleware\HttpBasicAuthentication\PdoAuthenticator;

// Datos de configuracion global
$config = array(
    /**
     * Configuracion de Slim
     *  - Datos de configuracion del framework
     */
    'slim' => array(
        'debug' => true,
        'cache' => dirname(__FILE__) . '/cache',
        'view' => new \Slim\Views\Twig(),
        'templates.path' => 'views'
    ),
    /**
     * Configuracion de Twig
     *  - Datos de configuracion del motor de plantillas.
     */
    'twig' => array(
        'view.dir' => 'views',
        'options' => array(
            'debug' => true
        ),
        'extensions' => array(
            new \Slim\Views\TwigExtension()
        )
    ),
    /**
     * Configuracion de PDO
     *  - Datos de configuracion para la conexion con la base de datos. 
     */
    'pdo' => array(
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'test',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8'
    ),
    /**
     * Configuracion del Sistema de Autentificacion
     * La Auntentificacion via HTTP Basic Authentication cuenta con dos forma de registro de usuarios:
     * 1 - Lista de usuarios y contraseñas para el acceso.
     * 2 - Table de usuarios en una base de datos.
     * El campo tiempo se usa para eligir entre las dos opciones segun el valor que se asigne:
     *  - Valor "0" para listas de acceso.
     *  - Valor "1" para tabla en la base de datos.
     * Coloque el en campo tipo el valor que mas el convenga y llene los datos que su eleccion requiere.
     * 
     * NOTA: Por defecto el valor de tipo es "0" para elegir listas de acceso y esta creado el usuario admin con contraseña admin.
     */
    'auth' => array(
        'tipo' => '1',
        'lista' => array(
            'users' => array(
                'admin' => 'admin',
                'sam' => 'paolosecondo',
                'isa' => 'toshchi'
            )
        ),
        'tabla' => array(
            'table' => 'usuarios',
            'user' => 'usuario',
            'hash' => 'contra'
        )
    )
);

// Inicializacion de Slim Framework
$app = new \Slim\Slim($config['slim']);

// Inicializacion del Motor de Plantillas Twig
$app->view->setTemplatesDirectory($config['twig']['view.dir']);
$view = $app->view();
$view->parserOptions = $config['twig']['options'];
$view->parserExtensions = $config['twig']['extensions'];

// Inicializando conexion a la base de datos con driver PDO
$pdo = new PDO(
    $config['pdo']['driver'].':host='.$config['pdo']['host'].';dbname='.$config['pdo']['database'].';charset='.$config['pdo']['charset'], 
    $config['pdo']['username'],
    $config['pdo']['password']
);

// Inicializacion de NotORM
$db = new NotORM($pdo);

// Inicializacion de HTTP Basic Authentication
$app->add(new \Slim\Middleware\HttpBasicAuthentication(
    array(
        'authenticator' => new PdoAuthenticator(array(
            'pdo' => $pdo,
            'table' => $config['auth']['tabla']['table'],
            'user' => $config['auth']['tabla']['user'],
            'hash' => $config['auth']['tabla']['hash']
        )),
        'callback' => function ($argumentos) use ($app, $pdo) {
            $consulta = $pdo->query ('SELECT id FROM usuarios WHERE usuario = "'.$argumentos['user'].'"');
            $usuario = $consulta->fetchObject();
            $_SESSION['usario'] = $usuario->id;
        }
    )
));

// Inicializacion de las rutas
require 'routes.php';

$app->run();
