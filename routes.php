<?php
// Archivo de rutas de la aplicacion y controlador con api's

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->any('/', function ($request, $response, $args) {
    return $this->view->render($response, 'inicio.html');
})->setName('inicio');


$app->any('/visitas', function ($request, $response, $args) {
    return $this->view->render($response, '/visitas/visitas.html');
})->setName('visitas');

$app->any('/visitas/registro', function ($request, $response, $args) {
	$parsedBody = $request->getParsedBody();
	var_dump($parsedBody);
	die();	
})->setName('visitas_reg');
