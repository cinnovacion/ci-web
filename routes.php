<?php
// Archivo de rutas de la aplicacion y controlador con api's

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app->any('/', function (Request $request, Response $response) {
    return "Hola";
})->setName('inicio');

$app->any('/inicio', function ($request, $response, $args) {
    $mapper = new PruebaController();
    echo $mapper; die();
    return $this->view->render($response, 'hola.html');
})->setName('inicio');

$app->any('/hello/{name}', function ($request, $response, $args) {
    return $this->view->render($response, 'profile.html', ['name' => $args['name']]);
})->setName('profile');
