<?php
// Archivo de rutas de la aplicacion y controlador con api's
use Underscore\Types\Arrays;

$app->map('/', function() use($app) {
    $app->render('inicio.html');
})->name('inicio')->via('GET', 'POST');

$app->group('/usuario', function() use($app, $db){
    // Ver todos los usuarios
    $app->get('/todos', function() use($app, $db){
        $usuarios = $db->usuarios();
        echo json_encode($usuarios);
    })->name('usuarios');
});
