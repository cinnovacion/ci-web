<?php
// Archivo de rutas de la aplicacion y controlador con api's

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/{name}', function ($request, $response, $args) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");
    return $response;
})->setName('inicio');

$app->get('/hello/{name}', function ($request, $response, $args) {
    return $this->view->render($response, 'profile.html', ['names' => $args['name']]);
})->setName('profile');

$app->get('/nombre/{index}', function ($request, $response, $args) {
    $this->logger->addInfo("Names list");
    $mapper = new NotORM($this->db);
    $data['names'] = $mapper->enlace('id', $args['index'])->fetch();
    return $this->view->render($response, 'nombres.html', $data);
})->setName('base');

$app->any('/', function ($request, $response, $args) {
    return $this->view->render($response, 'inicio.html');
})->setName('inicio');